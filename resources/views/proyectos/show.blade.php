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
        <section class="ui-panel p-6">
            <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Datos de la postulación</h2>
            <dl class="grid gap-4 text-sm sm:grid-cols-2">
                <div><dt class="text-gray-500 dark:text-gray-400">Organismo</dt><dd class="break-words">{{ $proyecto->organismo }}</dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">Fecha de postulación</dt><dd>{{ $proyecto->fecha_postulacion->format('d/m/Y') }}</dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">Estado</dt><dd>{{ \App\Models\Proyecto::ESTADOS[$proyecto->estado] }}</dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">Monto</dt><dd>{{ $proyecto->monto === null ? 'Sin indicar' : '$'.number_format($proyecto->monto, 0, ',', '.') }}</dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">Ingresó la postulación en la plataforma del organismo</dt><dd class="break-words">{{ $proyecto->postulado_por }}</dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">Registró el proyecto en JJVV Digital</dt><dd>{{ $proyecto->registrador?->name ?? 'Cuenta eliminada' }}</dd></div>
            </dl>
            <p class="mt-6 text-sm text-gray-500 dark:text-gray-400">Registro del {{ $proyecto->created_at->format('d/m/Y H:i') }}.</p>
        </section>
    </div>
</x-app-layout>
