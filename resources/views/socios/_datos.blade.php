@php
    $cls = 'mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-brand-500 dark:focus:border-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400 rounded-md shadow-sm';
    $archivo = 'mt-1 block w-full text-sm text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 file:mr-4 file:py-2 file:px-4 file:border-0 file:bg-gray-200 dark:file:bg-gray-700 file:text-gray-700 dark:file:text-gray-200';
    $socio = $socio ?? null;
@endphp

<x-form-step number="0">
    <div>
        <x-input-label for="nombre_completo" value="Nombre completo" />
        <input id="nombre_completo" name="nombre_completo" autocomplete="off" type="text" class="{{ $cls }}" required
               value="{{ old('nombre_completo', $socio?->nombre_completo) }}">
        <x-input-error class="mt-2" :messages="$errors->get('nombre_completo')" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
            <x-input-label for="rut" value="RUT" />
            <input id="rut" name="rut" type="text" class="{{ $cls }}" placeholder="12345678-5" required
                   value="{{ old('rut', $socio?->rut) }}">
            <x-input-error class="mt-2" :messages="$errors->get('rut')" />
        </div>
        <div>
            <x-input-label for="email" value="Correo electrónico (opcional)" />
            <input id="email" name="email" autocomplete="off" type="email" class="{{ $cls }}"
                   value="{{ old('email', $socio?->email) }}">
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>
    </div>

</x-form-step>
<x-form-step number="1">
    <div>
        <x-input-label for="direccion" value="Dirección" />
        <input id="direccion" name="direccion" autocomplete="off" type="text" class="{{ $cls }}" required
               value="{{ old('direccion', $socio?->direccion) }}">
        <x-input-error class="mt-2" :messages="$errors->get('direccion')" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
            <x-input-label for="telefono" value="Teléfono (fijo o móvil)" />
            <input id="telefono" name="telefono" autocomplete="off" type="tel" maxlength="20" class="{{ $cls }}" required
                   value="{{ old('telefono', $socio?->telefono) }}">
            <x-input-error class="mt-2" :messages="$errors->get('telefono')" />
        </div>
        <div>
            <x-input-label for="residente_desde" value="Reside en el sector desde" />
            <input id="residente_desde" name="residente_desde" type="date" max="{{ now()->subMonths(6)->toDateString() }}" class="{{ $cls }}" required
                   value="{{ old('residente_desde', $socio?->residente_desde?->toDateString()) }}">
            <x-input-error class="mt-2" :messages="$errors->get('residente_desde')" />
        </div>
    </div>

</x-form-step>
<x-form-step number="2">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
            <x-input-label for="carnet" value="Carnet de identidad (opcional, foto o PDF)" />
            <input id="carnet" name="carnet" type="file" accept=".jpg,.jpeg,.png,.pdf" class="{{ $archivo }}">
            @if ($socio?->carnet_path)
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Ya hay un archivo cargado; al subir otro se reemplaza.</p>
            @endif
            <x-input-error class="mt-2" :messages="$errors->get('carnet')" />
        </div>
        <div>
            <x-input-label for="boleta" value="Boleta de servicio básico (opcional, foto o PDF)" />
            <input id="boleta" name="boleta" type="file" accept=".jpg,.jpeg,.png,.pdf" class="{{ $archivo }}">
            @if ($socio?->boleta_path)
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Ya hay un archivo cargado; al subir otro se reemplaza.</p>
            @endif
            <x-input-error class="mt-2" :messages="$errors->get('boleta')" />
        </div>
    </div>

</x-form-step>
