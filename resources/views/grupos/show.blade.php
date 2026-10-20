<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white leading-tight">{{ $grupo->nombre }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <p class="px-4 sm:px-0 text-green-700 dark:text-green-400">Cambios guardados.</p>
            @endif

            @include('grupos._detalle')
            <a href="{{ route('grupos.index') }}" class="underline text-sm text-gray-600 dark:text-gray-400">Volver al listado</a>
        </div>
    </div>
</x-app-layout>
