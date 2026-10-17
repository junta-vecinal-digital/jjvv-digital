<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Préstamos de inventario</h1>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('inventario.index') }}" class="ui-button ui-button-secondary">Ver inventario</a>
                <a href="{{ route('insumos.index') }}" class="ui-button ui-button-primary">Solicitudes de insumos</a>
            </div>
        </div>
    </x-slot>
    <div class="mx-auto max-w-6xl space-y-4 px-4 py-6 sm:px-6 lg:px-8">
        <x-reservas-tabs active="insumos" />
        <section class="ui-panel overflow-x-auto">
            <h2 class="p-4 font-semibold">Préstamos entregados</h2>
            <table class="ui-table"><thead><tr><th>Solicitud</th><th>Solicitante / grupo</th><th>Entrega</th><th>Devolución comprometida</th><th>Estado</th><th></th></tr></thead><tbody>
                @forelse ($solicitudes as $solicitud)
                    <tr><td>#{{ $solicitud->id }}</td><td>{{ $solicitud->solicitante_nombre }}@if ($solicitud->grupo_nombre) · {{ $solicitud->grupo_nombre }}@endif</td><td>{{ $solicitud->fecha_entrega->format('d/m/Y') }}</td><td>{{ $solicitud->fecha_comprometida->format('d/m/Y') }}</td><td>{{ \App\Models\SolicitudInsumo::ESTADOS[$solicitud->estado] }}@if ($solicitud->atrasado()) · <strong class="text-red-600">Atrasado</strong>@endif</td><td><a class="underline" href="{{ route('insumos.show', $solicitud) }}">Ver detalle</a></td></tr>
                @empty<tr><td colspan="6">No hay préstamos entregados del flujo nuevo.</td></tr>@endforelse
            </tbody></table>
        </section>
        {{ $solicitudes->links() }}
        <form method="GET" action="{{ route('inventario.prestamos.index') }}" class="ui-panel flex flex-wrap items-end gap-3 p-4">
            <div>
                <x-input-label for="estado" value="Estado del préstamo" />
                <select id="estado" name="estado" class="mt-1">
                    <option value="">Todos</option><option value="prestado" @selected(request('estado') === 'prestado')>En préstamo</option><option value="devuelto" @selected(request('estado') === 'devuelto')>Devuelto</option>
                </select>
                <x-input-error :messages="$errors->get('estado')" class="mt-2" />
            </div>
            <button class="ui-button ui-button-secondary">Filtrar</button>
        </form>
        <div class="ui-panel overflow-x-auto">
            <h2 class="p-4 font-semibold">Préstamos históricos</h2>
            <table class="ui-table">
                <thead><tr><th>Ítem</th><th>Unidades</th><th>Destinatario</th><th>Entrega</th><th>Estado</th><th>Detalle</th></tr></thead>
                <tbody>
                    @forelse ($prestamos as $prestamo)
                        <tr>
                            <td>{{ $prestamo->item->nombre }}</td><td>{{ $prestamo->cantidad }}</td><td>{{ $prestamo->destinatario_nombre }}</td><td>{{ $prestamo->fecha_prestamo->format('d/m/Y') }}</td>
                            <td>{{ $prestamo->fecha_devolucion ? 'Devuelto' : 'En préstamo' }}</td>
                            <td><a href="{{ route('inventario.prestamos.show', $prestamo) }}" class="text-brand-500 underline">Ver préstamo</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No hay préstamos para mostrar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $prestamos->links() }}
    </div>
</x-app-layout>
