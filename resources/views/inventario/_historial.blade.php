        <section class="ui-panel overflow-x-auto">
            <h2 class="p-4 font-semibold">Préstamos entregados</h2>
            <table class="ui-table"><thead><tr><th>Solicitud</th><th>Solicitante / grupo</th><th>Entrega</th><th>Devolución comprometida</th><th>Estado</th><th></th></tr></thead><tbody>
                @forelse ($solicitudes as $solicitud)
                    <tr><td>#{{ $solicitud->id }}</td><td>{{ $solicitud->solicitante_nombre }}@if ($solicitud->grupo_nombre) · {{ $solicitud->grupo_nombre }}@endif</td><td>{{ $solicitud->fecha_entrega->format('d/m/Y') }}</td><td>{{ $solicitud->fecha_comprometida->format('d/m/Y') }}</td><td><x-status-badge :state="$solicitud->estado" :label="\App\Models\SolicitudInsumo::ESTADOS[$solicitud->estado]" />@if ($solicitud->atrasado()) · <strong class="text-red-600">Atrasado</strong>@endif</td><td><a class="underline" href="{{ route('insumos.show', [$solicitud, 'consulta' => 'historial']) }}">Ver detalle</a></td></tr>
                @empty<tr><td colspan="6">No hay préstamos entregados del flujo nuevo.</td></tr>@endforelse
            </tbody></table>
        </section>
        {{ $solicitudes->links() }}
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
