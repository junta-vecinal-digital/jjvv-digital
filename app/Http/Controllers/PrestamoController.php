<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PrestamoController extends Controller
{
    public function index(Request $request)
    {
        return app(InventarioController::class)->index($request);
    }

    public function create()
    {
        return redirect()->route('insumos.index');
    }

    public function store(Request $request)
    {
        return redirect()->route('insumos.index')->withErrors(['operacion' => 'Los préstamos nuevos deben comenzar por una solicitud de insumos.']);
    }

    public function show(Prestamo $prestamo)
    {
        if ($prestamo->solicitud_insumo_id) return redirect()->route('insumos.show', $prestamo->solicitud_insumo_id);
        return view('inventario.prestamos.show', ['prestamo' => $prestamo->load('item', 'registrador', 'receptor')]);
    }

    public function devolver(Request $request, Prestamo $prestamo)
    {
        abort_if($prestamo->solicitud_insumo_id, 422, 'Registra la devolución completa desde la solicitud de insumos.');
        $conDanos = $request->input('estado_regreso') === 'danado';
        $datos = $request->validate([
            'fecha_devolucion' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.$prestamo->fecha_prestamo->toDateString(), 'before_or_equal:today'],
            'estado_regreso' => ['required', Rule::in(array_keys(Prestamo::CONDICIONES))],
            'cantidad_danada' => [$conDanos ? 'required' : 'sometimes', 'integer', 'min:'.($conDanos ? 1 : 0), 'max:'.($conDanos ? $prestamo->cantidad : 0)],
        ]);

        return DB::transaction(function () use ($request, $prestamo, $datos) {
            DB::table('items_inventario')->where('id', $prestamo->item_inventario_id)->update(['cantidad' => DB::raw('cantidad')]);
            $prestamo->refresh();
            if ($prestamo->fecha_devolucion !== null) {
                throw ValidationException::withMessages(['devolucion' => 'Este préstamo ya fue devuelto.']);
            }

            $prestamo->update($datos + ['cantidad_danada' => 0, 'devuelto_por' => $request->user()->id]);

            return redirect()->route('inventario.prestamos.show', $prestamo)->with('status', 'Devolución completa registrada. Las unidades dañadas quedan fuera de servicio.');
        });
    }
}
