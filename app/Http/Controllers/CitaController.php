<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    // ---------- Vecino: reservar hora ----------

    public function index(Request $request)
    {
        $socio = $request->user()->socio;
        $cita = $socio?->cita;

        $disponibles = collect();

        if ($socio?->estado === 'por_firmar' && ! $cita) {
            $disponibles = Cita::whereNull('socio_id')
                ->where('inicio', '>', now())
                ->where('inicio', '<', now()->addDays(30))
                ->orderBy('inicio')
                ->get()
                ->groupBy(fn ($c) => $c->inicio->toDateString());
        }

        return view('citas.index', compact('socio', 'cita', 'disponibles'));
    }

    public function reservar(Request $request, Cita $cita)
    {
        $socio = $request->user()->socio;

        abort_unless($socio && $socio->estado === 'por_firmar', 403);

        if ($socio->cita) {
            return back()->withErrors(['cita' => 'Ya tienes una hora reservada. Cancélala si quieres cambiarla.']);
        }

        try {
            // Update condicionado: solo gana quien encuentre la hora todavía libre
            $tomada = Cita::whereKey($cita->id)
                ->whereNull('socio_id')
                ->where('inicio', '>', now())
                ->update(['socio_id' => $socio->id]);
        } catch (UniqueConstraintViolationException) {
            $tomada = 0;
        }

        if (! $tomada) {
            return back()->withErrors(['cita' => 'Esa hora ya no está disponible. Elige otra.']);
        }

        return redirect()->route('citas.index')->with('status', 'cita-reservada');
    }

    public function cancelar(Request $request)
    {
        $cita = $request->user()->socio?->cita;

        abort_unless($cita && $cita->inicio->isFuture(), 404);

        $cita->update(['socio_id' => null]);

        return redirect()->route('citas.index')->with('status', 'cita-cancelada');
    }

    // ---------- Dirigentes: agenda ----------

    public function agenda()
    {
        $citas = Cita::with('socio')
            ->where('inicio', '>=', now()->startOfDay())
            ->orderBy('inicio')
            ->get()
            ->groupBy(fn ($c) => $c->inicio->toDateString());

        return view('citas.agenda', compact('citas'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'fecha' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:' . now()->addMonths(6)->toDateString()],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'duracion' => ['required', 'integer', 'in:15,20,30,45,60'],
        ], [
            'fecha.after_or_equal' => 'La fecha no puede ser anterior a hoy.',
            'fecha.before_or_equal' => 'Solo puedes crear horarios con hasta 6 meses de anticipación.',
            'hora_fin.after' => 'La hora de término debe ser posterior a la hora de inicio.',
        ]);

        $duracion = (int) $datos['duracion'];
        $cursor = Carbon::parse("{$datos['fecha']} {$datos['hora_inicio']}");
        $fin = Carbon::parse("{$datos['fecha']} {$datos['hora_fin']}");

        $creadas = 0;

        while ($cursor->copy()->addMinutes($duracion)->lte($fin)) {
            if ($cursor->isFuture()) {
                $cita = Cita::firstOrCreate(
                    ['inicio' => $cursor->copy()],
                    ['creado_por' => $request->user()->id]
                );

                if ($cita->wasRecentlyCreated) {
                    $creadas++;
                }
            }

            $cursor->addMinutes($duracion);
        }

        $mensaje = $creadas > 0
            ? "Se crearon {$creadas} horarios nuevos."
            : 'No se crearon horarios nuevos: ya existían o el rango está en el pasado.';

        return redirect()->route('agenda.index')->with('mensaje', $mensaje);
    }

    public function liberar(Cita $cita)
    {
        $cita->update(['socio_id' => null]);

        return back()->with('mensaje', 'La hora quedó disponible nuevamente.');
    }

    public function destroy(Cita $cita)
    {
        if ($cita->socio_id) {
            return back()->withErrors(['agenda' => 'No puedes eliminar una hora reservada. Libérala primero.']);
        }

        $cita->delete();

        return back()->with('mensaje', 'Horario eliminado.');
    }
}