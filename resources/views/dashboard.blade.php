<x-app-layout>
<x-slot name="header">
<x-page-heading title="Panel de inicio" description="Revisa los pendientes y continúa una gestión." />
</x-slot>
<div class="ui-page">
@if(auth()->user()->hasRole('admin'))
<section class="ui-panel p-6 space-y-5">
<h2 class="text-xl font-semibold">Administración</h2>
<p>Gestiona las cuentas y la configuración del sistema.</p>
<div class="flex flex-wrap gap-4">
<a class="ui-button ui-button-primary" href="{{ route('usuarios.index') }}">Gestionar usuarios</a>
<a class="ui-button ui-button-secondary" href="{{ route('parametros.index') }}">Parámetros y catálogos</a>
<a class="ui-button ui-button-secondary" href="{{ route('profile.edit') }}">Mi perfil</a>
</div>
</section>
@else
@if(auth()->user()->hasRole('presidente'))<h2 class="text-xl font-semibold">Pendientes de revisión</h2>
<section class="ui-panel overflow-x-auto">
<table class="ui-table">
<thead>
<tr>
<th>Gestión</th>
<th>Pendiente</th>
<th>Acción</th>
</tr>
</thead>
<tbody>
@foreach($sociosPendientes as $pendiente)<tr>
<td>Inscripciones</td>
<td>{{ $pendiente->nombre_completo }} · Documentos por revisar</td>
<td>
<a class="ui-link" href="{{ route('socios.index',['estado'=>'pendientes']) }}">Revisar socios pendientes</a>
</td>
</tr>
@endforeach
@foreach($certificadosPendientes as $pendiente)<tr>
<td>Certificados</td>
<td>{{ $pendiente->nombre_solicitante }} · {{ $pendiente->etiquetaTipo() }}</td>
<td>
<a class="ui-link" href="{{ route('admin.certificados.index',['estado'=>'solicitado']) }}">Revisar certificados pendientes</a>
</td>
</tr>
@endforeach
@if($sociosPendientes->isEmpty() && $certificadosPendientes->isEmpty())<tr>
<td colspan="3">No hay pendientes de revisión.</td>
</tr>
@endif
</tbody>
</table>
</section>
@endif
@if(auth()->user()->hasRole('presidente','secretario'))<section class="ui-panel p-6 space-y-5">
<h2 class="text-xl font-semibold">Atención en ventanilla</h2>
<div class="flex flex-wrap gap-4">
<a class="ui-button ui-button-secondary" href="{{ route('socios.crear') }}">Registrar socio</a>
<a class="ui-button ui-button-secondary" href="{{ route('admin.certificados.create') }}">Emitir certificado</a>
<a class="ui-button ui-button-secondary" href="{{ route('reservas.index',['crear'=>1]) }}">Reservar sede</a>
<a class="ui-button ui-button-secondary" href="{{ route('insumos.index',['crear'=>1]) }}">Solicitar insumos</a>
</div>
</section>
@endif
<div class="flex flex-wrap gap-4">
@if(auth()->user()->hasRole('presidente','secretario') || auth()->user()->esSocioActivo())<a class="ui-button ui-button-secondary" href="{{ route('calendario.index') }}">Consultar calendario</a>
@endif
<a class="ui-button ui-button-secondary" href="{{ route('profile.edit') }}">Mi perfil</a>
@if(auth()->user()->hasRole('vecino'))<a class="ui-button ui-button-secondary" href="{{ route('socios.create') }}">Mi ficha de socio</a>
@endif
</div>
@endif
</div>
</x-app-layout>
