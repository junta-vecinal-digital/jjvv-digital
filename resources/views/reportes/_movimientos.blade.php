<h2 class="text-lg font-semibold mt-6 mb-3">Movimientos del período</h2>
<table class="ui-table">
    <thead><tr><th>Fecha</th><th>Registro</th><th>Concepto</th><th>Agrupación</th><th>Monto (CLP)</th><th>Registrado por</th></tr></thead>
    <tbody>
        @forelse ($movimientos as $movimiento)
            <tr><td>{{ $movimiento['fecha']->format('d/m/Y') }}</td><td>{{ $movimiento['tipo'] }} #{{ $movimiento['id'] }}</td><td>{{ $movimiento['concepto'] }}</td><td>{{ $movimiento['grupo_nombre'] }}</td><td>${{ number_format($movimiento['monto'], 0, ',', '.') }}</td><td>{{ $movimiento['autor'] }}</td></tr>
        @empty<tr><td colspan="6">No hay movimientos para estos filtros.</td></tr>@endforelse
    </tbody>
</table>
