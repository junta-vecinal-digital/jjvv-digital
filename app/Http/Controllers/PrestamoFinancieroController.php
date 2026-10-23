<?php

namespace App\Http\Controllers;

use App\Models\Garantia;
use App\Models\Ingreso;
use App\Models\SolicitudInsumo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PrestamoFinancieroController extends Controller
{
    public function index()
    {
        return redirect()->route('finanzas.arriendos.index');
    }

    public function show(SolicitudInsumo $solicitud)
    {
        abort_if($solicitud->estado === 'pendiente', 404);
        return view('finanzas.insumos.show', ['solicitud' => $solicitud->load(['lineas', 'ingresos', 'garantiaRecibida.ingresos'])]);
    }

    public function registrar(Request $request, SolicitudInsumo $solicitud)
    {
        return DB::transaction(function () use ($request, $solicitud) {
            $this->bloquear($solicitud);
            if ($solicitud->pago_registrado_en || $solicitud->ingresos()->exists() || $solicitud->garantiaRecibida()->exists()) {
                throw ValidationException::withMessages(['finanzas' => 'La recepción ya fue registrada. Si anulaste el ingreso, usa Volver a registrar cobro.']);
            }
            if ($solicitud->monto == 0 && $solicitud->garantia == 0) {
                throw ValidationException::withMessages(['finanzas' => 'Un préstamo gratuito sin garantía no genera movimientos financieros.']);
            }
            if (! $solicitud->pago_validado_en) {
                throw ValidationException::withMessages(['finanzas' => 'Presidente o secretario debe validar primero el pago.']);
            }
            $solicitud->update(['pago_registrado_en' => now(), 'pago_registrado_por' => $request->user()->id]);
            if ($solicitud->monto > 0) $this->crearIngreso($solicitud, $request->user()->id);
            if ($solicitud->garantia > 0) {
                Garantia::create([
                    'solicitud_insumo_id' => $solicitud->id, 'monto' => $solicitud->garantia,
                    'estado' => $solicitud->estado === 'devuelto' ? 'por_devolver' : 'recibida',
                    'recibida_en' => $solicitud->pago_validado_en, 'validado_por' => $solicitud->pago_validado_por,
                    'registrada_en' => now(), 'registrada_por' => $request->user()->id,
                    'fecha_pago' => $solicitud->fecha_pago, 'medio_pago' => $solicitud->medio_pago,
                    'talonario' => $solicitud->talonario,
                ]);
            }
            return back()->with('status', 'Recepción registrada: el aporte es ingreso y la garantía se conserva separada.');
        });
    }

    public function reingresar(Request $request, SolicitudInsumo $solicitud)
    {
        return DB::transaction(function () use ($request, $solicitud) {
            $this->bloquear($solicitud);
            if (! $solicitud->pago_registrado_en || $solicitud->monto <= 0 || $solicitud->ingresos()->vigentes()->exists()) {
                throw ValidationException::withMessages(['finanzas' => 'Solo puedes volver a registrar un aporte positivo recibido cuyo ingreso esté anulado.']);
            }
            $this->crearIngreso($solicitud, $request->user()->id);
            return back()->with('status', 'Aporte registrado nuevamente. La recepción original y la garantía se conservan.');
        });
    }

    public function comprobante(SolicitudInsumo $solicitud)
    {
        abort_unless($solicitud->pago_registrado_en && $solicitud->comprobante_path
            && Storage::disk('local')->exists($solicitud->comprobante_path), 404);
        return Storage::disk('local')->response($solicitud->comprobante_path);
    }

    private function bloquear(SolicitudInsumo $solicitud): void
    {
        // Comparte serialización con el registro y la resolución de garantías históricas.
        DB::table('parametros')->where('clave', 'sede_hora_inicio')->update(['valor' => DB::raw('valor')]);
        DB::table('solicitudes_insumos')->where('id', $solicitud->id)->update(['estado' => DB::raw('estado')]);
        $solicitud->refresh();
        if (! in_array($solicitud->estado, ['aprobado', 'entregado', 'devuelto'], true)) {
            throw ValidationException::withMessages(['finanzas' => 'Primero debe aprobarse la solicitud y definirse sus montos.']);
        }
    }

    private function crearIngreso(SolicitudInsumo $solicitud, int $autor): void
    {
        Ingreso::create([
            'solicitud_insumo_id' => $solicitud->id, 'concepto' => 'prestamo_insumos',
            'monto' => $solicitud->monto, 'fecha_pago' => $solicitud->fecha_pago,
            'medio_pago' => $solicitud->medio_pago, 'talonario' => $solicitud->talonario,
            'numero_transaccion' => $solicitud->numero_transaccion,
            'comprobante_path' => $solicitud->comprobante_path,
            'observaciones' => 'Aporte por préstamo de insumos #'.$solicitud->id.'. Garantía separada de los ingresos.',
            'registrado_por' => $autor,
        ]);
    }
}
