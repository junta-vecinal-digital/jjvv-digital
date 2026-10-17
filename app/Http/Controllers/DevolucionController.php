<?php

namespace App\Http\Controllers;

use App\Models\Devolucion;
use App\Models\Ingreso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Presidente y tesorero: devolución de una cuota con motivo obligatorio (RFS-66). */
class DevolucionController extends Controller
{
    public function store(Request $request, Ingreso $ingreso)
    {
        $datos = $request->validate([
            'motivo' => ['required', 'string', 'max:500'],
        ]);

        return DB::transaction(function () use ($request, $ingreso, $datos) {
            // Comparte el bloqueo con anular; la escritura también bloquea en SQLite.
            DB::table('ingresos')->where('id', $ingreso->id)->update(['monto' => DB::raw('monto')]);
            $ingreso->refresh();

            if (! $ingreso->puedeDevolverse()) {
                return back()->withErrors(['devolucion' => 'Este ingreso no se puede devolver.']);
            }

            Devolucion::create([
                'ingreso_id' => $ingreso->id,
                'fecha_devolucion' => now()->toDateString(),
                'monto' => $ingreso->monto,            // devolución total, una sola vez
                'motivo' => $datos['motivo'],
                'registrado_por' => $request->user()->id,
            ]);

            return redirect()->route('ingresos.index')->with('status', 'devolucion-registrada');
        });
    }
}
