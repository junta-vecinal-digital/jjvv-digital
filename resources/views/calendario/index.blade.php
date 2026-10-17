<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white leading-tight">Calendario de la sede</h2>
            @if ($esDirigente)
                <a href="{{ route('reservas.index', ['semana' => $lunes->toDateString()]) }}" class="ui-button ui-button-primary">Gestionar reservas</a>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Navegación entre semanas --}}
            <div class="flex flex-wrap items-center justify-between gap-3 px-4 sm:px-0">
                <h3 class="font-medium text-gray-900 dark:text-gray-100">
                    Semana del {{ $dias->first()->translatedFormat('j \d\e F') }}
                    al {{ $dias->last()->translatedFormat('j \d\e F \d\e Y') }}
                </h3>
                <div class="flex gap-4 text-sm">
                    <a href="{{ route('calendario.index', ['semana' => $lunes->copy()->subWeek()->toDateString()]) }}"
                       class="underline text-gray-600 dark:text-gray-400">← Anterior</a>
                    <a href="{{ route('calendario.index') }}"
                       class="underline text-gray-600 dark:text-gray-400">Esta semana</a>
                    <a href="{{ route('calendario.index', ['semana' => $lunes->copy()->addWeek()->toDateString()]) }}"
                       class="underline text-gray-600 dark:text-gray-400">Siguiente →</a>
                </div>
            </div>

            {{-- Grilla semanal --}}
            @if ($esDirigente)
                <form method="GET" action="{{ route('calendario.imprimir') }}" target="_blank" class="ui-panel p-4 flex flex-wrap items-end gap-3">
                    <div>
                        <x-input-label for="fecha_impresion" value="Fecha del período" />
                        <x-date-input id="fecha_impresion" name="fecha" :value="$lunes->toDateString()" required />
                    </div>
                    <div>
                        <x-input-label for="periodo_impresion" value="Imprimir" />
                        <select id="periodo_impresion" name="periodo" class="ui-input w-full sm:min-w-72" required>
                            <option value="semana">Semana de la fecha elegida</option>
                            <option value="mes">Mes de la fecha elegida</option>
                        </select>
                    </div>
                    <x-primary-button>Ver horario imprimible</x-primary-button>
                </form>
            @endif
            <div class="ui-panel overflow-x-auto">
                <table class="min-w-full text-xs border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300">
                        <tr>
                            <th class="px-3 py-2 w-16"></th>
                            @foreach ($dias as $dia)
                                <th class="px-3 py-2 font-medium text-left {{ $dia->isToday() ? 'text-brand-600 dark:text-brand-400' : '' }}">
                                    {{ ucfirst($dia->translatedFormat('D j')) }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($horas as $hora)
                            <tr>
                                <td class="px-3 py-2 font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $hora }}</td>
                                @foreach ($dias as $dia)
                                    @php
                                        $celda = $celdas[$dia->toDateString()][$hora] ?? null;
                                        if (! $celda) {
                                            $clase = 'text-gray-400 dark:text-gray-500';
                                        } elseif (! $esDirigente) {
                                            $clase = 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200';
                                        } elseif ($celda['tipo'] === 'fijo') {
                                            $clase = 'bg-amber-100 dark:bg-amber-900/40 text-amber-900 dark:text-amber-100';
                                        } else {
                                            $clase = 'bg-brand-50 dark:bg-brand-500/15 text-brand-600 dark:text-brand-400';
                                        }
                                    @endphp
                                    <td class="px-2 py-2 border-l border-gray-200 dark:border-gray-700 {{ $clase }}">
                                        {{ $celda['texto'] ?? 'Libre' }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($esDirigente)
                <p class="px-4 sm:px-0 text-xs text-gray-500 dark:text-gray-400">
                    Azul: reserva. Ámbar: horario fijo de grupo aún sin generar para esa semana.
                </p>

            @endif
        </div>
    </div>
</x-app-layout>
