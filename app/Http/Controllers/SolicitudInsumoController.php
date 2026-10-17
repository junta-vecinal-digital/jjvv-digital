<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\HorarioAtencion;
use App\Models\ItemInventario;
use App\Models\Prestamo;
use App\Models\Socio;
use App\Models\SolicitudInsumo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SolicitudInsumoController extends Controller
{
    public function index(Request $request)
    {
        $this->autorizarSolicitud($request);
        $gestion = $request->user()->hasRole('presidente', 'secretario');
        return view('insumos.index', [
            'gestion' => $gestion,
            'personas' => $this->personas($request),
            'items' => ItemInventario::with('tipoInsumo')->conExistencias()->whereNotNull('tipo_insumo_id')->orderBy('nombre')->get(),
            'horarios' => HorarioAtencion::orderBy('dia_semana')->orderBy('hora_inicio')->get(),
            'solicitudes' => SolicitudInsumo::query()
                ->when($gestion, fn ($q) => $q->whereIn('estado', ['pendiente', 'aprobado']))
                ->when(! $gestion, fn ($q) => $q->where('solicitante_id', $request->user()->id))
                ->latest('id')->paginate(20),
        ]);
    }

    public function store(Request $request)
    {
        $this->autorizarSolicitud($request);
        $gestion = $request->user()->hasRole('presidente', 'secretario');
        $datos = $request->validate([
            'solicitante' => $gestion ? ['required', 'string'] : ['exclude'],
            'grupo_id' => ['nullable', 'integer'],
            'telefono_contacto' => ['required', 'string', 'max:20'],
            'direccion_contacto' => ['nullable', 'string', 'max:255'],
            'fecha_comprometida' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'lineas' => ['required', 'array', 'min:1', 'max:100'],
            'lineas.*.item_inventario_id' => ['required', 'integer', 'distinct', Rule::exists('items_inventario', 'id')->whereNotNull('tipo_insumo_id')],
            'lineas.*.cantidad' => ['required', 'integer', 'min:1', 'max:4294967295'],
        ]);

        return DB::transaction(function () use ($request, $gestion, $datos) {
            $personas = $this->personas($request);
            $persona = $gestion ? $personas->firstWhere('clave', $datos['solicitante']) : $personas->first();
            if (! $persona) throw ValidationException::withMessages(['solicitante' => 'Selecciona un socio activo, dirigente o representante de grupo vigente.']);
            $grupos = collect($persona['grupos']);
            $grupoId = $datos['grupo_id'] ?? ($grupos->count() === 1 ? $grupos->first()['id'] : null);
            if (($grupos->isNotEmpty() && ! $grupos->contains('id', $grupoId)) || ($grupos->isEmpty() && $grupoId)) {
                throw ValidationException::withMessages(['grupo_id' => 'Selecciona la agrupación que representa el solicitante.']);
            }
            $solicitud = SolicitudInsumo::create([
                'estado' => 'pendiente', 'solicitante_id' => $persona['usuario_id'], 'socio_id' => $persona['socio_id'],
                'solicitante_nombre' => $persona['nombre'], 'grupo_id' => $grupoId,
                'grupo_nombre' => $grupos->firstWhere('id', $grupoId)['nombre'] ?? null,
                'telefono_contacto' => $datos['telefono_contacto'], 'direccion_contacto' => $datos['direccion_contacto'] ?? null,
                'correo_contacto' => $persona['correo'], 'canal' => $gestion && $persona['usuario_id'] !== $request->user()->id ? 'ventanilla' : 'en_linea',
                'fecha_comprometida' => $datos['fecha_comprometida'], 'registrado_por' => $request->user()->id,
            ]);
            foreach ($datos['lineas'] as $linea) {
                $item = ItemInventario::findOrFail($linea['item_inventario_id']);
                $solicitud->lineas()->create([
                    'item_inventario_id' => $item->id, 'nombre_insumo' => $item->nombre, 'cantidad' => $linea['cantidad'],
                ]);
            }
            return redirect()->route('insumos.show', $solicitud)->with('status', 'Solicitud de insumos registrada. Aún no reserva existencias.');
        });
    }

    public function show(Request $request, SolicitudInsumo $solicitud)
    {
        $this->autorizarConsulta($request, $solicitud);
        return view('insumos.show', [
            'solicitud' => $solicitud->load('lineas.prestamo'),
            'gestion' => $request->user()->hasRole('presidente', 'secretario'),
            'dirigentes' => User::whereIn('role', ['presidente', 'secretario', 'tesorero'])->orderBy('name')->get(),
            'horarios' => HorarioAtencion::orderBy('dia_semana')->orderBy('hora_inicio')->get(),
        ]);
    }

    public function aprobar(Request $request, SolicitudInsumo $solicitud)
    {
        $datos = $request->validate([
            'responsable_id' => ['required', 'integer', Rule::exists('users', 'id')->whereIn('role', ['presidente', 'secretario', 'tesorero'])],
            'monto' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'garantia' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'fecha_comprometida' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
        ]);
        return DB::transaction(function () use ($request, $solicitud, $datos) {
            $this->bloquear($solicitud, 'pendiente');
            $usuario = User::find($solicitud->solicitante_id);
            $socio = Socio::find($solicitud->socio_id);
            $grupo = Grupo::find($solicitud->grupo_id);
            $externo = ! $solicitud->solicitante_id && ! $solicitud->socio_id;
            $representa = $grupo && $grupo->tieneRepresentanteVigente() && ($externo
                ? $grupo->datosRepresentante()['nombre'] === $solicitud->solicitante_nombre
                : (($socio && $grupo->representante_id === $socio->id)
                    || ($usuario && $grupo->representante_usuario_id === $usuario->id)
                    || ($usuario?->socio && $grupo->representante_id === $usuario->socio->id)));
            if (($externo && ! $representa)
                || (! $externo && ! $usuario?->esDirigente() && $socio?->estado !== 'activo')
                || ($solicitud->grupo_id && ! $representa)) {
                throw ValidationException::withMessages(['operacion' => 'El solicitante o la representación ya no cumple las condiciones para aprobar el préstamo.']);
            }
            foreach ($solicitud->lineas as $linea) {
                $item = ItemInventario::conExistencias()->findOrFail($linea->item_inventario_id);
                if (! $item->tipo_insumo_id || $linea->cantidad > $item->disponibles()) {
                    throw ValidationException::withMessages(['operacion' => 'No hay existencias suficientes de '.$linea->nombre_insumo.'. No se aprobó ningún insumo.']);
                }
            }
            $responsable = User::whereIn('role', ['presidente', 'secretario', 'tesorero'])->find($datos['responsable_id']);
            if (! $responsable) throw ValidationException::withMessages(['responsable_id' => 'El responsable debe ser dirigente.']);
            $solicitud->update($datos + [
                'estado' => 'aprobado', 'responsable_nombre' => $responsable->name,
                'aprobado_por' => $request->user()->id, 'aprobado_en' => now(),
            ]);
            return back()->with('status', 'Solicitud aprobada. Todas las unidades quedaron reservadas.');
        });
    }

    public function entregar(Request $request, SolicitudInsumo $solicitud)
    {
        return DB::transaction(function () use ($request, $solicitud) {
            $this->bloquear($solicitud, 'aprobado');
            $datos = $request->validate([
                'fecha_entrega' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.$solicitud->created_at->toDateString(), 'before_or_equal:today', 'before_or_equal:'.$solicitud->fecha_comprometida->toDateString()],
                'condiciones' => ['required', 'array', 'size:'.$solicitud->lineas->count()],
            ]);
            foreach ($solicitud->lineas as $linea) {
                $request->validate(['condiciones.'.$linea->id => ['required', Rule::in(array_keys(Prestamo::CONDICIONES))]]);
                $item = ItemInventario::conExistencias()->findOrFail($linea->item_inventario_id);
                if ($item->disponibles() < 0) throw ValidationException::withMessages(['operacion' => 'Las existencias reservadas no son suficientes.']);
            }
            foreach ($solicitud->lineas as $linea) {
                $prestamo = Prestamo::create([
                    'solicitud_insumo_id' => $solicitud->id, 'item_inventario_id' => $linea->item_inventario_id,
                    'cantidad' => $linea->cantidad, 'grupo_id' => $solicitud->grupo_id,
                    'destinatario_nombre' => $solicitud->solicitante_nombre, 'responsable_nombre' => $solicitud->responsable_nombre,
                    'responsable_telefono' => User::find($solicitud->responsable_id)?->socio?->telefono ?? '',
                    'fecha_solicitud' => $solicitud->created_at->toDateString(), 'fecha_prestamo' => $datos['fecha_entrega'],
                    'estado_salida' => $request->input('condiciones.'.$linea->id), 'registrado_por' => $request->user()->id,
                ]);
                $linea->update(['prestamo_id' => $prestamo->id]);
            }
            $solicitud->update(['estado' => 'entregado', 'fecha_entrega' => $datos['fecha_entrega'], 'entregado_por' => $request->user()->id]);
            return back()->with('status', 'Entrega completa registrada. Las unidades siguen ocupadas hasta su devolución.');
        });
    }

    public function devolver(Request $request, SolicitudInsumo $solicitud)
    {
        return DB::transaction(function () use ($request, $solicitud) {
            $this->bloquear($solicitud, 'entregado');
            $datos = $request->validate([
                'fecha_devolucion' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.$solicitud->fecha_entrega->toDateString(), 'before_or_equal:today'],
                'devoluciones' => ['required', 'array', 'size:'.$solicitud->lineas->count()],
            ]);
            foreach ($solicitud->lineas as $linea) {
                $campo = 'devoluciones.'.$linea->id;
                $danado = $request->input($campo.'.estado_regreso') === 'danado';
                $request->validate([
                    $campo.'.estado_regreso' => ['required', Rule::in(array_keys(Prestamo::CONDICIONES))],
                    $campo.'.cantidad_danada' => ['required', 'integer', 'min:'.($danado ? 1 : 0), 'max:'.($danado ? $linea->cantidad : 0)],
                ]);
            }
            foreach ($solicitud->lineas as $linea) {
                $linea->prestamo->update([
                    'fecha_devolucion' => $datos['fecha_devolucion'],
                    'estado_regreso' => $request->input('devoluciones.'.$linea->id.'.estado_regreso'),
                    'cantidad_danada' => $request->integer('devoluciones.'.$linea->id.'.cantidad_danada'),
                    'devuelto_por' => $request->user()->id,
                ]);
            }
            $solicitud->update(['estado' => 'devuelto', 'fecha_devolucion' => $datos['fecha_devolucion'], 'devuelto_por' => $request->user()->id]);
            $garantia = $solicitud->garantiaRecibida()->first();
            if ($garantia?->estado === 'recibida') $garantia->update(['estado' => 'por_devolver']);
            return back()->with('status', 'Devolución completa registrada. Las unidades dañadas quedan fuera de servicio.');
        });
    }

    private function bloquear(SolicitudInsumo $solicitud, string $estado): void
    {
        DB::table('solicitudes_insumos')->where('id', $solicitud->id)->update(['estado' => DB::raw('estado')]);
        $solicitud->refresh()->load('lineas.prestamo');
        if ($solicitud->estado !== $estado) throw ValidationException::withMessages(['operacion' => 'La operación no corresponde al estado actual de la solicitud. Actualiza la página.']);
        // Orden único y bloqueo compartido con edición de cantidades y préstamos históricos.
        foreach ($solicitud->lineas->sortBy('item_inventario_id') as $linea) {
            DB::table('items_inventario')->where('id', $linea->item_inventario_id)->update(['cantidad' => DB::raw('cantidad')]);
        }
    }

    private function autorizarSolicitud(Request $request): void
    {
        abort_unless($request->user()->esDirigente() || $request->user()->esSocioActivo(), 403);
    }

    private function autorizarConsulta(Request $request, SolicitudInsumo $solicitud): void
    {
        abort_unless($request->user()->hasRole('presidente', 'secretario')
            || (($request->user()->esDirigente() || $request->user()->esSocioActivo()) && $solicitud->solicitante_id === $request->user()->id), 403);
    }

    private function personas(Request $request)
    {
        $gestion = $request->user()->hasRole('presidente', 'secretario');
        $grupos = Grupo::with('representante')->get()->filter->tieneRepresentanteVigente();
        $socios = $gestion ? Socio::with('user')->where('estado', 'activo')->get() : collect([$request->user()->socio])->filter(fn ($s) => $s?->estado === 'activo');
        $usuarios = $gestion ? User::with('socio')->whereIn('role', ['presidente', 'secretario', 'tesorero'])->get() : collect([$request->user()]);
        $personas = collect();
        foreach ($socios as $socio) {
            $personas->push(['clave' => 'socio:'.$socio->id, 'nombre' => $socio->nombre_completo,
                'usuario_id' => $socio->user_id, 'socio_id' => $socio->id,
                'telefono' => $socio->telefono, 'direccion' => $socio->direccion, 'correo' => $socio->user?->email,
                'grupos' => $grupos->filter(fn ($g) => $g->representante_id === $socio->id || ($socio->user_id && $g->representante_usuario_id === $socio->user_id))->map->only(['id', 'nombre'])->values()->all()]);
        }
        foreach ($usuarios as $usuario) {
            if (! $usuario->esDirigente() || $personas->contains('usuario_id', $usuario->id)) continue;
            $personas->push(['clave' => 'usuario:'.$usuario->id, 'nombre' => $usuario->name,
                'usuario_id' => $usuario->id, 'socio_id' => null,
                'telefono' => $usuario->socio?->telefono, 'direccion' => $usuario->socio?->direccion, 'correo' => $usuario->email,
                'grupos' => $grupos->filter(fn ($g) => $g->representante_usuario_id === $usuario->id || ($usuario->socio && $g->representante_id === $usuario->socio->id))->map->only(['id', 'nombre'])->values()->all()]);
        }
        if ($gestion) foreach ($grupos as $grupo) {
            if ($personas->contains(fn ($p) => collect($p['grupos'])->contains('id', $grupo->id))) continue;
            $persona = $grupo->datosRepresentante();
            $personas->push(['clave' => 'grupo:'.$grupo->id, 'nombre' => $persona['nombre'],
                'usuario_id' => null, 'socio_id' => null, 'telefono' => $persona['telefono'], 'direccion' => $persona['direccion'],
                'correo' => $persona['correo'], 'grupos' => [['id' => $grupo->id, 'nombre' => $grupo->nombre]]]);
        }
        return $personas->sortBy('nombre')->values();
    }
}
