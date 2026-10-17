@props(['steps', 'fields' => []])

@php
    $primerPaso = 0;
    foreach ($fields as $numero => $campos) {
        if ($errors->hasAny($campos)) {
            $primerPaso = $numero;
            break;
        }
    }
@endphp

<div x-data="formWizard(@js($steps), @js($primerPaso))" @submit="confirmar($event)" class="overflow-hidden">
    <ol class="mb-4 flex flex-wrap gap-3 text-sm" aria-label="Pasos del formulario">
        @foreach ($steps as $numero => $etiqueta)
            <li class="flex items-center gap-2" :aria-current="paso === {{ $numero }} ? 'step' : null">
                <span class="flex h-7 w-7 items-center justify-center rounded-full"
                    :class="paso === {{ $numero }} ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'">{{ $numero + 1 }}</span>
                {{ $etiqueta }}
            </li>
        @endforeach
    </ol>
    <p x-ref="tituloPaso" tabindex="-1" aria-live="polite" class="mb-4 font-medium text-gray-900 outline-none dark:text-white" x-text="pasos[paso]"></p>
    {{ $slot }}
</div>
