<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Agenda de firmas del libro de socios
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('mensaje'))
                <div class="p-4 bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 rounded-lg text-sm">
                    {{ session('mensaje') }}
                </div>
            @endif

            @error('agenda')
                <div class="p-4 bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200 rounded-lg text-sm">
                    {{ $message }}
                </div>
            @enderror

            {{-- Crear horarios --}}
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Crear horarios de atención</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Elige un día y un rango de horas. El sistema genera una hora por cada bloque.
                </p>

                <form method="POST" action="{{ route('agenda.store') }}" class="mt-6 grid grid-cols-1 sm:grid-cols-4 gap-4">
                    @csrf

                    <div>
                        <x-input-label for="fecha" value="Fecha" />
                        <x-text-input id="fecha" name="fecha" type="date" class="mt-1 block w-full"
                            :value="old('fecha')" min="{{ now()->toDateString() }}" required />
                        <x-input-error class="mt-2" :messages="$errors->get('fecha')" />
                    </div>

                    <div>
                        <x-input-label for="hora_inicio" value="Desde" />
                        <x-text-input id="hora_inicio" name="hora_inicio" type="time" class="mt-1 block w-full"
                            :value="old('hora_inicio')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('hora_inicio')" />
                    </div>

                    <div>
                        <x-input-label for="hora_fin" value="Hasta" />
                        <x-text-input id="hora_fin" name="hora_fin" type="time" class="mt-1 block w-full"
                            :value="old('hora_fin')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('hora_fin')" />
                    </div>

                    <div>
                        <x-input-label for="duracion" value="Duración de cada hora" />
                        <select id="duracion" name="duracion"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            @foreach ([15, 20, 30, 45, 60] as $minutos)
                                <option value="{{ $minutos }}" @selected(old('duracion', 20) == $minutos)>{{ $minutos }} minutos</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('duracion')" />
                    </div>

                    <div class="sm:col-span-4">
                        <x-primary-button>Generar horarios</x-primary-button>
                    </div>
                </form>
            </div>

            {{-- Agenda --}}
            @forelse ($citas as $lista)
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
                    <h3 class="px-6 py-3 bg-gray-50 dark:bg-gray-700/50 font-medium text-gray-900 dark:text-gray-100">
                        {{ ucfirst($lista->first()->inicio->translatedFormat('l d \d\e F')) }}
                    </h3>

                    <ul class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        @foreach ($lista as $cita)
                            <li class="px-6 py-3 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $cita->inicio->format('H:i') }}</span>

                                    @if ($cita->socio)
                                        <a href="{{ route('socios.show', $cita->socio) }}" class="underline text-indigo-600 dark:text-indigo-400">
                                            {{ $cita->socio->nombre_completo }}
                                        </a>
                                    @else
                                        <span class="text-gray-500 dark:text-gray-400">Disponible</span>
                                    @endif
                                </div>

                                @if ($cita->socio)
                                    <form method="POST" action="{{ route('agenda.liberar', $cita) }}"
                                          onsubmit="return confirm('¿Liberar esta hora? El socio deberá reservar otra.')">
                                        @csrf
                                        @method('PATCH')
                                        <button class="underline text-gray-600 dark:text-gray-400">Liberar</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('agenda.destroy', $cita) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="underline text-red-600 dark:text-red-400">Eliminar</button>
                                    </form>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <p class="text-center text-sm text-gray-500 dark:text-gray-400">Aún no hay horarios creados.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>