@extends('reportes.pdf-layout')
@section('titulo', 'Reporte financiero')
@section('contenido')
    <p>{{ $grupoNombre }} · Del {{ \Carbon\Carbon::parse($filtros['desde'])->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($filtros['hasta'])->format('d/m/Y') }}</p>
    <table><thead><tr><th>Pagos recibidos vigentes</th><th>Egresos vigentes</th><th>Cuotas devueltas</th><th>Resultado neto del período</th></tr></thead><tbody><tr>@foreach (['ingresos', 'egresos', 'devoluciones', 'neto'] as $clave)<td>${{ number_format($totales[$clave], 0, ',', '.') }}</td>@endforeach</tr></tbody></table>
    <p class="nota">Pagos y egresos por su fecha de pago; cuotas devueltas por su fecha de devolución. Se excluyen los movimientos actualmente anulados. Las garantías recibidas y devueltas no se suman a los totales; las retenciones vigentes ya están incluidas como ingresos. Este resultado corresponde al período seleccionado, no al saldo histórico de caja.</p>
    <p class="nota">Sin agrupación identifica movimientos sin vínculo guardado, incluidos egresos e ingresos manuales. No se deducen vínculos a partir de nombres o conceptos.</p>
    @include('reportes._finanzas-tablas')
@endsection
