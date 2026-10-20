<x-app-layout>
<x-slot name="header">
<x-page-heading :title="'Editar usuario: '.$usuario->name" />
</x-slot>
<div class="ui-page">
<a href="{{ route('usuarios.index') }}" class="ui-link">Volver al listado</a>
<x-operation-notice />
<section class="ui-panel p-6 space-y-6">@include('usuarios._editar')</section>
</div>
</x-app-layout>
