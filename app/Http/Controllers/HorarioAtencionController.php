<?php

namespace App\Http\Controllers;

use App\Models\HorarioAtencion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HorarioAtencionController extends Controller
{
    public function index()
    {
        return view('atencion.index', [
            'horarios' => HorarioAtencion::orderBy('dia_semana')->orderBy('hora_inicio')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->guardar($request, new HorarioAtencion());

        return redirect()->route('atencion.index')->with('status', 'Horario publicado.');
    }

    public function update(Request $request, HorarioAtencion $horario)
    {
        $this->guardar($request, $horario);

        return redirect()->route('atencion.index')->with('status', 'Horario actualizado.');
    }

    public function destroy(HorarioAtencion $horario)
    {
        $horario->delete();

        return redirect()->route('atencion.index')->with('status', 'Horario retirado de la portada.');
    }

    private function guardar(Request $request, HorarioAtencion $horario): void
    {
        $datos = $request->validate([
            'dia_semana' => ['required', 'integer', 'between:1,7'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
        ], [
            'hora_fin.after' => 'La hora de término debe ser posterior a la de inicio.',
        ]);
        $datos['hora_inicio'] .= ':00';
        $datos['hora_fin'] .= ':00';

        DB::transaction(function () use ($datos, $horario) {
            // Serializa la comprobación y escritura también cuando aún no hay tramos.
            DB::table('parametros')->where('clave', 'sede_hora_inicio')->update(['valor' => DB::raw('valor')]);

            $superpuesto = HorarioAtencion::where('dia_semana', $datos['dia_semana'])
                ->when($horario->exists, fn ($q) => $q->where('id', '!=', $horario->id))
                ->where('hora_inicio', '<', $datos['hora_fin'])
                ->where('hora_fin', '>', $datos['hora_inicio'])->exists();

            if ($superpuesto) {
                throw ValidationException::withMessages(['hora_inicio' => 'Este tramo se superpone con otro horario de atención del mismo día.']);
            }

            $horario->fill($datos)->save();
        });
    }
}
