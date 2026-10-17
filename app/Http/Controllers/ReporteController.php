<?php

namespace App\Http\Controllers;

use App\Exports\SociosActivosExcel;
use App\Models\Devolucion;
use App\Models\Egreso;
use App\Models\Grupo;
use App\Models\Ingreso;
use App\Models\Reserva;
use App\Models\Socio;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReporteController extends Controller
{
    public function index()
    {
        return view('reportes.index');
    }

    public function financieros(Request $request)
    {
        return view('reportes.financieros', $this->datosFinancieros($request));
    }

    public function financierosPdf(Request $request)
    {
        $datos = $this->datosFinancieros($request);
        return Pdf::loadView('reportes.financieros-pdf', $datos)->setPaper('a4', 'landscape')
            ->download('reporte-financiero-'.$datos['filtros']['desde'].'-'.$datos['filtros']['hasta'].'.pdf')
            ->header('Cache-Control', 'private, no-store');
    }

    public function socios(Request $request)
    {
        return view('reportes.socios', [
            'socios' => $this->sociosActivos($request)->paginate(25)->withQueryString(),
            'buscar' => $request->input('buscar', ''),
        ]);
    }

    public function sociosPdf(Request $request)
    {
        return Pdf::loadView('reportes.socios-pdf', [
            'socios' => $this->sociosActivos($request)->get(), 'buscar' => $request->input('buscar', ''), 'emitidoEn' => now(),
        ])->setPaper('a4')->download('socios-activos-'.now()->format('Y-m-d').'.pdf')->header('Cache-Control', 'private, no-store');
    }

    public function sociosExcel(Request $request)
    {
        return SociosActivosExcel::descargar($this->sociosActivos($request)->get());
    }

    private function sociosActivos(Request $request)
    {
        $datos = $request->validate(['buscar' => ['nullable', 'string', 'max:100']]);
        $buscar = trim($datos['buscar'] ?? '');
        return Socio::select(['id', 'nombre_completo', 'rut', 'estado', 'fecha_ingreso'])->where('estado', 'activo')
            ->when($buscar !== '', fn ($q) => $q->where(fn ($q) => $q->where('nombre_completo', 'like', '%'.$buscar.'%')->orWhere('rut', 'like', '%'.$buscar.'%')))
            ->orderBy('nombre_completo')->orderBy('id');
    }

    private function datosFinancieros(Request $request): array
    {
        $request->merge([
            'desde' => $request->input('desde', now()->startOfMonth()->toDateString()),
            'hasta' => $request->input('hasta', now()->endOfMonth()->toDateString()),
        ]);
        $filtros = $request->validate([
            'desde' => ['required', 'date_format:Y-m-d'],
            'hasta' => ['required', 'date_format:Y-m-d', 'after_or_equal:desde'],
            'grupo_id' => ['nullable', Rule::when($request->input('grupo_id') !== 'sin_grupo', ['integer', 'exists:grupos,id'])],
        ]);
        $vinculos = ['reserva.grupo', 'solicitudInsumo', 'garantia.reserva.grupo', 'garantia.solicitudInsumo'];
        $ingresos = Ingreso::with([...$vinculos, 'registrador'])->vigentes()->whereDate('fecha_pago', '>=', $filtros['desde'])->whereDate('fecha_pago', '<=', $filtros['hasta'])->get();
        $egresos = Egreso::with('registrador')->vigentes()->whereDate('fecha_pago', '>=', $filtros['desde'])->whereDate('fecha_pago', '<=', $filtros['hasta'])->get();
        $devoluciones = Devolucion::with([...array_map(fn ($v) => 'ingreso.'.$v, $vinculos), 'registrador'])
            ->whereDate('fecha_devolucion', '>=', $filtros['desde'])->whereDate('fecha_devolucion', '<=', $filtros['hasta'])->get();

        $movimientos = collect();
        foreach ($ingresos as $ingreso) {
            $movimientos->push($this->filaMovimiento($ingreso, 'Ingreso', $ingreso->fecha_pago,
                Ingreso::CONCEPTOS[$ingreso->concepto] ?? $ingreso->concepto, $this->grupoDelIngreso($ingreso)));
        }
        foreach ($egresos as $egreso) {
            $movimientos->push($this->filaMovimiento($egreso, 'Egreso', $egreso->fecha_pago,
                Egreso::CONCEPTOS[$egreso->concepto] ?? $egreso->concepto, [null, 'Sin agrupación']));
        }
        foreach ($devoluciones as $devolucion) {
            $movimientos->push($this->filaMovimiento($devolucion, 'Devolución', $devolucion->fecha_devolucion,
                'Devolución de cuota #'.$devolucion->ingreso_id, $this->grupoDelIngreso($devolucion->ingreso)));
        }
        $movimientos = $movimientos->filter(fn ($fila) => $this->coincideGrupo($fila['grupo_id'], $filtros['grupo_id'] ?? null))
            ->sortBy([['fecha', 'asc'], ['tipo', 'asc'], ['id', 'asc']])->values();
        $totales = [
            'ingresos' => (int) $movimientos->where('tipo', 'Ingreso')->sum('monto'),
            'egresos' => (int) $movimientos->where('tipo', 'Egreso')->sum('monto'),
            'devoluciones' => (int) $movimientos->where('tipo', 'Devolución')->sum('monto'),
        ];
        $totales['neto'] = $totales['ingresos'] - $totales['egresos'] - $totales['devoluciones'];

        $reservas = Reserva::with(['grupo', 'socio'])->where('tipo', 'arriendo')
            ->where('inicio', '>=', $filtros['desde'].' 00:00:00')->where('inicio', '<=', $filtros['hasta'].' 23:59:59')
            ->orderBy('inicio')->orderBy('id')->get()
            ->filter(fn ($r) => $this->coincideGrupo($r->grupo_id, $filtros['grupo_id'] ?? null))->values();
        $grupos = Grupo::orderBy('nombre')->get(['id', 'nombre']);
        $grupoNombre = empty($filtros['grupo_id']) ? 'Todas las agrupaciones'
            : ($filtros['grupo_id'] === 'sin_grupo' ? 'Sin agrupación' : $grupos->firstWhere('id', $filtros['grupo_id'])->nombre);
        return compact('filtros', 'movimientos', 'reservas', 'totales', 'grupos', 'grupoNombre') + ['emitidoEn' => now()];
    }

    private function grupoDelIngreso(Ingreso $ingreso): array
    {
        $solicitud = $ingreso->solicitudInsumo ?? $ingreso->garantia?->solicitudInsumo;
        if ($solicitud?->grupo_id) return [$solicitud->grupo_id, $solicitud->grupo_nombre ?? 'Agrupación #'.$solicitud->grupo_id];
        $reserva = $ingreso->reserva ?? $ingreso->garantia?->reserva;
        if ($reserva?->grupo_id) return [$reserva->grupo_id, $reserva->grupo->nombre];
        return [null, 'Sin agrupación'];
    }

    private function filaMovimiento($modelo, string $tipo, $fecha, string $concepto, array $grupo): array
    {
        return [
            'id' => $modelo->id, 'tipo' => $tipo, 'fecha' => $fecha, 'concepto' => $concepto,
            'grupo_id' => $grupo[0], 'grupo_nombre' => $grupo[1], 'monto' => (int) $modelo->monto,
            'autor' => $modelo->registrador?->name ?? 'Cuenta eliminada',
        ];
    }

    private function coincideGrupo($id, $filtro): bool
    {
        if ($filtro === null || $filtro === '') return true;
        return $filtro === 'sin_grupo' ? $id === null : (int) $id === (int) $filtro;
    }
}
