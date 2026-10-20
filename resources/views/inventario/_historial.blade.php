<section class="ui-panel overflow-x-auto"><h2 class="p-5 text-xl font-semibold">Préstamos entregados</h2><table class="ui-table"><thead><tr><th>Solicitud / solicitante</th><th>Entrega</th><th>Devolución comprometida</th><th>Estado</th><th>Consulta</th></tr></thead>
@forelse($solicitudes as $solicitud)<x-record-row :id="'insumo_'.$solicitud->id" :columns="5"><x-slot name="summary"><td><strong>{{ $solicitud->solicitante_nombre }}</strong><span class="ui-sub">#{{ $solicitud->id }} · {{ $solicitud->grupo_nombre ?? 'Particular' }}</span></td><td>{{ $solicitud->fecha_entrega->format('d/m/Y') }}</td><td>{{ $solicitud->fecha_comprometida->format('d/m/Y') }}</td><td><x-status-badge :state="$solicitud->atrasado() ? 'atrasado' : $solicitud->estado" :label="$solicitud->atrasado() ? 'Entregado · Atrasado' : \App\Models\SolicitudInsumo::ESTADOS[$solicitud->estado]" /></td></x-slot><p class="ui-notice">Vista de consulta. Las operaciones se gestionan en Solicitudes de insumos.</p>@include('insumos._detalle', ['gestion' => false])</x-record-row>@empty<tbody><tr><td colspan="5">No hay préstamos entregados.</td></tr></tbody>@endforelse</table></section>
{{ $solicitudes->links() }}
@if($prestamos->total())<details><summary class="cursor-pointer py-3 font-semibold">Préstamos históricos · registros anteriores</summary>
<div class="ui-panel overflow-x-auto">
            <h2 class="p-4 font-semibold">Préstamos históricos</h2>
            <table class="ui-table">
                <thead><tr><th>Ítem</th><th>Unidades</th><th>Destinatario</th><th>Entrega</th><th>Estado</th><th>Detalle</th></tr></thead>
                <tbody>
                    @forelse ($prestamos as $prestamo)
                        <tr>
                            <td>{{ $prestamo->item->nombre }}</td><td>{{ $prestamo->cantidad }}</td><td>{{ $prestamo->destinatario_nombre }}</td><td>{{ $prestamo->fecha_prestamo->format('d/m/Y') }}</td>
                            <td>{{ $prestamo->fecha_devolucion ? 'Devuelto' : 'En préstamo' }}</td>
                            <td><a href="{{ route('inventario.prestamos.show', [$prestamo, 'consulta' => 'historial']) }}" class="text-brand-500 underline">Ver préstamo</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No hay préstamos para mostrar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $prestamos->links() }}

</details>@endif