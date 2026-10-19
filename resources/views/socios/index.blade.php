<x-app-layout>
    <x-slot name="header">
        <x-page-heading title="Gestión de socios" description="Consulta fichas, revisa inscripciones y registra socios en ventanilla.">
            <a href="{{ route('reportes.socios') }}" class="ui-button ui-button-secondary">Exportar socios activos</a>
            <a href="{{ route('socios.crear') }}" class="ui-button ui-button-primary">Registrar socio</a>
        </x-page-heading>
    </x-slot>

    @php
        $filtros = [
            ''           => 'Todas',
            'pendiente'  => 'Pendientes de revisión',
            'por_firmar' => 'Pendientes de firma',
            'activo'     => 'Activos',
            'rechazado'  => 'Rechazadas',
            'inactivo'   => 'Inactivos',
        ];
        $actual = request('estado', '');
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            <div class="flex flex-wrap gap-2 px-4 sm:px-0">
                @foreach ($filtros as $valor => $etiqueta)
                    <a href="{{ route('socios.index', $valor ? ['estado' => $valor] : []) }}"
                       class="px-3 py-1.5 rounded-md text-sm border
                              {{ $actual === $valor
                                  ? 'bg-brand-50 text-brand-600 border-brand-400 dark:bg-brand-500/15 dark:text-brand-400 dark:border-brand-500'
                                  : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700' }}">
                        {{ $etiqueta }}
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('socios.index') }}" class="px-4 sm:px-0 flex gap-2">
                @if (request('estado'))
                    <input type="hidden" name="estado" value="{{ request('estado') }}">
                @endif
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre o RUT"
                       class="w-full sm:w-72 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                <x-secondary-button type="submit">Buscar</x-secondary-button>
            </form>

            <div class="ui-panel overflow-x-auto">
                <table class="ui-table divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-left text-gray-600 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-3 font-medium">Nombre</th>
                            <th class="px-4 py-3 font-medium">RUT</th>
                            <th class="px-4 py-3 font-medium">Teléfono</th>
                            <th class="px-4 py-3 font-medium">Solicitud</th>
                            <th class="px-4 py-3 font-medium">Estado</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-200">
                        @forelse ($socios as $socio)
                            <tr>
                                <td class="px-4 py-3">{{ $socio->nombre_completo }}</td>
                                <td class="px-4 py-3">{{ $socio->rut }}</td>
                                <td class="px-4 py-3">{{ $socio->telefono }}</td>
                                <td class="px-4 py-3">{{ $socio->created_at->format('d/m/Y') }}</td>
                                <td class="px-4 py-3"><x-estado-socio :estado="$socio->estado" /></td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('socios.show', $socio) }}" class="underline text-brand-600 dark:text-brand-400">Revisar</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No hay solicitudes en esta categoría.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 sm:px-0">{{ $socios->links() }}</div>
        </div>
    </div>
</x-app-layout>
