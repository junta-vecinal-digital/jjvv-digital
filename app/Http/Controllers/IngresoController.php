<?php

namespace App\Http\Controllers;

use App\Models\Ingreso;
use App\Models\Reserva;
use App\Models\SolicitudInsumo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/** Presidente y tesorero: ingresos, cuotas y anulaciones (RFS-58, 59, 60, 64, 67, 68). */
class IngresoController extends Controller
{
    public function index()
    {
        return view('finanzas.ingresos.index', [
            'ingresos' => Ingreso::with(['registrador', 'devolucion', 'reserva.ingresos', 'reserva.garantia', 'garantia.ingresos', 'solicitudInsumo.ingresos'])->latest('fecha_pago')->latest('id')->paginate(20)->withQueryString(),
            'saldo' => Ingreso::saldoCaja(),
            'reservas' => Reserva::with(['socio', 'garantia', 'ingresos'])->where('tipo', 'arriendo')->whereNotNull('validado_en')
                ->where(fn ($q) => $q->where(fn ($q) => $q->where('valor_arriendo', '>', 0)->whereDoesntHave('ingresos', fn ($q) => $q->vigentes()))
                    ->orWhereHas('garantia', fn ($q) => $q->where('monto', '>', 0)->whereNull('registrada_en')))
                ->oldest('validado_en')->paginate(15, ['*'], 'reservas')->withQueryString(),
            'prestamos' => SolicitudInsumo::with(['ingresos', 'lineas'])->whereIn('estado', ['aprobado', 'entregado', 'devuelto'])
                ->where(fn ($q) => $q->where('monto', '>', 0)->orWhere('garantia', '>', 0))
                ->where(fn ($q) => $q->whereNull('pago_registrado_en')
                    ->orWhere(fn ($q) => $q->where('monto', '>', 0)->whereDoesntHave('ingresos', fn ($q) => $q->vigentes())))
                ->oldest('aprobado_en')->paginate(15, ['*'], 'prestamos')->withQueryString(),
        ]);
    }

    public function create()
    {
        return view('finanzas.ingresos.create', [
            'conceptos' => array_diff_key(Ingreso::CONCEPTOS, ['arriendo' => true, 'aporte_reserva' => true, 'prestamo_insumos' => true]),
            'tiposCuota' => Ingreso::TIPOS_CUOTA,
            'medios' => Ingreso::MEDIOS,
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'concepto' => ['required', Rule::in(array_diff(array_keys(Ingreso::CONCEPTOS), ['arriendo', 'aporte_reserva', 'prestamo_insumos']))],
            'tipo_cuota' => ['nullable', 'required_if:concepto,cuota_socios,aporte_grupos', Rule::in(array_keys(Ingreso::TIPOS_CUOTA))],
            'fecha_pago' => ['required', 'date', 'before_or_equal:today'],
            'monto' => ['required', 'integer', 'min:1', 'max:100000000'],
            'medio_pago' => ['required', Rule::in(array_keys(Ingreso::MEDIOS))],
            'talonario' => ['nullable', 'required_if:medio_pago,efectivo', 'string', 'max:30'],
            'numero_transaccion' => ['nullable', Rule::requiredIf($request->medio_pago === 'transferencia' && ! $request->hasFile('comprobante')), 'string', 'max:50'],
            'comprobante' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'observaciones' => ['nullable', 'required_if:concepto,otros', 'string', 'max:500'],
        ]);

        // RFS-60: las cuotas y los aportes se clasifican como ordinarios o extraordinarios.
        if (! in_array($datos['concepto'], ['cuota_socios', 'aporte_grupos'], true)) {
            $datos['tipo_cuota'] = null;
        }

        $datos['registrado_por'] = $request->user()->id;
        $datos['comprobante_path'] = $request->file('comprobante')?->store('comprobantes/ingresos', 'local');
        unset($datos['comprobante']);

        Ingreso::create($datos);

        return redirect()->route('ingresos.index')->with('status', 'ingreso-registrado');
    }

    public function comprobante(Ingreso $ingreso)
    {
        abort_unless($ingreso->comprobante_path && Storage::disk('local')->exists($ingreso->comprobante_path), 404);
        return Storage::disk('local')->response($ingreso->comprobante_path, null, ['Cache-Control' => 'private, no-store']);
    }

    public function anular(Request $request, Ingreso $ingreso)
    {
        $datos = $request->validate([
            'motivo_anulacion' => ['required', 'string', 'max:500'],
        ]);

        return DB::transaction(function () use ($request, $ingreso, $datos) {
            // Comparte el bloqueo con devolver; la escritura también bloquea en SQLite.
            DB::table('ingresos')->where('id', $ingreso->id)->update(['monto' => DB::raw('monto')]);
            $ingreso->refresh();

            if ($ingreso->estaAnulado()) {
                return back()->withInput()->withErrors(['ingreso' => 'Este ingreso ya está anulado.']);
            }

            // Si ya tiene devolución, anularlo descontaría el dinero dos veces del saldo.
            if ($ingreso->devolucion()->exists()) {
                return back()->withInput()->withErrors(['ingreso' => 'Este ingreso ya fue devuelto y no se puede anular.']);
            }

            $ingreso->anular($datos['motivo_anulacion'], $request->user());

            return redirect()->route('ingresos.index')->with('status', 'ingreso-anulado');
        });
    }
}
