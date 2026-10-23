@php($tab = old('tab', request('tab', 'general')))
<x-app-layout>
<x-slot name="header">
<x-page-heading title="Parámetros del sistema" description="Configura horarios, plazos y catálogos." />
</x-slot>
<div class="ui-page">
<x-operation-notice />
<nav class="ui-tabs" aria-label="Configuración">
@foreach(['general'=>'Parámetros','actividades'=>'Actividades','insumos'=>'Catálogo de insumos'] as $clave=>$texto)<a href="{{ route('parametros.index',['tab'=>$clave]) }}" @if($tab === $clave) aria-current="page" @endif>{{ $texto }}</a>
@endforeach
</nav>
@if($tab === 'general')
<section class="ui-panel p-6">
<form method="POST" action="{{ route('parametros.update') }}" class="space-y-6">
@csrf @method('PUT')<input type="hidden" name="tab" value="general">
<div class="ui-form-grid">
@foreach($parametros as $parametro)<div>
<x-input-label :for="'valores_'.$parametro->clave" :value="$parametro->clave === 'certificado_residencia_valor' ? 'Aporte para residencia (CLP)' : $parametro->etiqueta" />
@if($parametro->clave === 'sede_duracion_bloque_min')<select id="valores_{{ $parametro->clave }}" name="valores[{{ $parametro->clave }}]" class="ui-input w-full" required>
@foreach([30,60] as $minutos)<option value="{{ $minutos }}" @selected((int)old('valores.'.$parametro->clave,$parametro->valor) === $minutos)>{{ $minutos }} minutos</option>
@endforeach
</select>
@else
<x-text-input :id="'valores_'.$parametro->clave" name="valores[{{ $parametro->clave }}]" :type="str_starts_with($parametro->clave, 'transferencia_') ? 'text' : (in_array($parametro->clave,['sede_hora_inicio','sede_hora_fin']) ? 'time' : 'number')" :min="$parametro->clave === 'certificado_residencia_valor' ? 0 : ((str_starts_with($parametro->clave, 'transferencia_') || in_array($parametro->clave,['sede_hora_inicio','sede_hora_fin'])) ? null : 1)" :value="old('valores.'.$parametro->clave,$parametro->valor)" required class="w-full" />
@endif
<x-input-error :messages="$errors->get('valores.'.$parametro->clave)" />
</div>
@endforeach
</div>
<x-primary-button>Guardar parámetros</x-primary-button>
</form>
</section>
@elseif($tab === 'actividades')
<p class="ui-notice">Los tipos nuevos son gratuitos. Los nombres y condiciones de las reservas registradas se conservan.</p>
<section class="ui-panel overflow-x-auto">
<table class="ui-table">
<thead>
<tr>
<th>Actividad</th>
<th>Estado</th>
<th>Gestión</th>
</tr>
</thead>
@foreach($tiposActividad as $tipoActividad)<x-record-row :id="'actividad_'.$tipoActividad->id" :columns="3" label="Editar actividad">
<x-slot name="summary">
<td>{{ $tipoActividad->nombre }}</td>
<td>{{ $tipoActividad->activo ? 'Activa' : 'Inactiva' }}</td>
</x-slot>
<form method="POST" action="{{ route('parametros.actividades.update',$tipoActividad) }}">
@csrf @method('PUT')<input type="hidden" name="tab" value="actividades">
<input type="hidden" name="operacion" value="actividad_{{ $tipoActividad->id }}">
<div class="ui-form-grid">
<div>
<x-input-label :for="'nombre_actividad_'.$tipoActividad->id" value="Nombre de actividad" />
<x-text-input :id="'nombre_actividad_'.$tipoActividad->id" name="nombre_actividad" :value="old('operacion') === 'actividad_'.$tipoActividad->id ? old('nombre_actividad') : $tipoActividad->nombre" maxlength="120" required class="w-full" />
</div>
<label class="flex items-center gap-2">
<input type="hidden" name="activo" value="0">
<input type="checkbox" name="activo" value="1" @checked(old('operacion') === 'actividad_'.$tipoActividad->id ? old('activo') : $tipoActividad->activo)> Activa</label>
</div>
<x-primary-button>Guardar actividad</x-primary-button>
</form>
</x-record-row>
@endforeach
</table>
</section>
<details class="ui-panel p-6" @if($errors->any() && !old('operacion')) open @endif>
<summary class="cursor-pointer font-semibold">Agregar actividad gratuita</summary>
<form method="POST" action="{{ route('parametros.actividades.store') }}" class="mt-6 space-y-4">
@csrf<input type="hidden" name="tab" value="actividades">
<x-input-label for="nueva_actividad" value="Nombre de la nueva actividad" />
<x-text-input id="nueva_actividad" name="nombre_actividad" :value="!old('operacion') ? old('nombre_actividad') : ''" maxlength="120" required />
<x-primary-button>Agregar actividad</x-primary-button>
</form>
</details>
@elseif($tab === 'insumos') @include('parametros._insumos') @endif
</div>
</x-app-layout>
