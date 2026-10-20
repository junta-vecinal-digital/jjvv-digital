<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $proyecto->titulo }}</h1>
            <div class="flex flex-wrap gap-3">
                @if (Auth::user()->hasRole('presidente'))
                    <a href="{{ route('proyectos.edit', $proyecto) }}" class="ui-button ui-button-primary">Editar proyecto</a>
                @endif
                <a href="{{ route('proyectos.index') }}" class="ui-button ui-button-secondary">Volver a proyectos</a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        @if (session('status'))
            <p role="status" class="rounded-lg bg-green-100 p-4 text-sm text-green-800 dark:bg-green-900/40 dark:text-green-200">{{ session('status') }}</p>
        @endif
        @include('proyectos._detalle')
    </div>
</x-app-layout>
