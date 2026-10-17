<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\HorarioFijo;
use App\Models\Reserva;
use App\Models\Socio;
use App\Models\TipoActividad;
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
        Reserva::liberarVencidos();
        $lunes = $this->lunesDe($request->query('semana'));
        ['inicios' => $horas, 'fines' => $horasFin] = Reserva::bloquesDeLaSede();
        $horaSugerida = collect($horas)->first(fn ($hora) => $hora > now()->format('H:i'));
        $fechaSugerida = $horaSugerida ? now()->toDateString() : now()->addDay()->toDateString();
        $horaSugerida ??= $horas[0] ?? '';
        $horaFinSugerida = $horasFin[array_search($horaSugerida, $horas)] ?? '';

        return view('reservas.index', compact('lunes', 'horas', 'horasFin', 'horaSugerida', 'fechaSugerida', 'horaFinSugerida') + [
            'reservas' => Reserva::with(['grupo', 'socio', 'creador'])->ocupan()
                ->where('fin', '>', $lunes)->where('inicio', '<', $lunes->copy()->addWeek())->orderBy('inicio')->get(),
            'grupos' => Grupo::with('representante')->orderBy('nombre')->get()->filter(fn ($g) => $g->tieneRepresentanteVigente()),
            'socios' => Socio::where('estado', 'activo')->orderBy('nombre_completo')->get(['id', 'nombre_completo', 'rut']),
            'tiposActividad' => TipoActividad::where('activo', true)->orderBy('id')->get(),
        ]);
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
            $responsable = $socioId ? Socio::findOrFail($socioId)->nombre_completo : $request->user()->name;

            if ($datos['tipo'] === 'grupo') {
                $grupo = Grupo::findOrFail($datos['grupo_id']);

                if (! $grupo->tieneRepresentanteVigente()) {
                    return $this->rechazar('El grupo no tiene representante vigente y no puede recibir reservas nuevas.');
                }

                $grupoId = $grupo->id;
                $socioId = $grupo->representante_id;
                $responsable = $grupo->datosRepresentante()['nombre'];
            }

            if ($error = Reserva::errorDeConflicto($inicio, $fin)) {
                return $this->rechazar($error);
            }

            $tipoActividad = TipoActividad::where('clave', $datos['tipo'])->firstOrFail();
            Reserva::create([
                'tipo' => $datos['tipo'],
                'estado' => 'confirmada',
                'inicio' => $inicio,
                'fin' => $fin,
                'motivo' => $tipoActividad->nombre,
                'tipo_nombre' => $tipoActividad->nombre,
                'responsable_nombre' => $responsable,
                'grupo_id' => $grupoId,
                'socio_id' => $socioId,
                'creado_por' => $request->user()->id,
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
                return back()->withErrors(['reserva' => 'Esta reserva ya está cancelada o vencida.']);
            }

            $reserva->update([
                'estado' => 'cancelada',
                'cancelada_en' => now(),
                'cancelada_por' => $request->user()->id,
            ]);

            if ($reserva->garantia?->estado === 'recibida') {
                $reserva->garantia->update(['estado' => 'por_devolver']);
            }

            return back()->with('status', 'reserva-cancelada');
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
