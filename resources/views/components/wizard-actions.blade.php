<div class="flex items-center justify-between gap-3 border-t border-gray-200 pt-5 dark:border-gray-800">
    <x-secondary-button x-bind:disabled="paso === 0 || guardando" @click="retroceder()">Anterior</x-secondary-button>
    <x-primary-button type="button" x-show="paso < pasos.length - 1" @click="avanzar()">Siguiente</x-primary-button>
    <x-primary-button x-cloak x-show="paso === pasos.length - 1" x-bind:disabled="guardando">Aceptar</x-primary-button>
</div>
