<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Proyectos</h1>
            @if (Auth::user()->hasRole('presidente'))
                <a href="{{ route('proyectos.create') }}" class="ui-button ui-button-primary">Registrar proyecto</a>
            @endif
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-4 px-4 py-6 sm:px-6 lg:px-8">
        <p class="text-sm text-gray-500 dark:text-gray-400">Consulta los proyectos postulados por la junta y su estado.</p>
        <form method="GET" action="{{ route('proyectos.index') }}" class="ui-panel flex flex-wrap items-end gap-3 p-4">
            <div>
                <x-input-label for="estado" value="Estado" />
                <select id="estado" name="estado" class="mt-1">
                    <option value="">Todos</option>
                    @foreach (\App\Models\Proyecto::ESTADOS as $estado => $nombre)
                        <option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ $nombre }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('estado')" class="mt-2" />
            </div>
            <button class="ui-button ui-button-secondary">Filtrar</button>
            <a href="{{ route('proyectos.index') }}" class="ui-button ui-button-secondary">Limpiar</a>
        </form>
        <div class="ui-panel overflow-x-auto">
            <table class="ui-table">
                <thead><tr><th>Proyecto</th><th>Organismo</th><th>Fecha de postulación</th><th>Estado</th><th>Monto</th><th>Consulta</th></tr></thead>
                <tbody>
                    @forelse ($proyectos as $proyecto)
                        <tr>
                            <td>{{ $proyecto->titulo }}</td>
                            <td>{{ $proyecto->organismo }}</td>
                            <td>{{ $proyecto->fecha_postulacion->format('d/m/Y') }}</td>
                            <td>{{ \App\Models\Proyecto::ESTADOS[$proyecto->estado] }}</td>
                            <td>{{ $proyecto->monto === null ? 'Sin indicar' : '$'.number_format($proyecto->monto, 0, ',', '.') }}</td>
                            <td><a href="{{ route('proyectos.show', $proyecto) }}" class="text-brand-500 underline">Ver proyecto</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No hay proyectos para mostrar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $proyectos->links() }}
    </div>
</x-app-layout>
