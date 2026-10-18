<?php

namespace App\Http\Controllers;

use App\Models\Garantia;
use App\Models\Grupo;
use App\Models\Parametro;
use App\Models\Reserva;
use App\Models\Socio;
use App\Models\TipoActividad;
use App\Rules\RutChileno;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ArriendoController extends Controller
{
    public function index(Request $request)
    {
        $usuario = $request->user();
        $esDirigente = $usuario->hasRole('presidente', 'secretario');
        abort_unless($esDirigente || $usuario->esSocioActivo(), 403);
        Reserva::liberarVencidos();

        return view('arriendos.index', [
            'esDirigente' => $esDirigente,
            'socios' => $esDirigente ? Socio::where('estado', 'activo')->orderBy('nombre_completo')
                ->get(['id', 'user_id', 'nombre_completo', 'rut', 'direccion', 'telefono']) : collect(),
            'grupos' => Grupo::with('representante')
                ->when(! $esDirigente, fn ($q) => $q->where(fn ($q) => $q->where('representante_id', $usuario->socio->id)
                    ->orWhere('representante_usuario_id', $usuario->id)))
                ->orderBy('nombre')->get()->filter(fn ($g) => $g->tieneRepresentanteVigente()),
            'bloques' => Reserva::bloquesDeLaSede(),
            'tiposActividad' => TipoActividad::where('activo', true)->orderBy('id')->get(),
            'plazoHoras' => Parametro::entero('arriendo_plazo_confirmacion_horas'),
            'arriendos' => Reserva::with(['socio', 'grupo', 'garantia'])
                ->when(! $esDirigente, fn ($q) => $q->where('socio_id', $usuario->socio->id))
                ->latest('id')->paginate(15),
        ]);
    }

    public function store(Request $request)
    {
        $usuario = $request->user();
        $esDirigente = $usuario->hasRole('presidente', 'secretario');
        abort_unless($esDirigente || $usuario->esSocioActivo(), 403);
        if ($esDirigente && $request->filled('tipo_actividad') && $request->tipo_actividad !== 'sede') {
            $request->validate(['valor_arriendo' => ['required', 'integer', 'in:0']]);
            $request->merge(['tipo' => $request->input('tipo_actividad')]);
            return app(ReservaController::class)->store($request);
        }
        $manual = $esDirigente && $request->input('modalidad_solicitante', 'socio') === 'manual';
        if ($manual && is_string($request->responsable_rut)) {
            $request->merge(['responsable_rut' => RutChileno::normalizar($request->responsable_rut)]);
        }

        $datos = $request->validate([
            'modalidad_solicitante' => $esDirigente ? ['sometimes', 'string', 'in:socio,manual'] : ['exclude'],
            'socio_id' => $esDirigente && ! $manual ? ['required', 'integer', Rule::exists('socios', 'id')->where('estado', 'activo')] : ['exclude'],
            'responsable_nombre' => $manual ? ['required', 'string', 'max:255'] : ['exclude'],
            'responsable_rut' => $manual ? ['bail', 'required', 'string', 'max:12', new RutChileno] : ['exclude'],
            'responsable_direccion' => $manual ? ['required', 'string', 'max:255'] : ['exclude'],
            'responsable_telefono' => $manual ? ['required', 'string', 'max:20'] : ['exclude'],
            'grupo_id' => ['nullable', 'integer', 'exists:grupos,id'],
            'fecha' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:'.now()->addMonths(6)->toDateString()],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'valor_arriendo' => $esDirigente ? ['required', 'integer', 'min:0', 'max:4294967295'] : ['exclude'],
            'comprobante' => $esDirigente && $request->integer('valor_arriendo') > 0
                ? ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'] : ['exclude'],
        ]);

        return DB::transaction(function () use ($request, $usuario, $esDirigente, $manual, $datos) {
            DB::table('parametros')->where('clave', 'sede_hora_inicio')->update(['valor' => DB::raw('valor')]);
            Reserva::liberarVencidos();
            $socio = $manual ? null : ($esDirigente ? Socio::findOrFail($datos['socio_id']) : $usuario->socio->fresh());
            if (! $manual) abort_unless($socio && $socio->estado === 'activo', 403, 'El solicitante en línea debe ser socio activo.');

            if (! empty($datos['grupo_id'])) {
                $grupo = Grupo::findOrFail($datos['grupo_id']);
                $esRepresentante = $manual
                    ? mb_strtolower(trim($datos['responsable_nombre'])) === mb_strtolower($grupo->datosRepresentante()['nombre'] ?? '')
                        && (! $grupo->datosRepresentante()['rut'] || $datos['responsable_rut'] === RutChileno::normalizar($grupo->datosRepresentante()['rut']))
                    : ((int) $grupo->representante_id === $socio->id || (int) $grupo->representante_usuario_id === $socio->user_id);
                if (! $grupo->tieneRepresentanteVigente() || ! $esRepresentante) {
                    throw ValidationException::withMessages(['grupo_id' => 'El responsable debe ser el representante vigente del grupo.']);
                }
            }

            $inicio = Carbon::parse($datos['fecha'].' '.$datos['hora_inicio']);
            $fin = Carbon::parse($datos['fecha'].' '.$datos['hora_fin']);
            $error = $inicio->isPast() ? 'El horario solicitado ya comenzó.' : Reserva::errorDeConflicto($inicio, $fin);
            if ($error) {
                throw ValidationException::withMessages(['arriendo' => $error]);
            }

            $reserva = Reserva::create([
                'tipo' => 'arriendo',
                'estado' => 'pendiente',
                'inicio' => $inicio,
                'fin' => $fin,
                'motivo' => 'Reserva de sede',
                'tipo_nombre' => 'Reserva de sede',
                'responsable_nombre' => $manual ? $datos['responsable_nombre'] : $socio->nombre_completo,
                'responsable_rut' => $manual ? $datos['responsable_rut'] : $socio->rut,
                'responsable_direccion' => $manual ? $datos['responsable_direccion'] : $socio->direccion,
                'responsable_telefono' => $manual ? $datos['responsable_telefono'] : $socio->telefono,
                'canal' => $esDirigente && ($manual || $socio?->user_id !== $usuario->id) ? 'ventanilla' : 'en_linea',
                'socio_id' => $socio?->id,
                'grupo_id' => $datos['grupo_id'] ?? null,
                'creado_por' => $usuario->id,
                'aporte_variable' => true,
                'valor_arriendo' => $esDirigente ? $datos['valor_arriendo'] : null,
                'aporte_definido_por' => $esDirigente ? $usuario->id : null,
                'aporte_definido_en' => $esDirigente ? now() : null,
                'valor_garantia' => null,
                'vence_en' => now()->addHours(Parametro::entero('arriendo_plazo_confirmacion_horas')),
                'comprobante_arriendo_path' => isset($datos['comprobante'])
                    ? $datos['comprobante']->store('comprobantes/arriendos', 'local') : null,
            ]);

            return redirect()->route('arriendos.show', $reserva)->with('status', 'arriendo-solicitado');
        });
    }

    public function show(Request $request, Reserva $reserva)
    {
        $this->autorizarConsulta($request, $reserva);
        Reserva::liberarVencidos();

        return view('arriendos.show', [
            'reserva' => $reserva->refresh()->load(['socio', 'grupo', 'garantia.ingresos', 'ingresos']),
            'esDirigente' => $request->user()->hasRole('presidente', 'secretario'),
            'esFinanciero' => $request->user()->hasRole('presidente', 'tesorero'),
            'volverFinanzas' => $request->query('desde') === 'finanzas' && $request->user()->hasRole('presidente', 'tesorero'),
            'volverReservas' => $request->query('desde') === 'reservas' && $request->user()->hasRole('presidente', 'secretario'),
        ]);
    }

    public function definirAporte(Request $request, Reserva $reserva)
    {
        $datos = $request->validate([
            'valor_arriendo' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ]);

        return DB::transaction(function () use ($request, $reserva, $datos) {
            DB::table('parametros')->where('clave', 'sede_hora_inicio')->update(['valor' => DB::raw('valor')]);
            Reserva::liberarVencidos();
            $reserva->refresh();
            abort_unless($reserva->tipo === 'arriendo', 404);
            if (! $reserva->aporte_variable || $reserva->estado !== 'pendiente' || $reserva->valor_arriendo !== null) {
                return back()->withErrors(['arriendo' => 'El aporte solo puede definirse una vez, en una solicitud nueva pendiente y dentro de su plazo.']);
            }
            $reserva->update([
                'valor_arriendo' => $datos['valor_arriendo'],
                'aporte_definido_por' => $request->user()->id,
                'aporte_definido_en' => now(),
            ]);

            return back()->with('status', 'aporte-definido');
        });
    }

    public function adjuntarComprobante(Request $request, Reserva $reserva)
    {
        $this->autorizarConsulta($request, $reserva);
        abort_unless($reserva->canal === 'ventanilla' ? $request->user()->hasRole('presidente', 'secretario')
            : ($request->user()->esSocioActivo() && $reserva->socio_id === $request->user()->socio->id), 403);
        $datos = $request->validate([
            'comprobante' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        return DB::transaction(function () use ($request, $reserva, $datos) {
            DB::table('parametros')->where('clave', 'sede_hora_inicio')->update(['valor' => DB::raw('valor')]);
            Reserva::liberarVencidos();
            $reserva->refresh();
            $this->autorizarConsulta($request, $reserva);
            if (! $reserva->aporte_variable || $reserva->estado !== 'pendiente'
                || $reserva->valor_arriendo === null || $reserva->valor_arriendo <= 0 || $reserva->comprobante_arriendo_path) {
                return back()->withErrors(['arriendo' => 'Solo puedes adjuntar el comprobante a una solicitud pendiente con aporte definido mayor que $0 y sin comprobante previo.']);
            }
            $reserva->update(['comprobante_arriendo_path' => $datos['comprobante']->store('comprobantes/arriendos', 'local')]);

            return back()->with('status', 'comprobante-adjuntado');
        });
    }

    public function confirmar(Request $request, Reserva $reserva)
    {
        return DB::transaction(function () use ($request, $reserva) {
            DB::table('parametros')->where('clave', 'sede_hora_inicio')->update(['valor' => DB::raw('valor')]);
            Reserva::liberarVencidos();
            $reserva->refresh();
            abort_unless($reserva->tipo === 'arriendo', 404);

            if ($reserva->estado !== 'pendiente') {
                return back()->withErrors(['arriendo' => 'Solo se puede confirmar una solicitud pendiente y dentro de su plazo.']);
            }

            if ($reserva->aporte_variable) {
                if ($reserva->valor_arriendo === null) {
                    return back()->withErrors(['arriendo' => 'Define el aporte antes de confirmar la reserva.']);
                }
                if ($reserva->valor_arriendo > 0 && (! $reserva->comprobante_arriendo_path
                    || ! Storage::disk('local')->exists($reserva->comprobante_arriendo_path))) {
                    return back()->withErrors(['arriendo' => 'Adjunta el comprobante del aporte antes de confirmar.']);
                }
            }
            $request->validate([
                'pago_verificado' => ! $reserva->aporte_variable || $reserva->valor_arriendo > 0 ? ['accepted'] : ['exclude'],
                'garantia_verificada' => ! $reserva->aporte_variable ? ['accepted'] : ['exclude'],
            ]);

            $reserva->update([
                'estado' => 'confirmada',
                'validado_en' => now(),
                'validado_por' => $request->user()->id,
            ]);
            if (! $reserva->aporte_variable) Garantia::create([
                'reserva_id' => $reserva->id,
                'monto' => $reserva->valor_garantia,
                'estado' => 'recibida',
                'recibida_en' => now(),
                'validado_por' => $request->user()->id,
            ]);

            return back()->with('status', 'arriendo-confirmado');
        });
    }

    public function comprobante(Request $request, Reserva $reserva)
    {
        $this->autorizarConsulta($request, $reserva);
        abort_unless($reserva->comprobante_arriendo_path
            && Storage::disk('local')->exists($reserva->comprobante_arriendo_path), 404);

        return Storage::disk('local')->response($reserva->comprobante_arriendo_path);
    }

    private function autorizarConsulta(Request $request, Reserva $reserva): void
    {
        abort_unless($reserva->tipo === 'arriendo', 404);
        $usuario = $request->user();
        abort_unless($usuario->hasRole('presidente', 'secretario')
            || ($usuario->hasRole('tesorero') && $reserva->validado_en)
            || ($usuario->esSocioActivo() && $reserva->socio_id === $usuario->socio->id), 403);
    }
}
