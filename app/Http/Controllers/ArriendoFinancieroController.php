<?php

namespace App\Http\Controllers;

use App\Models\Garantia;
use App\Models\Ingreso;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Presidente y tesorero: registro financiero posterior a la validación del arriendo. */
class ArriendoFinancieroController extends Controller
{
    public function index()
    {
        return view('finanzas.arriendos.index', [
            'garantias' => Garantia::with(['reserva.socio', 'solicitudInsumo', 'ingresos'])
                ->latest('id')->paginate(20),
        ]);
    }

    public function registrar(Request $request, Reserva $reserva)
    {
        $datos = $request->validate([
            'fecha_pago' => ['required', 'date', 'before_or_equal:today'],
            'medio_pago' => ['required', Rule::in(array_keys(Ingreso::MEDIOS))],
            'talonario' => ['nullable', 'required_if:medio_pago,efectivo', 'string', 'max:30'],
        ]);

        return DB::transaction(function () use ($request, $reserva, $datos) {
            DB::table('parametros')->where('clave', 'sede_hora_inicio')->update(['valor' => DB::raw('valor')]);
            $reserva->refresh();
            abort_unless($reserva->tipo === 'arriendo' && $reserva->validado_en, 422, 'El arriendo debe estar validado.');
            $ingresoVigente = $reserva->ingresos()->vigentes()->exists();
            $garantia = $reserva->garantia;
            $registrarGarantia = $garantia && $garantia->monto > 0 && ! $garantia->registrada_en;
            if ($reserva->valor_arriendo <= 0 && ! $registrarGarantia) {
                return back()->withInput()->withErrors(['finanzas' => 'Una reserva gratuita no genera ingresos.']);
            }
            if ($ingresoVigente && ! $registrarGarantia) {
                return back()->withInput()->withErrors(['finanzas' => 'Este arriendo ya tiene un ingreso vigente.']);
            }

            if ($reserva->valor_arriendo > 0 && ! $ingresoVigente) Ingreso::create([
                'reserva_id' => $reserva->id,
                'numero_transaccion' => $reserva->numero_transaccion,
                'concepto' => $reserva->aporte_variable ? 'aporte_reserva' : 'arriendo',
                'fecha_pago' => $datos['fecha_pago'],
                'monto' => $reserva->valor_arriendo,
                'medio_pago' => $datos['medio_pago'],
                'talonario' => $datos['medio_pago'] === 'efectivo' ? $datos['talonario'] : null,
                'observaciones' => $reserva->aporte_variable ? 'Aporte por reserva de sede #'.$reserva->id.'.'
                    : 'Aporte por uso de sede #'.$reserva->id.'. Comprobante compartido con la garantía.',
                'registrado_por' => $request->user()->id,
            ]);
            if ($registrarGarantia) {
                $garantia->update([
                    'registrada_en' => now(),
                    'registrada_por' => $request->user()->id,
                    'fecha_pago' => $datos['fecha_pago'],
                    'medio_pago' => $datos['medio_pago'],
                    'talonario' => $datos['medio_pago'] === 'efectivo' ? $datos['talonario'] : null,
                ]);
            }

            return back()->with('status', 'arriendo-registrado');
        });
    }

    public function porDevolver(Garantia $garantia)
    {
        return DB::transaction(function () use ($garantia) {
            DB::table('parametros')->where('clave', 'sede_hora_inicio')->update(['valor' => DB::raw('valor')]);
            $garantia->refresh();
            $this->validarDevolucionInsumos($garantia);
            if ($garantia->estado !== 'recibida') {
                return back()->withInput()->withErrors(['finanzas' => 'Solo una garantía recibida puede quedar por devolver.']);
            }
            $garantia->update(['estado' => 'por_devolver']);

            return back()->with('status', 'garantia-por-devolver');
        });
    }

    public function devolver(Request $request, Garantia $garantia)
    {
        $datos = $request->validate(['fecha_resolucion' => ['required', 'date', 'before_or_equal:today']]);

        return DB::transaction(function () use ($request, $garantia, $datos) {
            DB::table('parametros')->where('clave', 'sede_hora_inicio')->update(['valor' => DB::raw('valor')]);
            $garantia->refresh();
            $this->validarDevolucionInsumos($garantia, $datos['fecha_resolucion']);
            if (! $garantia->registrada_en || ! in_array($garantia->estado, ['recibida', 'por_devolver'], true)) {
                return back()->withInput()->withErrors(['finanzas' => 'La garantía debe estar registrada y pendiente de resolución.']);
            }
            if ($datos['fecha_resolucion'] < $garantia->fecha_pago->toDateString()) {
                return back()->withInput()->withErrors(['fecha_resolucion' => 'La devolución no puede ser anterior al pago.']);
            }
            $garantia->update([
                'estado' => 'devuelta',
                'fecha_resolucion' => $datos['fecha_resolucion'],
                'resuelta_en' => now(),
                'resuelta_por' => $request->user()->id,
                'monto_devuelto' => $garantia->monto,
                'monto_retenido' => 0,
            ]);

            return back()->with('status', 'garantia-resuelta');
        });
    }

