<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\HorarioAtencion;
use App\Models\ItemInventario;
use App\Models\Prestamo;
use App\Models\Socio;
use App\Models\SolicitudInsumo;
use App\Models\User;
use App\Rules\RutChileno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
            'solicitudes' => SolicitudInsumo::with('lineas.prestamo')
                ->when($gestion, fn ($q) => $q->whereIn('estado', ['pendiente', 'aprobado', 'entregado']))
                ->when(! $gestion, fn ($q) => $q->where('solicitante_id', $request->user()->id))
                ->latest('id')->paginate(20),
            'prestamosHistoricos' => $gestion ? Prestamo::with('item')->whereNull('solicitud_insumo_id')->whereNull('fecha_devolucion')->orderBy('fecha_prestamo')->get() : collect(),
        ]);
    }

    public function store(Request $request)
    {
        $this->autorizarSolicitud($request);
        $gestion = $request->user()->hasRole('presidente', 'secretario');
        if (is_string($request->solicitante_rut)) $request->merge(['solicitante_rut' => RutChileno::normalizar($request->solicitante_rut)]);
        $manual = $gestion && $request->solicitante === 'manual';
        $datos = $request->validate([
            'solicitante_nombre' => $manual ? ['required', 'string', 'max:255'] : ['exclude'],
            'solicitante' => $gestion ? ['required', 'string'] : ['exclude'],
            'solicitante_rut' => ['nullable', 'string', 'max:12', new RutChileno],
            'grupo_id' => ['nullable', 'integer'],
            'telefono_contacto' => ['required', 'string', 'max:20'],
            'direccion_contacto' => [$manual ? 'required' : 'nullable', 'string', 'max:255'],
            'fecha_comprometida' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'lineas' => ['required', 'array', 'min:1', 'max:100'],
            'lineas.*.item_inventario_id' => ['required', 'integer', 'distinct', Rule::exists('items_inventario', 'id')->whereNotNull('tipo_insumo_id')],
            'lineas.*.cantidad' => ['required', 'integer', 'min:1', 'max:4294967295'],
        ]);

        return DB::transaction(function () use ($request, $gestion, $datos, $manual) {
            $personas = $this->personas($request);
            $persona = $gestion ? $personas->firstWhere('clave', $datos['solicitante']) : $personas->first();
            if ($manual) $persona = [
                'usuario_id' => null, 'socio_id' => null, 'nombre' => $datos['solicitante_nombre'],
                'rut' => $datos['solicitante_rut'] ?? null, 'correo' => null, 'grupos' => [],
            ];
            if (! $persona) throw ValidationException::withMessages(['solicitante' => 'Selecciona un socio activo, dirigente o representante de grupo vigente.']);
            $rut = $persona['rut'] ?: ($datos['solicitante_rut'] ?? null);
            if (! RutChileno::esValido($rut)) {
                throw ValidationException::withMessages(['solicitante_rut' => 'Completa un RUT válido para el solicitante antes de solicitar insumos.']);
            }
            $grupos = collect($persona['grupos']);
            $grupoId = $datos['grupo_id'] ?? null;
            if (($grupoId && ! $grupos->contains('id', $grupoId)) || (str_starts_with($persona['clave'] ?? '', 'grupo:') && ! $grupoId)) {
                throw ValidationException::withMessages(['grupo_id' => 'Selecciona la agrupación que representa el solicitante.']);
            }
            $solicitud = SolicitudInsumo::create([
                'estado' => 'pendiente', 'solicitante_id' => $persona['usuario_id'], 'socio_id' => $persona['socio_id'],
                'solicitante_nombre' => $persona['nombre'], 'grupo_id' => $grupoId,
                'solicitante_rut' => $rut,
                'grupo_nombre' => $grupos->firstWhere('id', $grupoId)['nombre'] ?? null,
                'telefono_contacto' => $datos['telefono_contacto'], 'direccion_contacto' => $datos['direccion_contacto'] ?? null,
                'correo_contacto' => $persona['correo'], 'canal' => $gestion && $persona['usuario_id'] !== $request->user()->id ? 'ventanilla' : 'en_linea',
                'fecha_comprometida' => $datos['fecha_comprometida'], 'registrado_por' => $request->user()->id,
                'responsable_id' => $gestion && $persona['usuario_id'] !== $request->user()->id ? $request->user()->id : null,
                'responsable_nombre' => $gestion && $persona['usuario_id'] !== $request->user()->id ? $request->user()->name : null,
                'responsable_cargo' => $gestion && $persona['usuario_id'] !== $request->user()->id ? $request->user()->role : null,
            ]);
            foreach ($datos['lineas'] as $linea) {
                $item = ItemInventario::findOrFail($linea['item_inventario_id']);
                $solicitud->lineas()->create([
                    'item_inventario_id' => $item->id, 'nombre_insumo' => $item->nombre, 'cantidad' => $linea['cantidad'],
                ]);
            }
            if ($solicitud->canal === 'ventanilla') {
                $request->validate([
                    'monto' => ['required', 'integer', 'min:0', 'max:4294967295'],
                    'garantia' => ['required', 'integer', 'min:0', 'max:4294967295'],
                ]);
                $this->aprobar($request, $solicitud);
                if ($request->boolean('entregar_ahora') && ! $solicitud->requierePago()) {
                    $request->merge([
                        'fecha_entrega' => today()->toDateString(),
                        'condiciones' => $solicitud->lineas->mapWithKeys(fn ($linea) => [$linea->id => $request->input('estados_salida.'.$linea->item_inventario_id, 'bueno')])->all(),
                    ]);
                    $this->entregar($request, $solicitud);
                }
                return redirect()->route('insumos.index')->withInput(['operacion' => 'insumo_'.$solicitud->id])->with('status', $solicitud->estado === 'entregado' ? 'Préstamo registrado y entregado.' : 'Préstamo registrado y aprobado. Las unidades quedaron reservadas.');
            }
            return redirect()->route('insumos.index')->withInput(['operacion' => 'insumo_'.$solicitud->id])->with('status', 'Solicitud de insumos registrada.');
        });
    }

    public function show(Request $request, SolicitudInsumo $solicitud)
    {
        $this->autorizarConsulta($request, $solicitud);
        return view('insumos.show', [
            'solicitud' => $solicitud->load('lineas.prestamo'),
            'gestion' => $request->user()->hasRole('presidente', 'secretario') && $request->query('consulta') !== 'historial',
            'horarios' => HorarioAtencion::orderBy('dia_semana')->orderBy('hora_inicio')->get(),
        ]);
    }

    public function aprobar(Request $request, SolicitudInsumo $solicitud)
    {
        $datos = $request->validate([
            'monto' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'garantia' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ]);
        return DB::transaction(function () use ($request, $solicitud, $datos) {
            $this->bloquear($solicitud, 'pendiente');
            if ($solicitud->fecha_comprometida->lt(today())) {
                throw ValidationException::withMessages(['operacion' => 'La fecha comprometida ya pasó. Registra una nueva solicitud con una fecha vigente.']);
            }
            $usuario = User::find($solicitud->solicitante_id);
            $socio = Socio::find($solicitud->socio_id);
            $grupo = Grupo::find($solicitud->grupo_id);
            $externo = ! $solicitud->solicitante_id && ! $solicitud->socio_id;
            $representa = $grupo && $grupo->tieneRepresentanteVigente() && ($externo
                ? $grupo->datosRepresentante()['nombre'] === $solicitud->solicitante_nombre
                    && (! $grupo->datosRepresentante()['rut'] || $grupo->datosRepresentante()['rut'] === $solicitud->solicitante_rut)
                : (($socio && $grupo->representante_id === $socio->id)
                    || ($usuario && $grupo->representante_usuario_id === $usuario->id)
                    || ($usuario?->socio && $grupo->representante_id === $usuario->socio->id)));
            if (($externo && $solicitud->canal !== 'ventanilla')
                || (! $externo && $socio?->estado !== 'activo')
                || ($solicitud->grupo_id && ! $representa)) {
                throw ValidationException::withMessages(['operacion' => 'El solicitante o la representación ya no cumple las condiciones para aprobar el préstamo.']);
            }
            foreach ($solicitud->lineas as $linea) {
                $item = ItemInventario::conExistencias()->findOrFail($linea->item_inventario_id);
                if (! $item->tipo_insumo_id || $linea->cantidad > $item->disponibles()) {
                    throw ValidationException::withMessages(['operacion' => 'No hay existencias suficientes de '.$linea->nombre_insumo.'. No se aprobó ningún insumo.']);
                }
            }
            if (! in_array($solicitud->responsable_cargo, ['presidente', 'secretario'], true)) {
                $registrador = $solicitud->canal === 'ventanilla' ? User::find($solicitud->registrado_por) : null;
                $responsable = $registrador?->hasRole('presidente', 'secretario') ? $registrador : $request->user();
                $solicitud->fill(['responsable_id' => $responsable->id, 'responsable_nombre' => $responsable->name, 'responsable_cargo' => $responsable->role]);
            }
            $solicitud->update($datos + [
                'estado' => 'aprobado',
                'aprobado_por' => $request->user()->id, 'aprobado_en' => now(),
            ]);
            return back()->withInput($request->only('operacion'))->with('status', 'Solicitud aprobada. Todas las unidades quedaron reservadas.');
        });
    }

    public function entregar(Request $request, SolicitudInsumo $solicitud)
    {
        return DB::transaction(function () use ($request, $solicitud) {
            $this->bloquear($solicitud, 'aprobado');
            if (! $solicitud->puedeEntregarse()) {
                throw ValidationException::withMessages(['operacion' => 'Primero debes validar el pago del aporte y la garantía.']);
            }
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
            return back()->withInput($request->only('operacion'))->with('status', 'Entrega completa registrada. Las unidades siguen ocupadas hasta su devolución.');
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
            return back()->withInput($request->only('operacion'))->with('status', 'Devolución completa registrada. Las unidades dañadas quedan fuera de servicio.');
        });
    }

    public function adjuntarPago(Request $request, SolicitudInsumo $solicitud)
    {
        $this->autorizarConsulta($request, $solicitud);
        $datos = $request->validate([
            'fecha_pago' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.$solicitud->created_at->toDateString(), 'before_or_equal:today'],
            'medio_pago' => ['required', Rule::in(array_keys(\App\Models\Ingreso::MEDIOS))],
            'talonario' => ['nullable', 'required_if:medio_pago,efectivo', 'string', 'max:30'],
            'numero_transaccion' => ['nullable', 'string', 'max:50'],
            'comprobante' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);
        return DB::transaction(function () use ($request, $solicitud, $datos) {
            $this->bloquear($solicitud, 'aprobado');
            if (! $solicitud->requierePago() || $solicitud->pago_validado_en) {
                throw ValidationException::withMessages(['pago' => 'Esta operación no admite un nuevo comprobante.']);
            }
            $datos['comprobante_path'] = $request->file('comprobante')->store('comprobantes/insumos', 'local');
            unset($datos['comprobante']);
            $datos['talonario'] = $datos['medio_pago'] === 'efectivo' ? $datos['talonario'] : null;
            $datos['numero_transaccion'] = $datos['medio_pago'] === 'transferencia' ? ($datos['numero_transaccion'] ?? null) : null;
            $solicitud->update($datos);
            return back()->withInput($request->only('operacion'))->with('status', 'Comprobante guardado. Pendiente de validación por presidente o secretario.');
        });
    }

    public function validarPago(Request $request, SolicitudInsumo $solicitud)
    {
        $request->validate(['pago_verificado' => ['accepted']]);
        return DB::transaction(function () use ($request, $solicitud) {
            $this->bloquear($solicitud, 'aprobado');
            if (! $solicitud->requierePago() || $solicitud->pago_validado_en || ! $solicitud->comprobante_path || ! $solicitud->fecha_pago) {
                throw ValidationException::withMessages(['pago' => 'Debe existir un comprobante pendiente de validar.']);
            }
            $solicitud->update(['pago_validado_en' => now(), 'pago_validado_por' => $request->user()->id]);
            if ($solicitud->canal === 'ventanilla' && $request->boolean('entregar_ahora')) {
                $this->entregar($request, $solicitud);
                return back()->withInput($request->only('operacion'))->with('status', 'Pago validado y entrega registrada. El ingreso queda pendiente en Finanzas.');
            }
            return back()->withInput($request->only('operacion'))->with('status', 'Pago validado. Puedes registrar la entrega; el ingreso se aprobará por separado en Finanzas.');
        });
    }

    public function comprobante(Request $request, SolicitudInsumo $solicitud)
    {
        if (! $request->user()->hasRole('presidente', 'tesorero')) $this->autorizarConsulta($request, $solicitud);
        abort_unless($solicitud->comprobante_path && Storage::disk('local')->exists($solicitud->comprobante_path), 404);
        return Storage::disk('local')->response($solicitud->comprobante_path, null, ['Cache-Control' => 'private, no-store']);
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
        abort_unless($request->user()->hasRole('presidente', 'secretario') || $request->user()->esSocioActivo(), 403);
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
        $personas = collect();
        foreach ($socios as $socio) {
            $personas->push(['clave' => 'socio:'.$socio->id, 'nombre' => $socio->nombre_completo,
                'rut' => $socio->rut,
                'usuario_id' => $socio->user_id, 'socio_id' => $socio->id,
                'telefono' => $socio->telefono, 'direccion' => $socio->direccion, 'correo' => $socio->user?->email,
                'grupos' => $grupos->filter(fn ($g) => $g->representante_id === $socio->id || ($socio->user_id && $g->representante_usuario_id === $socio->user_id))->map->only(['id', 'nombre'])->values()->all()]);
        }
        if ($gestion) foreach ($grupos as $grupo) {
            if ($personas->contains(fn ($p) => collect($p['grupos'])->contains('id', $grupo->id)) || $grupo->representante?->user?->esDirigente()
                || User::find($grupo->representante_usuario_id)?->esDirigente()) continue;
            $persona = $grupo->datosRepresentante();
            $personas->push(['clave' => 'grupo:'.$grupo->id, 'nombre' => $persona['nombre'],
                'rut' => $persona['rut'],
                'usuario_id' => null, 'socio_id' => null, 'telefono' => $persona['telefono'], 'direccion' => $persona['direccion'],
                'correo' => $persona['correo'], 'grupos' => [['id' => $grupo->id, 'nombre' => $grupo->nombre]]]);
        }
        return $personas->sortBy('nombre')->values();
    }
}
