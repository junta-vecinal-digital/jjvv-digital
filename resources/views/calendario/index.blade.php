@php
    $claseSelect = 'mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-brand-500 dark:focus:border-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400 rounded-md shadow-sm';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white leading-tight">Calendario de la sede</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status') === 'reserva-creada')
                <div class="p-4 bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 rounded-lg text-sm">
                    Reserva guardada.
                </div>
            @endif

            {{-- Navegación entre semanas --}}
            @if (session('status') === 'reserva-cancelada')
                <p class="ui-panel p-4">Reserva cancelada. El horario quedó disponible.</p>
            @endif
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
                        <x-text-input id="fecha_impresion" name="fecha" type="date" :value="$lunes->toDateString()" required />
                    </div>
                    <div>
                        <x-input-label for="periodo_impresion" value="Imprimir" />
                        <select id="periodo_impresion" name="periodo" class="ui-input" required>
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

                <section class="ui-panel overflow-hidden">
                    <h3 class="px-6 py-4 text-lg font-medium text-gray-900 dark:text-gray-100">Detalle de reservas de la semana</h3>
                    <div class="overflow-x-auto">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th>Fecha y horario</th>
                                    <th>Actividad</th>
                                    <th>Quién reserva</th>
                                    <th>Ingresada por</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($reservas as $reserva)
                                    <tr>
                                        <td class="whitespace-nowrap">
                                            {{ $reserva->inicio->format('d-m-Y') }}<br>
                                            {{ $reserva->inicio->format('H:i') }} a {{ $reserva->fin->format('H:i') }}
                                        </td>
                                        <td>
                                            <span class="font-medium">{{ \App\Models\Reserva::TIPOS[$reserva->tipo] ?? $reserva->tipo }}</span>
                                            @if ($reserva->grupo)
                                                <p>{{ $reserva->grupo->nombre }}</p>
                                            @endif
                                            @if ($reserva->motivo !== (\App\Models\Reserva::TIPOS[$reserva->tipo] ?? $reserva->tipo))
                                                <p class="text-gray-500 dark:text-gray-400">{{ $reserva->motivo }}</p>
                                            @endif
                                        </td>
                                        <td>{{ $reserva->socio?->nombre_completo ?? ($reserva->tipo === 'actividad' ? $reserva->creador?->name : null) ?? 'No registrado' }}</td>
                                        <td>{{ $reserva->creador?->name ?? 'Cuenta eliminada' }}</td>
                                        <td>{{ $reserva->estado === 'confirmada' ? 'Confirmada' : 'Pendiente' }}</td>
                                        <td>
                                            @if ($reserva->tipo === 'arriendo')
                                                <a class="ui-link" href="{{ route('arriendos.show', $reserva) }}">Ver arriendo</a>
                                            @endif
                                            <form method="POST" action="{{ route('reservas.cancelar', $reserva) }}" onsubmit="return confirm('¿Cancelar esta reserva y liberar su horario?')">
                                                @csrf
                                                <button class="underline" type="submit">Cancelar reserva</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-gray-500 dark:text-gray-400">No hay reservas generadas para esta semana.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- Ingreso de reservas --}}
                <div class="p-4 sm:p-8 ui-panel"
                     x-data="{ tipo: @js(old('tipo', 'grupo')), inicio: @js(old('hora_inicio', $horaSugerida)), fin: @js(old('hora_fin', $horaFinSugerida)), fines: @js($horasFin) }">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Ingresar reserva</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Una reserva ocupa uno o más bloques consecutivos dentro de un mismo día.
                    </p>

                    @error('reserva')
                        <div class="mt-4 p-4 bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200 rounded-lg text-sm">
                            {{ $message }}
                        </div>
                    @enderror

                    <x-form-wizard :steps="['Actividad', 'Horario']" :fields="[['tipo', 'grupo_id', 'socio_id'], ['fecha', 'hora_inicio', 'hora_fin', 'reserva']]">
                        <form method="POST" action="{{ route('reservas.store') }}" novalidate class="space-y-5">
                            @csrf
                            <x-form-step number="0">
                                <div>
                                    <x-input-label for="tipo" value="Tipo de actividad" />
                                    <select id="tipo" name="tipo" x-model="tipo" class="{{ $claseSelect }}" required>
                                        @foreach (['grupo', 'particular', 'actividad'] as $tipoReserva)
                                            <option value="{{ $tipoReserva }}">{{ \App\Models\Reserva::TIPOS[$tipoReserva] }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('tipo')" />
                                </div>
                                <div x-show="tipo === 'grupo'" x-cloak>
                                    <x-input-label for="grupo_id" value="Grupo" />
                                    <select id="grupo_id" name="grupo_id" class="{{ $claseSelect }}" :disabled="tipo !== 'grupo'" :required="tipo === 'grupo'">
                                        <option value="">Elegir grupo</option>
                                        @foreach ($grupos as $grupo)
                                            <option value="{{ $grupo->id }}" @selected((string) old('grupo_id') === (string) $grupo->id)>{{ $grupo->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Solo se muestran grupos con representante vigente.</p>
                                    <x-input-error class="mt-2" :messages="$errors->get('grupo_id')" />
                                </div>
                                <div x-show="tipo === 'particular'" x-cloak>
                                    <x-input-label for="socio_id" value="Socio responsable" />
                                    <select id="socio_id" name="socio_id" class="{{ $claseSelect }}" :disabled="tipo !== 'particular'" :required="tipo === 'particular'">
                                        <option value="">Elegir socio activo</option>
                                        @foreach ($socios as $socio)
                                            <option value="{{ $socio->id }}" @selected((string) old('socio_id') === (string) $socio->id)>{{ $socio->nombre_completo }} · {{ $socio->rut }}</option>
                                        @endforeach
                                    </select>
                                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Actividad gratuita. No corresponde a un arriendo.</p>
                                    <x-input-error class="mt-2" :messages="$errors->get('socio_id')" />
                                </div>
                            </x-form-step>
                            <x-form-step number="1">
                                <div class="grid gap-4 sm:grid-cols-3">
                                    <div>
                                        <x-input-label for="fecha" value="Fecha" />
                                        <x-text-input id="fecha" name="fecha" type="date" class="mt-1 block w-full" :value="old('fecha', $fechaSugerida)"
                                            min="{{ now()->toDateString() }}" max="{{ now()->addMonths(6)->toDateString() }}" required />
                                        <x-input-error class="mt-2" :messages="$errors->get('fecha')" />
                                    </div>
                                    <div>
                                        <x-input-label for="hora_inicio" value="Desde" />
                                        <select id="hora_inicio" name="hora_inicio" x-model="inicio" @change="if (fin <= inicio) fin = fines.find(hora => hora > inicio) ?? ''" class="{{ $claseSelect }}" required>
                                            @foreach ($horas as $hora)
                                                <option value="{{ $hora }}">{{ $hora }}</option>
                                            @endforeach
                                        </select>
                                        <x-input-error class="mt-2" :messages="$errors->get('hora_inicio')" />
                                    </div>
                                    <div>
                                        <x-input-label for="hora_fin" value="Hasta" />
                                        <select id="hora_fin" name="hora_fin" x-model="fin" class="{{ $claseSelect }}" required>
                                            @foreach ($horasFin as $hora)
                                                <option value="{{ $hora }}" :disabled="'{{ $hora }}' <= inicio">{{ $hora }}</option>
                                            @endforeach
                                        </select>
                                        <x-input-error class="mt-2" :messages="$errors->get('hora_fin')" />
                                    </div>
                                </div>
                            </x-form-step>
                            <x-wizard-actions />
                        </form>
                    </x-form-wizard>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
