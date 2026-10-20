<x-app-layout>
    <x-slot name="header"><x-page-heading title="Proyectos" description="Consulta las postulaciones ingresadas en los organismos y su estado.">@if (Auth::user()->hasRole('presidente'))
                <a href="{{ route('proyectos.create') }}" class="ui-button ui-button-primary">Registrar proyecto</a>
            @endif</x-page-heading></x-slot>

    <div class="mx-auto max-w-6xl space-y-4 px-4 py-6 sm:px-6 lg:px-8">
        <p class="text-sm text-gray-500 dark:text-gray-400">Consulta los proyectos postulados por la junta y su estado.</p>

        <x-operation-notice />
        <section class="ui-panel overflow-x-auto"><table class="ui-table"><thead><tr><th>Proyecto / organismo</th><th>Postulación</th><th>Estado</th><th>Monto</th><th>Consulta</th></tr></thead>
            @forelse($proyectos as $proyecto)<x-record-row :id="'proyecto_'.$proyecto->id" :columns="5" label="Ver proyecto"><x-slot name="summary"><td><strong>{{ $proyecto->titulo }}</strong><span class="ui-sub">{{ $proyecto->organismo }}</span></td><td>{{ $proyecto->fecha_postulacion->format('d/m/Y') }}</td><td><x-status-badge :state="$proyecto->estado" :label="\App\Models\Proyecto::ESTADOS[$proyecto->estado]" /></td><td>{{ $proyecto->monto === null ? 'Sin indicar' : '$'.number_format($proyecto->monto,0,',','.') }}</td></x-slot>
                @include('proyectos._detalle')
            </x-record-row>
            @empty<tbody><tr><td colspan="5">No hay proyectos para mostrar.</td></tr></tbody>@endforelse
        </table></section>
        {{ $proyectos->links() }}
    </div>
</x-app-layout>
