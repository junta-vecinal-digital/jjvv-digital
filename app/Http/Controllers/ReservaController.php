<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\HorarioFijo;
use App\Models\Reserva;
use App\Models\Socio;
use App\Models\TipoActividad;
use App\Rules\RutChileno;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Calendario de la sede e ingreso de reservas (RFU-31 a RFU-33, RFS-43, RFS-44, RFS-55, RFS-56). */
class ReservaController extends Controller
{
    public function index(Request $request)
    {
        Reserva::liberarVencidos();
        $usuario = $request->user();
        $esDirigente = $usuario->hasRole('presidente', 'secretario');

        abort_unless(
            $esDirigente || $usuario->esSocioActivo(),
            403,
            'El calendario es solo para socios con inscripción activa.'
        );

        $lunes = $this->lunesDe($request->query('semana'));
        $dias = collect(range(0, 6))->map(fn ($i) => $lunes->copy()->addDays($i));
        ['inicios' => $horas, 'fines' => $horasFin] = Reserva::bloquesDeLaSede();

        $celdas = []; // [fecha][hora] => ['tipo' => 'reserva'|'fijo', 'texto' => ...]

        $reservas = Reserva::with(['grupo', 'socio', 'creador'])
            ->ocupan()
            ->where('fin', '>', $lunes)
            ->where('inicio', '<', $lunes->copy()->addDays(7))
            ->orderBy('inicio')->get();

        $horariosFijos = HorarioFijo::with([
            'grupo',
            'reservas' => fn ($q) => $q->where('estado', 'cancelada')
                ->where('inicio', '>=', $lunes)->where('inicio', '<', $lunes->copy()->addWeek()),
        ])->get()->groupBy('dia_semana');

        // La ocupación depende del intervalo real, aunque hayan cambiado los bloques de la grilla.
        foreach ($dias as $dia) {
            foreach ($horas as $indice => $hora) {
                $inicio = $dia->copy()->setTimeFromTimeString($hora);
                $fin = $dia->copy()->setTimeFromTimeString($horasFin[$indice]);
                $ocupantes = $reservas->filter(fn ($r) => $r->inicio->lt($fin) && $r->fin->gt($inicio));

                if ($ocupantes->isNotEmpty()) {
                    $celdas[$dia->toDateString()][$hora] = [
                        'detalles' => $esDirigente ? $ocupantes->map(fn ($r) => [
                            'titulo' => trim(($r->grupo ? $r->grupo->nombre.' · ' : '').$r->motivo),
                            'horario' => $r->inicio->format('d/m/Y H:i').'–'.$r->fin->format('H:i'),
                            'estado' => Reserva::ESTADOS[$r->estado] ?? $r->estado,
                            'persona' => $r->responsable_nombre ?? $r->socio?->nombre_completo ?? 'Sin registrar',
                            'autor' => $r->creador?->name ?? 'Sin cuenta vinculada', 'fijo' => false,
                        ])->values()->all() : [],
                        'tipo' => 'reserva',
                        'texto' => $esDirigente
                            ? $ocupantes->map(fn ($r) => trim(($r->grupo ? $r->grupo->nombre.' · ' : '').$r->motivo))->implode(' / ')
                            : 'Ocupado',
                    ];

                    continue;
                }

                // Los horarios fijos ocupan el espacio aunque su mes aún no se haya generado (RFS-48).
                $fijos = $horariosFijos->get($dia->isoWeekday(), collect())->filter(
                    fn ($hf) => ! $hf->reservas->contains(fn ($r) => $r->inicio->isSameDay($dia))
                        && $dia->copy()->setTimeFromTimeString($hf->hora_inicio)->lt($fin)
                        && $dia->copy()->setTimeFromTimeString($hf->hora_fin)->gt($inicio)
                );

                if ($fijos->isNotEmpty()) {
                    $celdas[$dia->toDateString()][$hora] = [
                        'detalles' => $esDirigente ? $fijos->map(fn ($hf) => [
                            'titulo' => $hf->grupo->nombre.' · Horario fijo',
                            'horario' => $dia->format('d/m/Y').' '.substr($hf->hora_inicio,0,5).'–'.substr($hf->hora_fin,0,5),
                            'estado' => 'Programado', 'persona' => $hf->grupo->datosRepresentante()['nombre'] ?? 'Sin representante',
                            'autor' => 'Horario fijo de agrupación', 'fijo' => true,
                        ])->values()->all() : [],
                        'tipo' => 'fijo',
                        'texto' => $esDirigente
                            ? $fijos->map(fn ($hf) => $hf->grupo->nombre.' · horario fijo')->implode(' / ')
                            : 'Ocupado',
                    ];
                }
            }
        }

        return view('calendario.index', compact(
            'esDirigente', 'lunes', 'dias', 'horas', 'celdas'
        ));
    }

