<x-app-layout>
<x-slot name="header">
<x-page-heading title="Gestión de agrupaciones" description="Consulta representantes, contactos y horarios fijos de uso de la sede.">
@unless(request('crear'))<a href="{{ route('grupos.index', ['crear' => 1]) }}" class="ui-button ui-button-primary">Registrar agrupación</a>
@endunless
</x-page-heading>
</x-slot>
<div class="ui-page">
<x-operation-notice />
@if(request('crear')) @include('grupos._form', ['grupo' => null]) @else
<section class="ui-panel overflow-x-auto">
<table class="ui-table">
<thead>
<tr>
<th>Agrupación</th>
<th>Representante</th>
<th>Estado</th>
<th>Horarios fijos</th>
<th>Gestión</th>
</tr>
</thead>
@forelse($grupos as $grupo)<x-record-row :id="'grupo_'.$grupo->id" :columns="5" label="Ver detalles y gestionar">
<x-slot name="summary">
<td>
<strong>{{ $grupo->nombre }}</strong>
</td>
<td>{{ $grupo->datosRepresentante()['nombre'] ?? 'Sin representante' }}</td>
<td>{{ $grupo->tieneRepresentanteVigente() ? 'Con representante' : 'Sin representante vigente' }}</td>
<td>
@forelse($grupo->horariosFijos as $horario){{ \App\Models\HorarioFijo::DIAS[$horario->dia_semana] }} · {{ substr($horario->hora_inicio,0,5) }}–{{ substr($horario->hora_fin,0,5) }}<br>
@empty Sin horarios @endforelse
</td>
</x-slot>@include('grupos._detalle')</x-record-row>
@empty
<tbody>
<tr>
<td colspan="5">No hay agrupaciones registradas.</td>
</tr>
</tbody>
@endforelse
</table>
</section>
@endif
</div>
</x-app-layout>
