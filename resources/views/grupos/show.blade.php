<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white leading-tight">{{ $grupo->nombre }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <p class="px-4 sm:px-0 text-green-700 dark:text-green-400">Cambios guardados.</p>
            @endif

            <details class="ui-panel" @if ($errors->any() && ! $errors->has('horario')) open @endif>
                <summary class="cursor-pointer p-4 font-medium">Editar grupo</summary>
                @include('grupos._form', ['grupo' => $grupo])
            </details>

            {{-- Horarios fijos (RFU-34, RFS-47, RFS-49) --}}
            @php
                $claseSelect = 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-brand-500 dark:focus:border-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400 rounded-md shadow-sm';
            @endphp
            <div class="ui-panel p-6 space-y-6">
                <div>
                    <h3 class="font-medium text-gray-900 dark:text-gray-100">Horarios fijos</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Reservas que se repiten cada semana. Las reservas de cada mes se generan con el botón de más abajo.
                        Editar o eliminar un horario no cambia las reservas ya generadas.
                    </p>
                </div>

                @error('horario')
                    <div class="p-4 bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200 rounded-lg text-sm">
                        {{ $message }}
                    </div>
                @enderror

                @if (session('mensaje'))
                    <div class="p-4 bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 rounded-lg text-sm">
                        {{ session('mensaje') }}
                    </div>
                @endif

                @if (session('omitidas'))
                    <div class="p-4 bg-amber-100 dark:bg-amber-900/40 text-amber-900 dark:text-amber-100 rounded-lg text-sm">
                        <p class="font-medium">No se generaron estos días:</p>
                        <ul class="mt-1 list-disc ps-5">
                            @foreach (session('omitidas') as $omitida)
                                <li>{{ $omitida }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Horarios existentes --}}
                @forelse ($grupo->horariosFijos as $horario)
                    <div class="flex flex-wrap items-end gap-3">
                        <form method="POST" action="{{ route('grupos.horarios.update', $horario) }}" class="flex flex-wrap items-end gap-3">
                            @csrf
                            @method('PUT')
                            <select name="dia_semana" class="{{ $claseSelect }}">
                                @foreach (\App\Models\HorarioFijo::DIAS as $numero => $nombre)
                                    <option value="{{ $numero }}" @selected((int) $horario->dia_semana === $numero)>{{ $nombre }}</option>
                                @endforeach
                            </select>
                            <select name="hora_inicio" class="{{ $claseSelect }}">
                                @foreach ($horas as $hora)
                                    <option value="{{ $hora }}" @selected(substr($horario->hora_inicio, 0, 5) === $hora)>{{ $hora }}</option>
                                @endforeach
                            </select>
                            <span class="pb-2 text-gray-500 dark:text-gray-400">a</span>
                            <select name="hora_fin" class="{{ $claseSelect }}">
                                @foreach ($horasFin as $hora)
                                    <option value="{{ $hora }}" @selected(substr($horario->hora_fin, 0, 5) === $hora)>{{ $hora }}</option>
                                @endforeach
                            </select>
                            <x-secondary-button type="submit">Guardar</x-secondary-button>
                        </form>

                        <form method="POST" action="{{ route('grupos.horarios.destroy', $horario) }}"
                              onsubmit="return confirm('¿Eliminar este horario fijo? Las reservas ya generadas se mantienen.')">
                            @csrf
                            @method('DELETE')
                            <button class="pb-2 underline text-sm text-red-600 dark:text-red-400">Eliminar</button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">Este grupo aún no tiene horarios fijos.</p>
                @endforelse

                {{-- Agregar horario --}}
                @unless ($grupo->tieneRepresentanteVigente())
                    <p class="text-sm text-red-600 dark:text-red-400">
                        Asigna un representante vigente para agregar horarios y generar reservas.
                    </p>
                @endunless
                <form method="POST" action="{{ route('grupos.horarios.store', $grupo) }}"
                      class="flex flex-wrap items-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    @csrf
                    <div>
                        <x-input-label for="nuevo_dia" value="Día" />
                        <select id="nuevo_dia" name="dia_semana" class="mt-1 block {{ $claseSelect }}" required>
                            @foreach (\App\Models\HorarioFijo::DIAS as $numero => $nombre)
                                <option value="{{ $numero }}">{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="nuevo_desde" value="Desde" />
                        <select id="nuevo_desde" name="hora_inicio" class="mt-1 block {{ $claseSelect }}" required>
                            @foreach ($horas as $hora)
                                <option value="{{ $hora }}">{{ $hora }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="nuevo_hasta" value="Hasta" />
                        <select id="nuevo_hasta" name="hora_fin" class="mt-1 block {{ $claseSelect }}" required>
                            @foreach ($horasFin as $hora)
                                <option value="{{ $hora }}">{{ $hora }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-primary-button :disabled="! $grupo->tieneRepresentanteVigente()">Agregar horario</x-primary-button>
                </form>

                {{-- Generar reservas del mes --}}
                <form method="POST" action="{{ route('grupos.horarios.generar', $grupo) }}"
                      class="flex flex-wrap items-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    @csrf
                    <div>
                        <x-input-label for="mes" value="Generar reservas del mes" />
                        <select id="mes" name="mes" class="ui-input mt-1 block w-full sm:w-64" required>
                            @for ($adelanto = 0; $adelanto <= 6; $adelanto++)
                                @php($mesReserva = now()->startOfMonth()->addMonths($adelanto))
                                <option value="{{ $mesReserva->format('Y-m') }}" @selected(old('mes', now()->startOfMonth()->addMonth()->format('Y-m')) === $mesReserva->format('Y-m'))>{{ ucfirst($mesReserva->translatedFormat('F Y')) }}</option>
                            @endfor
                        </select>
                    </div>
                    <x-primary-button :disabled="! $grupo->tieneRepresentanteVigente()">Generar reservas</x-primary-button>
                </form>
            </div>

            <a href="{{ route('grupos.index') }}" class="underline text-sm text-gray-600 dark:text-gray-400">Volver al listado</a>
        </div>
    </div>
</x-app-layout>
