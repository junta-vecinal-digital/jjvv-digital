<?php

namespace App\Http\Controllers;

use App\Models\Garantia;
use App\Models\Grupo;
use App\Models\Parametro;
use App\Models\Reserva;
use App\Models\Socio;
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
                ->get(['id', 'nombre_completo', 'rut']) : collect(),
            'grupos' => Grupo::with('representante')
                ->when(! $esDirigente, fn ($q) => $q->where('representante_id', $usuario->socio->id))
                ->orderBy('nombre')->get()->filter(fn ($g) => $g->tieneRepresentanteVigente()),
            'bloques' => Reserva::bloquesDeLaSede(),
            'valorArriendo' => Parametro::entero('arriendo_valor'),
            'valorGarantia' => Parametro::entero('arriendo_garantia'),
            'plazoHoras' => Parametro::entero('arriendo_plazo_confirmacion_horas'),
            'arriendos' => Reserva::with(['socio', 'grupo', 'garantia'])->where('tipo', 'arriendo')
                ->when(! $esDirigente, fn ($q) => $q->where('socio_id', $usuario->socio->id))
                ->latest('id')->paginate(15),
        ]);
    }

    public function store(Request $request)
    {
        $usuario = $request->user();
        $esDirigente = $usuario->hasRole('presidente', 'secretario');
        abort_unless($esDirigente || $usuario->esSocioActivo(), 403);

        $datos = $request->validate([
            'socio_id' => $esDirigente ? ['required', Rule::exists('socios', 'id')->where('estado', 'activo')] : ['exclude'],
            'grupo_id' => ['nullable', 'exists:grupos,id'],
            'fecha' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:'.now()->addMonths(6)->toDateString()],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'valor_arriendo' => ['required', 'integer', 'min:0'],
            'valor_garantia' => ['required', 'integer', 'min:0'],
            'comprobante' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        return DB::transaction(function () use ($request, $usuario, $esDirigente, $datos) {
            DB::table('parametros')->where('clave', 'sede_hora_inicio')->update(['valor' => DB::raw('valor')]);
            Reserva::liberarVencidos();
            $socio = $esDirigente ? Socio::findOrFail($datos['socio_id']) : $usuario->socio->fresh();
            abort_unless($socio && $socio->estado === 'activo', 403, 'El solicitante debe ser socio activo.');

            if (! empty($datos['grupo_id'])) {
                $grupo = Grupo::findOrFail($datos['grupo_id']);
                if (! $grupo->tieneRepresentanteVigente() || (int) $grupo->representante_id !== $socio->id) {
                    throw ValidationException::withMessages(['grupo_id' => 'El responsable debe ser el representante vigente del grupo.']);
                }
            }

            $inicio = Carbon::parse($datos['fecha'].' '.$datos['hora_inicio']);
            $fin = Carbon::parse($datos['fecha'].' '.$datos['hora_fin']);
            $error = $inicio->isPast() ? 'El horario solicitado ya comenzó.' : Reserva::errorDeConflicto($inicio, $fin);
            if ($error) {
                throw ValidationException::withMessages(['arriendo' => $error]);
            }

            $valorArriendo = Parametro::entero('arriendo_valor');
            $valorGarantia = Parametro::entero('arriendo_garantia');
            if ((int) $datos['valor_arriendo'] !== $valorArriendo || (int) $datos['valor_garantia'] !== $valorGarantia) {
                throw ValidationException::withMessages(['arriendo' => 'Los valores cambiaron. Revisa el total actualizado antes de enviar.']);
            }

            $reserva = Reserva::create([
                'tipo' => 'arriendo',
                'estado' => 'pendiente',
                'inicio' => $inicio,
                'fin' => $fin,
                'motivo' => 'Arriendo de sede',
                'socio_id' => $socio->id,
                'grupo_id' => $datos['grupo_id'] ?? null,
                'creado_por' => $usuario->id,
                'valor_arriendo' => $valorArriendo,
                'valor_garantia' => $valorGarantia,
                'vence_en' => now()->addHours(Parametro::entero('arriendo_plazo_confirmacion_horas')),
                'comprobante_arriendo_path' => $request->file('comprobante')->store('comprobantes/arriendos', 'local'),
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
        ]);
    }

    public function confirmar(Request $request, Reserva $reserva)
    {
        $request->validate([
            'pago_verificado' => ['accepted'],
            'garantia_verificada' => ['accepted'],
        ]);

        return DB::transaction(function () use ($request, $reserva) {
            DB::table('parametros')->where('clave', 'sede_hora_inicio')->update(['valor' => DB::raw('valor')]);
            Reserva::liberarVencidos();
            $reserva->refresh();
            abort_unless($reserva->tipo === 'arriendo', 404);

            if ($reserva->estado !== 'pendiente') {
                return back()->withErrors(['arriendo' => 'Solo se puede confirmar una solicitud pendiente y dentro de su plazo.']);
            }

            $reserva->update([
                'estado' => 'confirmada',
                'validado_en' => now(),
                'validado_por' => $request->user()->id,
            ]);
            Garantia::create([
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