    public function gestion(Request $request)
    {
        return app(ArriendoController::class)->index($request);
    }

    /** Horario semanal o mensual para impresión (RFU-38, RFS-57). */
    public function imprimir(Request $request)
    {
        $datos = $request->validate([
            'periodo' => ['required', 'in:semana,mes'],
            'fecha' => ['required', 'date_format:Y-m-d'],
        ]);
        Reserva::liberarVencidos();
        $fecha = Carbon::parse($datos['fecha']);
        $desde = $datos['periodo'] === 'mes' ? $fecha->copy()->startOfMonth() : $fecha->copy()->startOfWeek();
        $hasta = $datos['periodo'] === 'mes' ? $desde->copy()->addMonth() : $desde->copy()->addWeek();
        $reservas = Reserva::with('grupo')->ocupan()
            ->where('inicio', '<', $hasta)->where('fin', '>', $desde)->orderBy('inicio')->get();
        $fijos = HorarioFijo::with([
            'grupo',
            'reservas' => fn ($q) => $q->where('estado', 'cancelada')
                ->where('inicio', '>=', $desde)->where('inicio', '<', $hasta),
        ])->get();
        $agenda = [];
        for ($dia = $desde->copy(); $dia->lt($hasta); $dia->addDay()) {
            $delDia = $reservas->filter(fn ($r) => $r->inicio->lt($dia->copy()->addDay()) && $r->fin->gt($dia));
            $filas = $delDia->map(fn ($r) => [
                'inicio' => $r->inicio->format('H:i'),
                'fin' => $r->fin->format('H:i'),
                'actividad' => ($r->grupo ? $r->grupo->nombre.' · ' : '').$r->motivo,
                'estado' => Reserva::ESTADOS[$r->estado],
            ]);
            foreach ($fijos->where('dia_semana', $dia->isoWeekday()) as $fijo) {
                if ($fijo->reservas->contains(fn ($r) => $r->inicio->isSameDay($dia))) {
                    continue;
                }
                $inicio = substr($fijo->hora_inicio, 0, 5);
                $fin = substr($fijo->hora_fin, 0, 5);
                if ($delDia->contains(fn ($r) => $r->horario_fijo_id === $fijo->id
                    && $r->inicio->format('H:i') === $inicio && $r->fin->format('H:i') === $fin)) {
                    continue;
                }
                $filas->push([
                    'inicio' => $inicio, 'fin' => $fin,
                    'actividad' => ($fijo->grupo?->nombre ?? 'Grupo').' · Horario fijo',
                    'estado' => 'Programado (reserva aún sin generar)',
                ]);
            }
            $agenda[] = ['fecha' => $dia->copy(), 'filas' => $filas->sortBy('inicio')->values()];
        }

        return view('calendario.imprimir', [
            'periodo' => $datos['periodo'], 'desde' => $desde, 'hasta' => $hasta->copy()->subDay(),
            'agenda' => $agenda, 'emitidoEn' => now(),
        ]);
    }

    /** RFU-33: el presidente o el secretario ingresa una reserva. */
    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            // La escritura sin cambios serializa reservas y horarios también en SQLite.
            DB::table('parametros')->where('clave', 'sede_hora_inicio')->update(['valor' => DB::raw('valor')]);

            $datos = $request->validate([
                'fecha' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:'.now()->addMonths(6)->toDateString()],
                'hora_inicio' => ['required', 'date_format:H:i'],
                'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
                'responsable_nombre' => ['nullable', 'string', 'max:255'],
                'responsable_rut' => ['nullable', 'string', 'max:12', new RutChileno],
                'responsable_direccion' => ['nullable', 'string', 'max:255'],
                'responsable_telefono' => ['nullable', 'string', 'max:20'],
                'tipo' => ['required', 'string', 'max:20', Rule::exists('tipos_actividad', 'clave')->where('activo', true)],
                'grupo_id' => ['exclude_unless:tipo,grupo', 'required', 'exists:grupos,id'],
                'socio_id' => ['exclude_unless:tipo,particular', 'required', Rule::exists('socios', 'id')->where('estado', 'activo')],
            ], [
                'fecha.after_or_equal' => 'La fecha no puede ser anterior a hoy.',
                'fecha.before_or_equal' => 'Solo puedes reservar con hasta 6 meses de anticipación.',
                'hora_fin.after' => 'La hora de término debe ser posterior a la de inicio.',
                'grupo_id.required' => 'Elige el grupo.',
                'socio_id.required' => 'Elige al socio responsable de la actividad particular.',
                'socio_id.exists' => 'El responsable debe ser un socio activo.',
            ]);

