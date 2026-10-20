@props(['id', 'name', 'type' => 'password', 'autocomplete' => 'new-password'])
<span class="flex items-center gap-2" x-data="{ visible: false }">
<input id="{{ $id }}" name="{{ $name }}" :type="visible ? 'text' : 'password'" autocomplete="{{ $autocomplete }}" {{ $attributes->except(['type'])->class(['ui-input min-w-0 w-full']) }}>
<button type="button" class="ui-button ui-button-secondary shrink-0" @click="visible = !visible" :aria-pressed="visible" aria-controls="{{ $id }}" x-text="visible ? 'Ocultar' : 'Mostrar'">Mostrar</button>
</span>
