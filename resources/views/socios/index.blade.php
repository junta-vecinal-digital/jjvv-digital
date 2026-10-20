<x-app-layout>
<x-slot name="header">
<x-page-heading :title="$archivo ? 'Archivo de socios' : 'Gestión de socios'" :description="$archivo ? 'Consulta solicitudes rechazadas y fichas de socios inactivos.' : 'Consulta socios activos y continúa las inscripciones pendientes.'">
@unless($archivo)<a href="{{ route('reportes.socios') }}" class="ui-button ui-button-secondary">Exportar socios activos</a>
<a href="{{ route('socios.crear') }}" class="ui-button ui-button-primary">Registrar socio</a>
@endunless
</x-page-heading>
</x-slot>
<div class="ui-page">
<x-operation-notice />
<nav class="ui-tabs" aria-label="Estado de la ficha">
            @foreach(($archivo ? ['rechazado' => 'Solicitudes rechazadas', 'inactivo' => 'Socios inactivos'] : ['activo' => 'Socios activos', 'pendientes' => 'Socios pendientes']) as $clave => $etiqueta)
                <a href="{{ route($archivo ? 'socios.archivo' : 'socios.index', ['estado' => $clave]) }}" @if($estado === $clave || ($clave === 'pendientes' && in_array($estado, ['pendiente','por_firmar']))) aria-current="page" @endif>{{ $etiqueta }}</a>
            @endforeach
        </nav>
<form method="GET" class="ui-tools">
<input type="hidden" name="estado" value="{{ $estado }}">
<div>
<x-input-label for="buscar" value="Buscar" />
<x-text-input id="buscar" name="buscar" :value="request('buscar')" placeholder="Nombre o RUT" />
</div>
<x-secondary-button type="submit">Buscar</x-secondary-button>
</form>
<section class="ui-panel overflow-x-auto">
<table class="ui-table">
<thead>
<tr>
<th>Socio / RUT</th>
<th>Solicitud</th>
<th>Estado</th>
<th>Acción</th>
</tr>
</thead>
            @forelse($socios as $socio)
                <x-record-row :id="'socio_'.$socio->id" :columns="4" :label="$socio->estado === 'pendiente' ? 'Revisar inscripción' : 'Ver ficha'">
<x-slot name="summary">
<td>
<strong>{{ $socio->nombre_completo }}</strong>
<span class="ui-sub">{{ $socio->rut }}</span>
</td>
<td>{{ $socio->created_at->format('d/m/Y') }}</td>
<td>
<x-estado-socio :estado="$socio->estado" />
</td>
</x-slot>
                    @include('socios._detalle')
                </x-record-row>
            @empty
<tbody>
<tr>
<td colspan="4">No hay registros con estos filtros.</td>
</tr>
</tbody>
@endforelse
        </table>
</section>
        {{ $socios->links() }}
    </div>
</x-app-layout>
