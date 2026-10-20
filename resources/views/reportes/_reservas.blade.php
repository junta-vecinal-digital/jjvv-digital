<h2 class="text-lg font-semibold mt-6 mb-3">Solicitudes de sede y arriendos del período</h2>
<p>Se usa la fecha de inicio de la reserva, incluyendo solicitudes de uso de sede y arriendos históricos pendientes, confirmados, cancelados y vencidos. Los importes acordados no se suman nuevamente a los aportes voluntarios recibidos.</p>
<table class="ui-table">
    <thead><tr><th>Reserva</th><th>Fecha y horario</th><th>Solicitante</th><th>Agrupación</th><th>Estado</th><th>Aporte / arriendo (CLP)</th></tr></thead>
    <tbody>
        @forelse ($reservas as $reserva)
            <tr><td>#{{ $reserva->id }}</td><td>{{ $reserva->inicio->format('d/m/Y H:i') }} a {{ $reserva->fin->format('H:i') }}</td><td>{{ $reserva->responsable_nombre ?? $reserva->socio?->nombre_completo ?? 'Sin nombre registrado' }}</td><td>{{ $reserva->grupo?->nombre ?? 'Sin agrupación' }}</td><td>{{ $reserva->estado === 'pendiente' && $reserva->vence_en?->lte(now()) ? \App\Models\Reserva::ESTADOS['vencida'] : (\App\Models\Reserva::ESTADOS[$reserva->estado] ?? $reserva->estado) }}</td><td>{{ $reserva->valor_arriendo === null ? 'Por definir' : '$'.number_format($reserva->valor_arriendo, 0, ',', '.') }}</td></tr>
        @empty<tr><td colspan="6">No hay reservas de sede para estos filtros.</td></tr>@endforelse
    </tbody>
</table>
