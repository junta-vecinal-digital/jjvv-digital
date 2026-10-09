<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Socios</h2>
    </x-slot>

    @php
        $filtros = [
            ''           => 'Todas',
            'pendiente'  => 'Pendientes de revisión',
            'por_firmar' => 'Pendientes de firma',
            'activo'     => 'Activos',
            'rechazado'  => 'Rechazadas',
        ];
        $actual = request('estado', '');
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            <div class="flex flex-wrap gap-2 px-4 sm:px-0">
                @foreach ($filtros as $valor => $etiqueta)
                    <a href="{{ route('socios.index', $valor ? ['estado' => $valor] : []) }}"
                       class="px-3 py-1.5 rounded-md text-sm border
                              {{ $actual === $valor
                                  ? 'bg-gray-800 text-white border-gray-800 dark:bg-gray-200 dark:text-gray-900 dark:border-gray-200'
                                  : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700' }}">
                        {{ $etiqueta }}
                    </a>
                @endforeach
            </div>

            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
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
                                <td class="px-4 py-3">{{ $socio->created_at->format('d-m-Y') }}</td>
                                <td class="px-4 py-3"><x-estado-socio :estado="$socio->estado" /></td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('socios.show', $socio) }}" class="underline text-indigo-600 dark:text-indigo-400">Revisar</a>
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