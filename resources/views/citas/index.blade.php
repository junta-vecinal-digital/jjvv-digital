<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Reserva de hora para la firma
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status') === 'cita-reservada')
                <div class="p-4 bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 rounded-lg text-sm">
                    Tu hora fue reservada correctamente.
                </div>
            @endif

            @if (session('status') === 'cita-cancelada')
                <div class="p-4 bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 rounded-lg text-sm">
                    Tu reserva fue cancelada. Puedes elegir otra hora cuando quieras.
                </div>
            @endif

            @error('cita')
                <div class="p-4 bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200 rounded-lg text-sm">
                    {{ $message }}
                </div>
            @enderror

            @if (! $socio)
                <div class="p-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg text-sm text-gray-600 dark:text-gray-400">
                    Primero debes enviar tu solicitud de inscripción.
                    <a href="{{ route('socios.create') }}" class="underline text-indigo-600 dark:text-indigo-400">Ir a la solicitud</a>
                </div>

            @elseif ($socio->estado !== 'por_firmar')
                <div class="p-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg text-sm text-gray-600 dark:text-gray-400">
                    @if ($socio->estado === 'pendiente')
                        Tus documentos aún están en revisión. Cuando sean aprobados podrás reservar tu hora de firma aquí.
                    @elseif ($socio->estado === 'activo')
                        Ya eres socio de la junta de vecinos, no necesitas reservar una hora.
                    @else
                        Tu solicitud fue rechazada, por lo que no corresponde reservar una hora.
                    @endif
                    <a href="{{ route('socios.create') }}" class="block mt-2 underline text-indigo-600 dark:text-indigo-400">Ver estado de mi solicitud</a>
                </div>

            @elseif ($cita)
                <div class="p-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg space-y-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Tu hora reservada</h3>

                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                        {{ ucfirst($cita->inicio->translatedFormat('l d \d\e F')) }}, {{ $cita->inicio->format('H:i') }} hrs
                    </p>

                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Preséntate en la sede con tu carnet de identidad para firmar el libro de registro de socios.
                    </p>

                    @if ($cita->inicio->isFuture())
                        <form method="POST" action="{{ route('citas.cancelar') }}"
                              onsubmit="return confirm('¿Seguro que quieres cancelar tu hora?')">
                            @csrf
                            @method('DELETE')
                            <x-danger-button>Cancelar reserva</x-danger-button>
                        </form>
                    @endif
                </div>

            @else
                <div class="p-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Elige una hora</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Por ley, la inscripción se completa firmando el libro de registro de socios en la sede. Se muestran las horas disponibles de los próximos 30 días.
                        </p>
                    </div>

                    @forelse ($disponibles as $lista)
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                {{ ucfirst($lista->first()->inicio->translatedFormat('l d \d\e F')) }}
                            </h4>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($lista as $disponible)
                                    <form method="POST" action="{{ route('citas.reservar', $disponible) }}">
                                        @csrf
                                        <x-secondary-button type="submit">{{ $disponible->inicio->format('H:i') }}</x-secondary-button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            No hay horas disponibles por el momento. Vuelve a revisar más tarde o contacta a la directiva.
                        </p>
                    @endforelse
                </div>
            @endif
        </div>
    </div>
</x-app-layout>