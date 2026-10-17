<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white leading-tight">
            Solicitud de inscripción como socio
        </h2>
    </x-slot>

    @php
        // Se puede editar la solicitud mientras esté pendiente (incluye las reabiertas tras un rechazo).
        $edicion = $socio && $socio->estado === 'pendiente';
        $mostrarFormulario = ! $socio || $edicion;
        $archivo = 'mt-1 block w-full text-sm text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 file:mr-4 file:py-2 file:px-4 file:border-0 file:bg-gray-200 dark:file:bg-gray-700 file:text-gray-700 dark:file:text-gray-200';
    @endphp

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status') === 'solicitud-enviada')
                <div class="p-4 bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 rounded-lg text-sm">
                    Tu solicitud fue enviada. Un dirigente revisará tus documentos.
                </div>
            @endif

            @if (session('status') === 'solicitud-corregida')
                <div class="p-4 bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 rounded-lg text-sm">
                    Guardamos tus cambios. Un dirigente revisará tu solicitud.
                </div>
            @endif

            @if ($socio)
                {{-- Ya existe una solicitud: se muestra su estado --}}
                <div class="p-6 ui-panel space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Estado de tu solicitud</h3>
                        <x-estado-socio :estado="$socio->estado" />
                    </div>

                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        @switch($socio->estado)
                            @case('pendiente')
                                Recibimos tu solicitud el {{ $socio->created_at->format('d-m-Y') }}. Un dirigente está revisando tu carnet y tu boleta de servicio básico. Mientras tanto puedes corregir tus datos más abajo.
                                @if ($socio->observaciones)
                                    <span class="block mt-2 font-medium">Motivo del rechazo anterior: {{ $socio->observaciones }}</span>
                                @endif
                                @break
                            @case('por_firmar')
                                Tus documentos fueron aprobados. Falta un último paso: por ley debes firmar el libro de registro de socios de forma presencial.
                                <a href="{{ route('citas.index') }}" class="block mt-2 font-medium underline text-brand-600 dark:text-brand-400">Reservar hora para la firma</a>
                                @break
                            @case('activo')
                                Eres socio de la junta de vecinos desde el {{ $socio->fecha_ingreso?->format('d-m-Y') }}.
                                @break
                            @case('rechazado')
                                Tu solicitud no pudo ser aprobada.
                                @if ($socio->observaciones)
                                    <span class="block mt-2 font-medium">Motivo: {{ $socio->observaciones }}</span>
                                @endif
                                <span class="block mt-2">Si puedes corregir el problema, acércate a la sede para que un dirigente reabra tu solicitud.</span>
                                @break
                            @case('inactivo')
                                Tu inscripción como socio fue dada de baja el {{ $socio->fecha_baja?->format('d-m-Y') }}. Si crees que es un error, acércate a la sede.
                                @break
                        @endswitch
                    </p>
                </div>
            @endif

            @if ($mostrarFormulario)
                <div class="p-4 sm:p-8 ui-panel">
                    <header>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ $edicion ? 'Corregir mis datos' : 'Datos del solicitante' }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Requisitos: residir en el sector hace al menos 6 meses, adjuntar tu carnet de identidad y una boleta de luz, agua u otro servicio básico que indique tu dirección.
                        </p>
                    </header>

                    <x-form-wizard :steps="['Identidad', 'Residencia', 'Documentos']" :fields="[['nombre_completo','rut'],['direccion','telefono','residente_desde'],['carnet','boleta']]">
                        <form novalidate method="POST" action="{{ $edicion ? route('socios.reenviar') : route('socios.store') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                            @csrf
                            @if ($edicion)
                                @method('PUT')
                            @endif

                            <x-form-step number="0">
                                <div>
                                    <x-input-label for="nombre_completo" value="Nombre completo" />
                                    <x-text-input id="nombre_completo" name="nombre_completo" autocomplete="name" type="text" class="mt-1 block w-full"
                                        :value="old('nombre_completo', $socio?->nombre_completo ?? auth()->user()->name)" required autofocus />
                                    <x-input-error class="mt-2" :messages="$errors->get('nombre_completo')" />
                                </div>

                                <div>
                                    <x-input-label for="rut" value="RUT" />
                                    <x-text-input id="rut" name="rut" type="text" class="mt-1 block w-full"
                                        :value="old('rut', $socio?->rut)" placeholder="12345678-5" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('rut')" />
                                </div>

                            </x-form-step>

                            <x-form-step number="1">
                                <div>
                                    <x-input-label for="direccion" value="Dirección" />
                                    <x-text-input id="direccion" name="direccion" autocomplete="street-address" type="text" class="mt-1 block w-full"
                                        :value="old('direccion', $socio?->direccion)" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('direccion')" />
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div>
                                        <x-input-label for="telefono" value="Teléfono (fijo o móvil)" />
                                        <x-text-input id="telefono" name="telefono" autocomplete="tel" type="tel" maxlength="20" class="mt-1 block w-full"
                                            :value="old('telefono', $socio?->telefono)" required />
                                        <x-input-error class="mt-2" :messages="$errors->get('telefono')" />
                                    </div>

                                    <div>
                                        <x-input-label for="residente_desde" value="Resido en el sector desde" />
                                        <x-text-input id="residente_desde" name="residente_desde" type="date" class="mt-1 block w-full"
                                            :value="old('residente_desde', $socio?->residente_desde?->toDateString())" max="{{ now()->subMonths(6)->toDateString() }}" required />
                                        <x-input-error class="mt-2" :messages="$errors->get('residente_desde')" />
                                    </div>
                                </div>

                            </x-form-step>

                            <x-form-step number="2">
                                <div>
                                    <x-input-label for="carnet" value="Carnet de identidad (foto o PDF)" />
                                    <input id="carnet" name="carnet" type="file" accept=".jpg,.jpeg,.png,.pdf" @required(! $edicion) class="{{ $archivo }}" />
                                    @if ($edicion)
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Déjalo vacío para conservar el archivo actual.</p>
                                    @endif
                                    <x-input-error class="mt-2" :messages="$errors->get('carnet')" />
                                </div>

                                <div>
                                    <x-input-label for="boleta" value="Boleta de luz, agua u otro servicio básico (foto o PDF)" />
                                    <input id="boleta" name="boleta" type="file" accept=".jpg,.jpeg,.png,.pdf" @required(! $edicion) class="{{ $archivo }}" />
                                    @if ($edicion)
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Déjalo vacío para conservar el archivo actual.</p>
                                    @endif
                                    <x-input-error class="mt-2" :messages="$errors->get('boleta')" />
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
