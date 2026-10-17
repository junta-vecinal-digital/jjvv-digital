@props(['number'])

<fieldset data-paso="{{ $number }}" x-cloak x-show="paso === {{ $number }}" :inert="paso !== {{ $number }}"
    x-transition:enter="transition duration-200 ease-out motion-reduce:transition-none"
    x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0"
    {{ $attributes->class(['min-w-0 space-y-4']) }}>
    <legend class="sr-only" x-text="pasos[{{ $number }}]"></legend>
    {{ $slot }}
</fieldset>
