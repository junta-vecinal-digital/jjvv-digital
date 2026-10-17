<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Actas</h1>
            @if (Auth::user()->hasRole('presidente', 'secretario'))
                <a href="{{ route('actas.create') }}" class="ui-button ui-button-primary">Registrar acta</a>
            @endif
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-4 px-4 py-6 sm:px-6 lg:px-8">
        @if (Auth::user()->esDirigente())
            <form method="GET" action="{{ route('actas.index') }}" class="ui-panel flex flex-wrap items-end gap-3 p-4">
                <div>
                    <x-input-label for="tipo" value="Tipo de acta" />
                    <select id="tipo" name="tipo" class="mt-1">
                        <option value="">Todos</option>
                        @foreach ($tipos as $tipo => $nombre)
                            <option value="{{ $tipo }}" @selected(request('tipo') === $tipo)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
                </div>
                <button class="ui-button ui-button-secondary">Filtrar</button>
                <a href="{{ route('actas.index') }}" class="ui-button ui-button-secondary">Limpiar</a>
            </form>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400">Aquí puedes consultar las actas de asamblea.</p>
        @endif
        <div class="ui-panel overflow-x-auto">
            <table class="ui-table">
                <thead><tr><th>Fecha de reunión</th><th>Tipo</th><th>Escaneo</th><th>Consulta</th></tr></thead>
                <tbody>
                    @forelse ($actas as $acta)
                        <tr>
                            <td>{{ $acta->fecha->format('d/m/Y') }}</td>
                            <td>{{ \App\Models\Acta::TIPOS[$acta->tipo] }}</td>
                            <td>{{ $acta->escaneo_path ? 'Adjunto' : 'Sin escaneo' }}</td>
                            <td><a href="{{ route('actas.show', $acta) }}" class="text-brand-500 underline">Ver acta</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No hay actas para mostrar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $actas->links() }}
    </div>
</x-app-layout>
