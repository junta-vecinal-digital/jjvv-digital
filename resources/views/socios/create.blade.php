<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Solicitud de inscripción como socio
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status') === 'solicitud-enviada')
                <div class="p-4 bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 rounded-lg text-sm">
                    Tu solicitud fue enviada. Un dirigente revisará tus documentos.
                </div>
            @endif

            @if ($socio)
                {{-- Ya existe una solicitud: se muestra su estado --}}
                <div class="p-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Estado de tu solicitud</h3>
                        <x-estado-socio :estado="$socio->estado" />
                    </div>

                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        @switch($socio->estado)
                            @case('pendiente')
                                Recibimos tu solicitud el {{ $socio->created_at->format('d-m-Y') }}. Un dirigente está revisando tu carnet y tu boleta de servicio básico.
                                @break
                            @case('por_firmar')
                                Tus documentos fueron aprobados. Falta un último paso: por ley debes firmar el libro de registro de socios de forma presencial. Contacta a la directiva para coordinar tu visita.
                                @break
                            @case('activo')
                                Eres socio de la junta de vecinos desde el {{ $socio->fecha_ingreso?->format('d-m-Y') }}.
                                @break
                            @case('rechazado')
                                Tu solicitud no pudo ser aprobada.
                                @if ($socio->observaciones)
                                    <span class="block mt-2 font-medium">Motivo: {{ $socio->observaciones }}</span>
                                @endif
                                @break
                        @endswitch
                    </p>
                </div>
            @else
                {{-- Formulario de solicitud --}}
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <header>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Datos del solicitante</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Requisitos: residir en el sector hace al menos 6 meses, adjuntar tu carnet de identidad y una boleta de luz, agua u otro servicio básico que indique tu dirección.
                        </p>
                    </header>

                    <form method="POST" action="{{ route('socios.store') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="nombre_completo" value="Nombre completo" />
                            <x-text-input id="nombre_completo" name="nombre_completo" type="text" class="mt-1 block w-full"
                                :value="old('nombre_completo', auth()->user()->name)" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('nombre_completo')" />
                        </div>

                        <div>
                            <x-input-label for="rut" value="RUT" />
                            <x-text-input id="rut" name="rut" type="text" class="mt-1 block w-full"
                                :value="old('rut')" placeholder="12345678-9" required />
                            <x-input-error class="mt-2" :messages="$errors->get('rut')" />
                        </div>

                        <div>
                            <x-input-label for="direccion" value="Dirección" />
                            <x-text-input id="direccion" name="direccion" type="text" class="mt-1 block w-full"
                                :value="old('direccion')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('direccion')" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="telefono" value="Teléfono (fijo o móvil)" />
                                <x-text-input id="telefono" name="telefono" type="text" class="mt-1 block w-full"
                                    :value="old('telefono')" required />
                                <x-input-error class="mt-2" :messages="$errors->get('telefono')" />
                            </div>

                            <div>
                                <x-input-label for="residente_desde" value="Resido en el sector desde" />
                                <x-text-input id="residente_desde" name="residente_desde" type="date" class="mt-1 block w-full"
                                    :value="old('residente_desde')" max="{{ now()->subMonths(6)->toDateString() }}" required />
                                <x-input-error class="mt-2" :messages="$errors->get('residente_desde')" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="carnet" value="Carnet de identidad (foto o PDF)" />
                            <input id="carnet" name="carnet" type="file" accept=".jpg,.jpeg,.png,.pdf" required
                                class="mt-1 block w-full text-sm text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 file:mr-4 file:py-2 file:px-4 file:border-0 file:bg-gray-200 dark:file:bg-gray-700 file:text-gray-700 dark:file:text-gray-200" />
                            <x-input-error class="mt-2" :messages="$errors->get('carnet')" />
                        </div>

                        <div>
                            <x-input-label for="boleta" value="Boleta de luz, agua u otro servicio básico (foto o PDF)" />
                            <input id="boleta" name="boleta" type="file" accept=".jpg,.jpeg,.png,.pdf" required
                                class="mt-1 block w-full text-sm text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 file:mr-4 file:py-2 file:px-4 file:border-0 file:bg-gray-200 dark:file:bg-gray-700 file:text-gray-700 dark:file:text-gray-200" />
                            <x-input-error class="mt-2" :messages="$errors->get('boleta')" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>Enviar solicitud</x-primary-button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>