@props(['id', 'name'])

<div x-data="{ archivos: '' }" class="flex flex-wrap items-center gap-3 rounded-lg border border-gray-300 p-2 dark:border-gray-700">
    <input id="{{ $id }}" name="{{ $name }}" type="file" class="peer sr-only" @change="archivos = [...$event.target.files].map(archivo => archivo.name).join(', ')" {{ $attributes->except(['class', 'type']) }}>
    <label for="{{ $id }}" class="ui-button ui-button-secondary cursor-pointer peer-focus-visible:outline-2 peer-focus-visible:outline-brand-500">Seleccionar archivo</label>
    <span class="break-all text-sm" x-text="archivos || 'Ningún archivo seleccionado'"></span>
</div>
