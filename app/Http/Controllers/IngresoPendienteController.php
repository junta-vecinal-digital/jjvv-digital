<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\SolicitudInsumo;

class IngresoPendienteController extends Controller
{
    public function index()
    {
        return view('finanzas.pendientes', [
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
}
