@php
    $prefijo = $socio?->id ?? 'nuevo';
    $valor = fn ($campo, $defecto = null) => (! $socio || old('operacion') === 'socio_'.$socio->id) ? old($campo, $defecto) : $defecto;
    $cls = 'mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-brand-500 dark:focus:border-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400 rounded-md shadow-sm';
    $socio = $socio ?? null;
@endphp

<x-form-step number="0" class="ui-form-grid">
    <div>
        <x-input-label for="nombre_completo_{{ $prefijo }}" value="Nombre completo" />
        <input id="nombre_completo_{{ $prefijo }}" name="nombre_completo" autocomplete="off" type="text" class="{{ $cls }}" required
               value="{{ $valor('nombre_completo', $socio?->nombre_completo) }}">
        <x-input-error class="mt-2" :messages="$errors->get('nombre_completo')" />
    </div>

    <div class="grid gap-6 sm:col-span-2 sm:grid-cols-2">
        <div>
            <x-input-label for="rut_{{ $prefijo }}" value="RUT" />
            <input id="rut_{{ $prefijo }}" name="rut" type="text" class="{{ $cls }}" placeholder="12345678-5" required
                   value="{{ $valor('rut', $socio?->rut) }}">
            <x-input-error class="mt-2" :messages="$errors->get('rut')" />
        </div>
        <div>
            <x-input-label for="email_{{ $prefijo }}" value="Correo electrónico (opcional)" />
            <input id="email_{{ $prefijo }}" name="email" autocomplete="off" type="email" class="{{ $cls }}"
                   value="{{ $valor('email', $socio?->email) }}">
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>
    </div>


    <div>
        <x-input-label for="direccion_{{ $prefijo }}" value="Dirección" />
        <input id="direccion_{{ $prefijo }}" name="direccion" autocomplete="off" type="text" class="{{ $cls }}" required
               value="{{ $valor('direccion', $socio?->direccion) }}">
        <x-input-error class="mt-2" :messages="$errors->get('direccion')" />
    </div>

    <div class="grid gap-6 sm:col-span-2 sm:grid-cols-2">
        <div>
            <x-input-label for="telefono_{{ $prefijo }}" value="Teléfono (fijo o móvil)" />
            <input id="telefono_{{ $prefijo }}" name="telefono" autocomplete="off" type="tel" maxlength="20" class="{{ $cls }}" required
                   value="{{ $valor('telefono', $socio?->telefono) }}">
            <x-input-error class="mt-2" :messages="$errors->get('telefono')" />
        </div>

    </div>

</x-form-step>
<x-form-step number="1" class="ui-form-grid">
        <div>
            <x-input-label for="residente_desde_{{ $prefijo }}" value="Reside en el sector desde" />
            <x-date-input id="residente_desde_{{ $prefijo }}" name="residente_desde" max="{{ now()->subMonths(6)->toDateString() }}" class="{{ $cls }}" required
                   value="{{ $valor('residente_desde', $socio?->residente_desde?->toDateString()) }}" />
            <x-input-error class="mt-2" :messages="$errors->get('residente_desde')" />
        </div>
    <div class="grid gap-6 sm:col-span-2 sm:grid-cols-2">
        <div>
            <x-input-label for="carnet_{{ $prefijo }}" value="Carnet de identidad (opcional, foto o PDF)" />
            <x-file-input id="carnet_{{ $prefijo }}" name="carnet" accept=".jpg,.jpeg,.png,.pdf" />
            @if ($socio?->carnet_path)
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Ya hay un archivo cargado; al subir otro se reemplaza.</p>
            @endif
            <x-input-error class="mt-2" :messages="$errors->get('carnet')" />
        </div>
        <div>
            <x-input-label for="boleta_{{ $prefijo }}" value="Boleta de servicio básico (opcional, foto o PDF)" />
            <x-file-input id="boleta_{{ $prefijo }}" name="boleta" accept=".jpg,.jpeg,.png,.pdf" />
            @if ($socio?->boleta_path)
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Ya hay un archivo cargado; al subir otro se reemplaza.</p>
            @endif
            <x-input-error class="mt-2" :messages="$errors->get('boleta')" />
        </div>
    </div>

</x-form-step>
