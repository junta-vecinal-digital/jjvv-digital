<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $socio->nombre_completo }}
            </h2>
            <a href="{{ route('socios.index') }}" class="text-sm underline text-gray-600 dark:text-gray-400">← Volver al listado</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status') === 'socio-actualizado')
                <div class="p-4 bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 rounded-lg text-sm">
                    La solicitud fue actualizada.
                </div>
            @endif

            <div class="p-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Datos del solicitante</h3>
                    <x-estado-socio :estado="$socio->estado" />
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div><dt class="text-gray-500 dark:text-gray-400">RUT</dt><dd class="text-gray-900 dark:text-gray-100">{{ $socio->rut }}</dd></div>
                    <div><dt class="text-gray-500 dark:text-gray-400">Teléfono</dt><dd class="text-gray-900 dark:text-gray-100">{{ $socio->telefono }}</dd></div>
                    <div><dt class="text-gray-500 dark:text-gray-400">Correo</dt><dd class="text-gray-900 dark:text-gray-100">{{ $socio->email ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500 dark:text-gray-400">Dirección</dt><dd class="text-gray-900 dark:text-gray-100">{{ $socio->direccion }}</dd></div>
                    <div><dt class="text-gray-500 dark:text-gray-400">Reside en el sector desde</dt><dd class="text-gray-900 dark:text-gray-100">{{ $socio->residente_desde->format('d-m-Y') }}</dd></div>
                    <div><dt class="text-gray-500 dark:text-gray-400">Solicitud recibida</dt><dd class="text-gray-900 dark:text-gray-100">{{ $socio->created_at->format('d-m-Y H:i') }}</dd></div>
                    @if ($socio->fecha_ingreso)
                        <div><dt class="text-gray-500 dark:text-gray-400">Fecha de ingreso</dt><dd class="text-gray-900 dark:text-gray-100">{{ $socio->fecha_ingreso->format('d-m-Y') }}</dd></div>
                    @endif
                    @if ($socio->revisor)
                        <div><dt class="text-gray-500 dark:text-gray-400">Última revisión por</dt><dd class="text-gray-900 dark:text-gray-100">{{ $socio->revisor->name }}</dd></div>
                    @endif
                    @if ($socio->observaciones)
                        <div class="sm:col-span-2"><dt class="text-gray-500 dark:text-gray-400">Observaciones</dt><dd class="text-gray-900 dark:text-gray-100">{{ $socio->observaciones }}</dd></div>
                    @endif
                </dl>
            </div>

            <div class="p-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Documentos adjuntos</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('socios.documento', [$socio, 'carnet']) }}" target="_blank" class="underline text-indigo-600 dark:text-indigo-400">Ver carnet de identidad</a></li>
                    <li><a href="{{ route('socios.documento', [$socio, 'boleta']) }}" target="_blank" class="underline text-indigo-600 dark:text-indigo-400">Ver boleta de servicio básico</a></li>
                </ul>
            </div>
            
            @if ($socio->estado === 'por_firmar')
                <div class="p-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Firma del libro de socios</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        @if ($socio->cita)
                            Hora reservada:
                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                {{ ucfirst($socio->cita->inicio->translatedFormat('l d \d\e F, H:i')) }} hrs
                            </span>
                        @else
                            Esta persona aún no ha reservado una hora.
                        @endif
                    </p>
                </div>
            @endif

            @if (in_array($socio->estado, ['pendiente', 'por_firmar']))
                <div class="p-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">Resolución</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        @if ($socio->estado === 'pendiente')
                            Verifica que el carnet y la boleta coincidan con los datos ingresados y que la dirección pertenezca al sector.
                        @else
                            Confirma solo cuando la persona ya haya firmado el libro de registro de socios.
                        @endif
                    </p>

                    <form method="POST" action="{{ route('socios.update', $socio) }}" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <x-input-label for="observaciones" value="Observaciones (obligatorias si rechazas)" />
                            <textarea id="observaciones" name="observaciones" rows="3"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('observaciones') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('observaciones')" />
                        </div>

                        <div class="flex items-center gap-3">
                            <x-primary-button name="estado" :value="$socio->estado === 'pendiente' ? 'por_firmar' : 'activo'">
                                {{ $socio->estado === 'pendiente' ? 'Aprobar documentos' : 'Confirmar firma y activar socio' }}
                            </x-primary-button>

                            <x-danger-button name="estado" value="rechazado">Rechazar</x-danger-button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>