<x-app-layout>
    <x-slot name="header"><x-page-heading title="Reportes" description="Consulta resultados y prepara las exportaciones según los permisos de tu cargo." /></x-slot>
    <div class="max-w-5xl mx-auto px-4 py-6 space-y-6">
        <x-reportes-tabs />
        <p>Solo socios activos. Se exportan nombre, RUT, estado y fecha de ingreso, sin datos de contacto.</p>
        <form method="GET" action="{{ route('reportes.socios') }}" class="ui-panel p-4 flex flex-wrap gap-3 items-end">
            <div><x-input-label for="buscar" value="Buscar por nombre o RUT" /><x-text-input id="buscar" name="buscar" :value="$buscar" maxlength="100" /></div><button class="ui-button ui-button-primary">Buscar</button><a class="underline" href="{{ route('reportes.socios') }}">Limpiar</a>
        </form>
        <x-input-error :messages="$errors->get('buscar')" />
        <div class="flex flex-wrap gap-3"><a class="ui-button ui-button-secondary" href="{{ route('reportes.socios.excel', ['buscar' => $buscar]) }}">Descargar Excel</a><a class="ui-button ui-button-secondary" href="{{ route('reportes.socios.pdf', ['buscar' => $buscar]) }}">Descargar PDF</a></div>
        <p>{{ $socios->total() }} socios activos encontrados. Las descargas incluyen todos los resultados de la búsqueda, no solo esta página.</p>
        <section class="ui-panel overflow-x-auto"><table class="ui-table"><thead><tr><th>Nombre</th><th>RUT</th><th>Estado</th><th>Fecha de ingreso</th></tr></thead><tbody>
            @forelse ($socios as $socio)<tr><td>{{ $socio->nombre_completo }}</td><td>{{ $socio->rut }}</td><td>Activo</td><td>{{ $socio->fecha_ingreso?->format('d/m/Y') ?? 'Sin fecha registrada' }}</td></tr>@empty<tr><td colspan="4">No hay socios activos para esta búsqueda.</td></tr>@endforelse
        </tbody></table></section>
        {{ $socios->links() }}
    </div>
</x-app-layout>
