<x-app-layout>
<x-slot name="header">
<x-page-heading title="Horarios de atención al público" description="Publica los tramos semanales de ventanilla que se muestran en la portada.">
@unless(request('crear'))<a href="{{ route('atencion.index',['crear'=>1]) }}" class="ui-button ui-button-primary">Publicar horario</a>
@endunless
</x-page-heading>
</x-slot>
<div class="ui-page">
<x-operation-notice />
<p class="ui-notice">Estos tramos se repiten cada semana y permiten varios horarios por día. No crean citas de firma ni reservas de sede.</p>
@if(request('crear'))<section class="ui-panel p-6">
<x-form-wizard :steps="['Tramo semanal']" :fields="[['dia_semana','hora_inicio','hora_fin']]">
<form method="POST" action="{{ route('atencion.store') }}" novalidate>
@csrf<x-form-step number="0">@include('atencion._form',['horario'=>null])</x-form-step>
<x-wizard-actions :cancel="route('atencion.index')" submit-label="Publicar horario" />
</form>
</x-form-wizard>
</section>
@else
<h2 class="text-xl font-semibold">Horarios publicados</h2>
<section class="ui-panel overflow-x-auto">
<table class="ui-table">
<thead>
<tr>
<th>Día</th>
<th>Atención</th>
<th>Gestión</th>
</tr>
</thead>
@forelse($horarios as $horario)<x-record-row :id="'horario_'.$horario->id" :columns="3" label="Editar horario">
<x-slot name="summary">
<td>{{ \App\Models\HorarioAtencion::DIAS[$horario->dia_semana] }}</td>
<td>{{ substr($horario->hora_inicio,0,5) }}–{{ substr($horario->hora_fin,0,5) }}</td>
</x-slot>
<h2 class="text-xl font-semibold">Editar horario de atención</h2>
<form method="POST" action="{{ route('atencion.update',$horario) }}">
@csrf @method('PUT')<input type="hidden" name="operacion" value="horario_{{ $horario->id }}">@include('atencion._form')<x-primary-button>Guardar cambios</x-primary-button>
</form>
<details>
<summary>Retirar horario</summary>
<form method="POST" action="{{ route('atencion.destroy',$horario) }}">
@csrf @method('DELETE')<label class="flex gap-2">
<input type="checkbox" required>Confirmo que deseo retirar este horario de la portada pública.</label>
<x-danger-button>Retirar horario</x-danger-button>
</form>
</details>
</x-record-row>
@empty
<tbody>
<tr>
<td colspan="3">No hay horarios publicados.</td>
</tr>
</tbody>
@endforelse
</table>
</section>
@endif
</div>
</x-app-layout>
