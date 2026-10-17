<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white leading-tight">Grupos y organizaciones</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status') === 'grupo-creado')
                <p class="px-4 sm:px-0 text-green-700 dark:text-green-400">Grupo creado.</p>
            @endif

            <details class="ui-panel" open>
                <summary class="cursor-pointer p-4 font-medium">Grupos registrados ({{ $grupos->count() }})</summary>
                <div class="max-h-96 overflow-auto">
                <table class="ui-table divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-left text-gray-600 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-3 font-medium">Grupo</th>
                            <th class="px-4 py-3 font-medium">Representante</th>
                            <th class="px-4 py-3 font-medium">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-200">
                        @forelse ($grupos as $grupo)
                            <tr>
                                <td class="px-4 py-3">
                                    <a href="{{ route('grupos.show', $grupo) }}" class="underline">{{ $grupo->nombre }}</a>
                                </td>
                                <td class="px-4 py-3">{{ $grupo->datosRepresentante()['nombre'] ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @if ($grupo->tieneRepresentanteVigente())
                                        Con representante
                                    @else
                                        <span class="text-red-600 dark:text-red-400">Sin representante vigente</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    Aún no hay grupos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </details>

            @include('grupos._form', ['grupo' => null])
        </div>
    </div>
</x-app-layout>
