<?php

namespace App\Http\Controllers;

use App\Models\ItemInventario;
use App\Models\TipoInsumo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventarioController extends Controller
{
    public function index()
    {
        return view('inventario.index', [
            'items' => ItemInventario::with('tipoInsumo')->conExistencias()->orderBy('nombre')->paginate(20),
            'tiposInsumo' => TipoInsumo::whereDoesntHave('existencias')->orderBy('categoria')->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'tipo_insumo_id' => ['required', 'integer', 'exists:tipos_insumo,id'],
            'cantidad' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ]);

        DB::transaction(function () use ($request, $datos) {
            DB::table('tipos_insumo')->where('id', $datos['tipo_insumo_id'])->update(['nombre' => DB::raw('nombre')]);
            if (ItemInventario::where('tipo_insumo_id', $datos['tipo_insumo_id'])->exists()) {
                throw ValidationException::withMessages(['tipo_insumo_id' => 'Este insumo ya tiene existencias. Utiliza Editar cantidad.']);
            }
            $tipo = TipoInsumo::findOrFail($datos['tipo_insumo_id']);
            ItemInventario::create($datos + ['nombre' => $tipo->nombre, 'registrado_por' => $request->user()->id]);
        });

        return redirect()->route('inventario.index')->with('status', 'Ítem registrado.');
    }

    public function update(Request $request, ItemInventario $item)
    {
        $datos = $request->validate(['cantidad_total' => ['required', 'integer', 'min:0', 'max:4294967295']]);

        DB::transaction(function () use ($item, $datos) {
            // Comparte bloqueo con los préstamos y devoluciones antes de recalcular el mínimo.
            DB::table('items_inventario')->where('id', $item->id)->update(['cantidad' => DB::raw('cantidad')]);
            $actual = ItemInventario::conExistencias()->findOrFail($item->id);
            $minimo = $actual->cantidad - $actual->disponibles();
            if ($datos['cantidad_total'] < $minimo) {
                throw ValidationException::withMessages(['cantidad_total' => "El total no puede ser menor que {$minimo}: hay unidades reservadas, prestadas o fuera de servicio."]);
            }
            $actual->update(['cantidad' => $datos['cantidad_total']]);
        });

        return redirect()->route('inventario.index')->with('status', 'Cantidad total actualizada.');
    }
}
