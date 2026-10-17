<?php

namespace App\Http\Controllers;

use App\Models\Egreso;
use App\Models\Ingreso;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/** Presidente y tesorero: egresos y su anulación (RFS-65, 67, 68). */
class EgresoController extends Controller
{
    public function index()
    {
        return view('finanzas.egresos.index', [
            'egresos' => Egreso::with('registrador')->latest('fecha_pago')->latest('id')->paginate(20),
            'saldo' => Ingreso::saldoCaja(),
        ]);
    }

    public function create()
    {
        return view('finanzas.egresos.create', [
            'conceptos' => Egreso::CONCEPTOS,
            'pagadores' => User::whereIn('role', ['presidente', 'tesorero'])->pluck('name')
                ->merge(Egreso::query()->distinct()->pluck('pagado_por'))->unique()->sort()->values(),
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'concepto' => ['required', Rule::in(array_keys(Egreso::CONCEPTOS))],
            'fecha_pago' => ['required', 'date', 'before_or_equal:today'],
            'monto' => ['required', 'integer', 'min:1', 'max:100000000'],
            'numero_transaccion' => ['required', 'string', 'max:50'],
            'pagado_por' => ['required', 'string', 'max:100'],
            'observaciones' => ['nullable', 'string', 'max:500'],
            'comprobante' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        // Disco privado, igual que los comprobantes de certificados.
        $datos['comprobante_path'] = $request->file('comprobante')->store('comprobantes/egresos', 'local');
        unset($datos['comprobante']);

        $datos['registrado_por'] = $request->user()->id;

        Egreso::create($datos);

        return redirect()->route('egresos.index')->with('status', 'egreso-registrado');
    }

    public function anular(Request $request, Egreso $egreso)
    {
        $datos = $request->validate([
            'motivo_anulacion' => ['required', 'string', 'max:500'],
        ]);

        if ($egreso->estaAnulado()) {
            return back()->withErrors(['egreso' => 'Este egreso ya está anulado.']);
        }

        $egreso->anular($datos['motivo_anulacion'], $request->user());

        return redirect()->route('egresos.index')->with('status', 'egreso-anulado');
    }

    public function comprobante(Egreso $egreso)
    {
        abort_unless(
            $egreso->comprobante_path && Storage::disk('local')->exists($egreso->comprobante_path),
            404
        );

        return Storage::disk('local')->response($egreso->comprobante_path);
    }
}
