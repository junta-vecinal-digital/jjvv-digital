<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white leading-tight">Registro de auditoría</h2>
    </x-slot>

    @php
        $filtros = [
            '' => 'Todo',
            'socio' => 'Socios',
            'certificado' => 'Certificados',
            'usuario' => 'Usuarios',
            'finanzas' => 'Finanzas',
        ];
        $actual = request('modelo', '');
        $eventos = [
            'creado' => 'Creado',
            'modificado' => 'Modificado',
            'eliminado' => 'Eliminado',
            'descargado' => 'Descargado',
        ];
        $claseInput = 'rounded-md border-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600';
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Filtros por tipo de registro --}}
            <div class="flex flex-wrap gap-2 px-4 sm:px-0">
                @foreach ($filtros as $valor => $etiqueta)
                    <a href="{{ route('auditoria.index', $valor ? ['modelo' => $valor] : []) }}"
                       class="px-3 py-1.5 rounded-md text-sm border
                              {{ $actual === $valor
                                  ? 'bg-brand-50 text-brand-600 border-brand-400 dark:bg-brand-500/15 dark:text-brand-400 dark:border-brand-500'
                                  : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700' }}">
                        {{ $etiqueta }}
                    </a>
                @endforeach
            </div>

            {{-- Filtros por fecha y autor --}}
            <form method="GET" action="{{ route('auditoria.index') }}"
                  class="flex flex-wrap items-end gap-3 px-4 sm:px-0 text-sm text-gray-700 dark:text-gray-300">
                <input type="hidden" name="modelo" value="{{ $actual }}">

                <div>
                    <label for="desde" class="block">Desde</label>
                    <x-date-input id="desde" name="desde" value="{{ request('desde') }}" class="{{ $claseInput }}" />
                </div>

                <div>
                    <label for="hasta" class="block">Hasta</label>
                    <x-date-input id="hasta" name="hasta" value="{{ request('hasta') }}" class="{{ $claseInput }}" />
                </div>

                <div>
                    <label for="autor" class="block">Autor</label>
                    <select id="autor" name="autor" class="{{ $claseInput }}">
                        <option value="">Todos</option>
                        @foreach ($autores as $autor)
                            <option value="{{ $autor->id }}" @selected(request('autor') == $autor->id)>{{ $autor->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="px-3 py-1.5 rounded-md border border-gray-300 bg-white hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:hover:bg-gray-700">
                    Filtrar
                </button>
                <a href="{{ route('auditoria.index') }}" class="underline">Limpiar</a>
            </form>

            @if ($errors->any())
                <div class="px-4 sm:px-0 text-sm text-red-600 dark:text-red-400">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Registros --}}
            <div class="ui-panel overflow-x-auto">
                <table class="ui-table divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-left text-gray-600 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-3 font-medium">Fecha</th>
                            <th class="px-4 py-3 font-medium">Autor</th>
                            <th class="px-4 py-3 font-medium">Evento</th>
                            <th class="px-4 py-3 font-medium">Registro</th>
                            <th class="px-4 py-3 font-medium">Detalle</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-200 align-top">
                        @forelse ($auditorias as $a)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $a->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">{{ $a->usuario?->name ?? 'Sistema / autoregistro' }}</td>
                                <td class="px-4 py-3">{{ $eventos[$a->evento] ?? $a->evento }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $a->etiquetaRegistro() }}</td>
                                <td class="px-4 py-3">
                                    @if ($a->evento === 'modificado')
                                        <ul class="space-y-1">
                                            @foreach ($a->cambios ?? [] as $campo => $v)
                                                <li><span class="font-medium">{{ $campo }}:</span>
                                                    {{ $v['antes'] ?? '—' }} → {{ $v['despues'] ?? '—' }}</li>
                                            @endforeach
                                        </ul>
                                    @elseif ($a->evento === 'descargado')
                                        <span class="text-gray-500 dark:text-gray-400">
                                            IP {{ $a->cambios['descarga']['despues']['ip'] ?? '—' }}
                                        </span>
                                    @else
                                        <span class="text-gray-500 dark:text-gray-400">{{ count($a->cambios ?? []) }} campos</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Sin registros con estos filtros.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 sm:px-0">{{ $auditorias->links() }}</div>

            {{-- Bajas de socios: solo vista --}}
            <div class="ui-panel overflow-x-auto">
                <h3 class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200">Socios dados de baja</h3>
                <table class="ui-table divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-left text-gray-600 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-3 font-medium">Socio</th>
                            <th class="px-4 py-3 font-medium">RUT</th>
                            <th class="px-4 py-3 font-medium">Fecha de baja</th>
                            <th class="px-4 py-3 font-medium">Motivo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-200 align-top">
                        @forelse ($bajas as $socio)
                            <tr>
                                <td class="px-4 py-3">{{ $socio->nombre_completo }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $socio->rut }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $socio->fecha_baja?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $socio->motivo_baja ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No hay socios dados de baja.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>