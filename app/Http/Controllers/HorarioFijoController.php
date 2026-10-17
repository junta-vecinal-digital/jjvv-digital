<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\HorarioFijo;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Horarios fijos de grupo y generación mensual de sus reservas (RFU-34, RFS-47, RFS-49, RFS-42). */
class HorarioFijoController extends Controller
{
    public function store(Request $request, Grupo $grupo)
    {
        return DB::transaction(function () use ($request, $grupo) {
            // La escritura sin cambios serializa reservas y horarios también en SQLite.
            DB::table('parametros')->where('clave', 'sede_hora_inicio')->update(['valor' => DB::raw('valor')]);

            if (! $grupo->tieneRepresentanteVigente()) {
                return $this->rechazar('Asigna un representante vigente al grupo antes de agregar horarios fijos.');
            }

            $datos = $this->validar($request);

            if ($error = HorarioFijo::errorDeConflicto($datos['dia_semana'], $datos['hora_inicio'], $datos['hora_fin'])) {
                return $this->rechazar($error);
            }

            $grupo->horariosFijos()->create($datos);

            return redirect()->route('grupos.show', $grupo)->with('status', 'horario-guardado');
        });
    }

    /** RFS-49: editar no cambia las reservas ya generadas, solo las que se generen después. */
    public function update(Request $request, HorarioFijo $horario)
    {
        return DB::transaction(function () use ($request, $horario) {
            // La escritura sin cambios serializa reservas y horarios también en SQLite.
            DB::table('parametros')->where('clave', 'sede_hora_inicio')->update(['valor' => DB::raw('valor')]);

            $datos = $this->validar($request);

            if ($error = HorarioFijo::errorDeConflicto($datos['dia_semana'], $datos['hora_inicio'], $datos['hora_fin'], $horario->id)) {
                return $this->rechazar($error);
            }

            $horario->update($datos);

            return redirect()->route('grupos.show', $horario->grupo_id)->with('status', 'horario-guardado');
        });
    }

    /** Las reservas ya generadas se mantienen (horario_fijo_id queda en null). */
    public function destroy(HorarioFijo $horario)
    {
        return DB::transaction(function () use ($horario) {
            // La escritura sin cambios serializa reservas y horarios también en SQLite.
            DB::table('parametros')->where('clave', 'sede_hora_inicio')->update(['valor' => DB::raw('valor')]);

            $horario->delete();

            return redirect()->route('grupos.show', $horario->grupo_id)->with('status', 'horario-guardado');
        });
    }

    /** RFS-47: genera las reservas confirmadas de un mes para todos los horarios fijos del grupo. */
    public function generar(Request $request, Grupo $grupo)
    {
        return DB::transaction(function () use ($request, $grupo) {
            // La escritura sin cambios serializa reservas y horarios también en SQLite.
            DB::table('parametros')->where('clave', 'sede_hora_inicio')->update(['valor' => DB::raw('valor')]);

            $datos = $request->validate([
                'mes' => ['required', 'date_format:Y-m'],
            ]);

            $primero = Carbon::createFromFormat('!Y-m', $datos['mes']);

            if ($primero->lt(now()->startOfMonth()) || $primero->gt(now()->addMonths(6)->startOfMonth())) {
                return $this->rechazar('Elige un mes entre el actual y los próximos 6 meses.');
            }

            if (! $grupo->tieneRepresentanteVigente()) {
                return $this->rechazar('El grupo no tiene representante vigente: no se pueden generar reservas.');
            }

            $horarios = $grupo->horariosFijos;

            if ($horarios->isEmpty()) {
                return $this->rechazar('El grupo no tiene horarios fijos para generar.');
            }

            $creadas = 0;
            $existentes = 0;
            $omitidas = [];

            foreach ($horarios as $horario) {
                for ($dia = $primero->copy(); $dia->month === $primero->month; $dia->addDay()) {
                    if ($dia->isoWeekday() !== (int) $horario->dia_semana) {
                        continue;
                    }

                    $inicio = Carbon::parse($dia->toDateString().' '.$horario->hora_inicio);
                    $fin = Carbon::parse($dia->toDateString().' '.$horario->hora_fin);

                    if ($inicio->isPast()) {
                        continue;
                    }

                    $yaGenerada = Reserva::where('horario_fijo_id', $horario->id)
                        ->where('inicio', $inicio)
                        ->exists();

                    if ($yaGenerada) {
                        $existentes++;

                        continue;
                    }

                    if ($error = Reserva::errorDeConflicto($inicio, $fin, $horario->id)) {
                        $omitidas[] = ucfirst($inicio->translatedFormat('l j \d\e F, H:i')).': '.$error;

                        continue;
                    }

                    Reserva::create([
                        'tipo' => 'grupo',
                        'estado' => 'confirmada',
                        'inicio' => $inicio,
                        'fin' => $fin,
                        'motivo' => 'Horario fijo',
                        'grupo_id' => $grupo->id,
                        'socio_id' => $grupo->representante_id,
                        'horario_fijo_id' => $horario->id,
                        'creado_por' => $request->user()->id,
                    ]);

                    $creadas++;
                }
            }

            $mensaje = "Se generaron {$creadas} reservas para ".$primero->translatedFormat('F \d\e Y').'.';

            if ($existentes > 0) {
                $mensaje .= " {$existentes} ya estaban generadas.";
            }

            return redirect()->route('grupos.show', $grupo)
                ->with('mensaje', $mensaje)
                ->with('omitidas', $omitidas);
        });
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'dia_semana' => ['required', 'integer', 'between:1,7'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i'],
        ]);
    }

    private function rechazar(string $mensaje)
    {
        return back()->withErrors(['horario' => $mensaje]);
    }
}
