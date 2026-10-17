@php
    $claseSelect = 'mt-1 block w-full';
@endphp
<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Reservas</h1>
    </x-slot>
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <x-reservas-tabs active="sede" />
        <p>Para una reserva con aporte voluntario acordado o un solicitante sin ficha, <a class="underline" href="{{ route('arriendos.index') }}">ingresar una solicitud de sede</a>.</p>
        @if (session('status') === 'reserva-creada')
            <p role="status" class="ui-panel p-4">Reserva guardada.</p>
        @elseif (session('status') === 'reserva-cancelada')
            <p role="status" class="ui-panel p-4">Reserva cancelada. El horario quedó disponible.</p>
        @endif
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-medium">Semana del {{ $lunes->format('d/m/Y') }} al {{ $lunes->copy()->addDays(6)->format('d/m/Y') }}</h2>
            <div class="flex flex-wrap gap-4 text-sm">
                <a href="{{ route('reservas.index', ['semana' => $lunes->copy()->subWeek()->toDateString()]) }}" class="underline">← Anterior</a>
                <a href="{{ route('reservas.index') }}" class="underline">Esta semana</a>
                <a href="{{ route('reservas.index', ['semana' => $lunes->copy()->addWeek()->toDateString()]) }}" class="underline">Siguiente →</a>
                <a href="{{ route('calendario.index', ['semana' => $lunes->toDateString()]) }}" class="underline">Ver calendario</a>
            </div>
        </div>
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
                                            {{ $reserva->inicio->format('d/m/Y') }}<br>
                                            {{ $reserva->inicio->format('H:i') }} a {{ $reserva->fin->format('H:i') }}
                                        </td>
                                        <td>
                                            <span class="font-medium">{{ $reserva->etiquetaTipo() }}</span>
                                            @if ($reserva->grupo)
                                                <p>{{ $reserva->grupo->nombre }}</p>
                                            @endif
                                            @if ($reserva->motivo !== $reserva->etiquetaTipo())
                                                <p class="text-gray-500 dark:text-gray-400">{{ $reserva->motivo }}</p>
                                            @endif
                                        </td>
                                        <td>{{ $reserva->responsable_nombre ?? $reserva->socio?->nombre_completo ?? ($reserva->tipo === 'actividad' ? $reserva->creador?->name : null) ?? 'No registrado' }}</td>
                                        <td>{{ $reserva->creador?->name ?? 'Cuenta eliminada' }}</td>
                                        <td>{{ $reserva->estado === 'confirmada' ? 'Confirmada' : 'Pendiente' }}</td>
                                        <td>
                                            @if ($reserva->tipo === 'arriendo')
                                                <a class="ui-link" href="{{ route('arriendos.show', [$reserva, 'desde' => 'reservas']) }}">Ver detalle</a>
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
                     x-data="{ tipo: @js(old('tipo', $tiposActividad->first()?->clave ?? '')), inicio: @js(old('hora_inicio', $horaSugerida)), fin: @js(old('hora_fin', $horaFinSugerida)), fines: @js($horasFin) }">
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
                                        <option value="">Elegir actividad</option>
                                        @foreach ($tiposActividad as $tipoReserva)
                                            <option value="{{ $tipoReserva->clave }}">{{ $tipoReserva->nombre }}</option>
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
                                        <x-date-input id="fecha" name="fecha" class="mt-1 block w-full" :value="old('fecha', $fechaSugerida)"
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
    </div>
</x-app-layout>