            $inicio = Carbon::parse("{$datos['fecha']} {$datos['hora_inicio']}");
            $fin = Carbon::parse("{$datos['fecha']} {$datos['hora_fin']}");

            if ($inicio->isPast()) {
                return $this->rechazar('No se puede reservar un horario que ya comenzó.');
            }

            $grupoId = null;
            $socioId = $datos['tipo'] === 'particular' ? $datos['socio_id'] : null;
            $responsable = $datos['responsable_nombre'] ?? ($socioId ? Socio::findOrFail($socioId)->nombre_completo : $request->user()->name);

            if ($datos['tipo'] === 'grupo') {
                $grupo = Grupo::findOrFail($datos['grupo_id']);

                if (! $grupo->tieneRepresentanteVigente()) {
                    return $this->rechazar('El grupo no tiene representante vigente y no puede recibir reservas nuevas.');
                }
                if (! RutChileno::esValido($grupo->datosRepresentante()['rut'])) {
                    return $this->rechazar('Completa un RUT válido del representante del grupo antes de reservar.');
                }

                $grupoId = $grupo->id;
                $socioId = $grupo->representante_id;
                $responsable = $grupo->datosRepresentante()['nombre'];
            }

            if ($error = Reserva::errorDeConflicto($inicio, $fin)) {
                return $this->rechazar($error);
            }

            $tipoActividad = TipoActividad::where('clave', $datos['tipo'])->firstOrFail();
            $request->validate(['garantia_reserva' => ['nullable', 'integer', 'min:0', 'max:4294967295']]);
            $conGarantia = $request->integer('garantia_reserva') > 0;
            $comprobante = $conGarantia ? $request->validate(['comprobante' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120']]) : [];
            Reserva::create([
                'tipo' => $conGarantia ? 'arriendo' : $datos['tipo'],
                'estado' => $conGarantia ? 'pendiente' : 'confirmada',
                'inicio' => $inicio,
                'fin' => $fin,
                'motivo' => $tipoActividad->nombre,
                'tipo_nombre' => $tipoActividad->nombre,
                'responsable_nombre' => $responsable,
                'responsable_direccion' => $datos['responsable_direccion'] ?? null,
                'responsable_telefono' => $datos['responsable_telefono'] ?? null,
                'responsable_rut' => $datos['responsable_rut'] ?? ($grupoId ? $grupo->datosRepresentante()['rut'] : ($socioId ? Socio::findOrFail($socioId)->rut : $request->user()->socio?->rut)),
                'grupo_id' => $grupoId,
                'socio_id' => $socioId,
                'creado_por' => $request->user()->id,
                ...($conGarantia ? [
                    'aporte_variable' => true, 'valor_arriendo' => 0, 'valor_garantia' => $request->integer('garantia_reserva'),
                    'aporte_definido_por' => $request->user()->id, 'aporte_definido_en' => now(),
                    'canal' => 'ventanilla', 'vence_en' => now()->addHours(\App\Models\Parametro::entero('arriendo_plazo_confirmacion_horas')),
                    'comprobante_arriendo_path' => isset($comprobante['comprobante']) ? $comprobante['comprobante']->store('comprobantes/arriendos', 'local') : null,
                ] : []),
            ]);

            return redirect()
                ->route('reservas.index', ['semana' => $inicio->copy()->startOfWeek()->toDateString()])
                ->with('status', 'reserva-creada');
        });
    }

    public function cancelar(Request $request, Reserva $reserva)
    {
        return DB::transaction(function () use ($request, $reserva) {
            DB::table('parametros')->where('clave', 'sede_hora_inicio')->update(['valor' => DB::raw('valor')]);
            Reserva::liberarVencidos();
            $reserva->refresh();

            if (! in_array($reserva->estado, Reserva::ESTADOS_OCUPAN, true)) {
                return back()->withInput()->withErrors(['reserva' => 'Esta reserva ya está cancelada o vencida.']);
            }

            $reserva->update([
                'estado' => 'cancelada',
                'cancelada_en' => now(),
                'cancelada_por' => $request->user()->id,
            ]);

            if ($reserva->garantia?->estado === 'recibida') {
                $reserva->garantia->update(['estado' => 'por_devolver']);
            }

            return back()->withInput($request->only('operacion'))->with('status', 'reserva-cancelada');
        });
    }

    private function rechazar(string $mensaje)
    {
        return back()->withInput()->withErrors(['reserva' => $mensaje]);
    }

    private function lunesDe(?string $semana): Carbon
    {
        try {
            $fecha = $semana ? Carbon::createFromFormat('Y-m-d', $semana) : now();
        } catch (\Throwable) {
            $fecha = now();
        }

        return $fecha->copy()->startOfWeek();
    }
}
