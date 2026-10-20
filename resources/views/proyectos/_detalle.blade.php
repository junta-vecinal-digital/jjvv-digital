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
@if(auth()->user()->hasRole('presidente'))<details @if(old('operacion') === 'proyecto_'.$proyecto->id) open @endif><summary>Editar proyecto</summary>@include('proyectos._editar')</details>@endif