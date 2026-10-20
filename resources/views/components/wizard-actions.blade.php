@props(['cancel' => null, 'submitLabel' => 'Confirmar'])
<fieldset x-cloak x-show="paso === pasos.length - 1" :inert="paso !== pasos.length - 1" :data-paso="pasos.length - 1" class="space-y-3">
    <legend class="sr-only">Resumen</legend>
    <p>Revisa los datos antes de aceptar. Puedes volver con Anterior para corregirlos.</p>
    <x-form-summary />
</fieldset>
<div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 pt-5 dark:border-gray-800">
<span><x-secondary-button x-show="paso > 0" x-bind:disabled="guardando" @click="retroceder()">Anterior</x-secondary-button>
    @if($cancel === false)<x-secondary-button x-show="paso === 0" @click="cancelar()">Cancelar</x-secondary-button>@else<a x-show="paso === 0" class="ui-button ui-button-secondary" href="{{ $cancel ?? route('dashboard') }}">Cancelar</a>@endif</span>
    <x-primary-button type="button" x-show="paso < pasos.length - 1" @click="avanzar()">Continuar</x-primary-button>
    <x-primary-button x-cloak x-show="paso === pasos.length - 1" x-bind:disabled="guardando">{{ $submitLabel }}</x-primary-button>
</div>
