<x-app-layout>
    <x-slot name="header"><x-page-heading title="Registro de auditoría" description="Consulta quién realizó cada cambio y cuándo. Los registros se conservan sin edición ni eliminación."></x-page-heading></x-slot>

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
            <nav class="ui-tabs" aria-label="Tipo de registro">
                @foreach ($filtros as $valor => $etiqueta)
                    <a href="{{ route('auditoria.index', $valor ? ['modelo' => $valor] : []) }}"
                       @if($actual === $valor) aria-current="page" @endif class="px-3 py-1.5 rounded-md text-sm border
                              {{ $actual === $valor
                                  ? 'bg-brand-50 text-brand-600 border-brand-400 dark:bg-brand-500/15 dark:text-brand-400 dark:border-brand-500'
                                  : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700' }}">
                        {{ $etiqueta }}
                    </a>
                @endforeach
            </nav>

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
                    @forelse($auditorias as $a)<x-record-row :id="'auditoria_'.$a->id" :columns="5"><x-slot name="summary"><td>{{ $a->created_at?->format('d/m/Y') }}<span class="ui-sub">{{ $a->created_at?->format('H:i') }}</span></td><td>{{ $a->usuario?->name ?? 'Sistema / autoregistro' }}</td><td>{{ $eventos[$a->evento] ?? $a->evento }}</td><td>{{ $a->etiquetaRegistro() }}</td></x-slot><h2 class="text-xl font-semibold">{{ $eventos[$a->evento] ?? $a->evento }} · {{ $a->etiquetaRegistro() }}</h2><div class="overflow-x-auto"><table class="ui-table"><thead><tr><th>Campo</th><th>Antes</th><th>Después</th></tr></thead><tbody>@forelse($a->cambios ?? [] as $campo=>$cambio)<tr><td>{{ $campo }}</td>@foreach(['antes','despues'] as $lado)@php($dato = $cambio[$lado] ?? null)<td>{{ is_array($dato) ? json_encode($dato,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) : ($dato ?? '—') }}</td>@endforeach</tr>@empty<tr><td colspan="3">Sin campos registrados.</td></tr>@endforelse</tbody></table></div></x-record-row>@empty<tbody><tr><td colspan="5">Sin registros con estos filtros.</td></tr></tbody>@endforelse
                </table>
            </div>

            <div class="px-4 sm:px-0">{{ $auditorias->links() }}</div>

            {{-- Bajas de socios: solo vista --}}
            <details class="ui-panel overflow-x-auto"><summary class="cursor-pointer px-4 py-3 font-medium">Socios dados de baja · consulta</summary>
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
            </details>
        </div>
    </div>
</x-app-layout>