    public function retener(Request $request, Garantia $garantia)
    {
        $datos = $request->validate([
            'fecha_resolucion' => ['required', 'date', 'before_or_equal:today'],
            'monto_retenido' => ['required', 'integer', 'min:1'],
            'motivo_retencion' => ['required', 'string', 'max:500'],
        ]);

        return DB::transaction(function () use ($request, $garantia, $datos) {
            DB::table('parametros')->where('clave', 'sede_hora_inicio')->update(['valor' => DB::raw('valor')]);
            $garantia->refresh();
            $this->validarDevolucionInsumos($garantia, $datos['fecha_resolucion']);
            if (! $garantia->registrada_en || ! in_array($garantia->estado, ['recibida', 'por_devolver'], true)) {
                return back()->withInput()->withErrors(['finanzas' => 'La garantía debe estar registrada y pendiente de resolución.']);
            }
            if ($datos['fecha_resolucion'] < $garantia->fecha_pago->toDateString()) {
                return back()->withInput()->withErrors(['fecha_resolucion' => 'La resolución no puede ser anterior al pago.']);
            }
            if ($datos['monto_retenido'] > $garantia->monto) {
                return back()->withInput()->withErrors(['monto_retenido' => 'El monto retenido no puede superar la garantía.']);
            }
            $garantia->update([
                'estado' => 'retenida',
                'fecha_resolucion' => $datos['fecha_resolucion'],
                'resuelta_en' => now(),
                'resuelta_por' => $request->user()->id,
                'monto_retenido' => $datos['monto_retenido'],
                'monto_devuelto' => $garantia->monto - $datos['monto_retenido'],
                'motivo_retencion' => $datos['motivo_retencion'],
            ]);
            $this->crearIngresoRetencion($garantia, $request->user()->id);

            return back()->with('status', 'garantia-resuelta');
        });
    }

    public function registrarRetencion(Request $request, Garantia $garantia)
    {
        return DB::transaction(function () use ($request, $garantia) {
            DB::table('parametros')->where('clave', 'sede_hora_inicio')->update(['valor' => DB::raw('valor')]);
            $garantia->refresh();
            if ($garantia->estado !== 'retenida' || $garantia->ingresos()->vigentes()->exists()) {
                return back()->withInput()->withErrors(['finanzas' => 'La retención ya tiene un ingreso vigente o la garantía no está retenida.']);
            }
            $this->crearIngresoRetencion($garantia, $request->user()->id);

            return back()->with('status', 'retencion-registrada');
        });
    }

    private function crearIngresoRetencion(Garantia $garantia, int $autor): void
    {
        Ingreso::create([
            'garantia_id' => $garantia->id,
            'concepto' => 'otros',
            'fecha_pago' => $garantia->fecha_resolucion,
            'monto' => $garantia->monto_retenido,
            'medio_pago' => $garantia->medio_pago,
            'talonario' => $garantia->talonario,
            'numero_transaccion' => $garantia->solicitudInsumo?->numero_transaccion,
            'observaciones' => $garantia->solicitud_insumo_id
                ? 'Retención por daños del préstamo de insumos #'.$garantia->solicitud_insumo_id.': '.$garantia->motivo_retencion
                : 'Retención por daños del arriendo #'.$garantia->reserva_id.': '.$garantia->motivo_retencion,
            'registrado_por' => $autor,
        ]);
    }

    private function validarDevolucionInsumos(Garantia $garantia, ?string $fecha = null): void
    {
        if (! $garantia->solicitud_insumo_id) return;
        $solicitud = $garantia->solicitudInsumo;
        if ($solicitud->estado !== 'devuelto') {
            throw ValidationException::withMessages(['finanzas' => 'Registra primero la devolución completa de los insumos para resolver su garantía.']);
        }
        if ($fecha && $fecha < $solicitud->fecha_devolucion->toDateString()) {
            throw ValidationException::withMessages(['fecha_resolucion' => 'La resolución no puede ser anterior a la devolución de los insumos.']);
        }
    }
}
