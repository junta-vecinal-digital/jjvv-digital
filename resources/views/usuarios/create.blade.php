<x-app-layout>
<x-slot name="header">
<x-page-heading title="Crear usuario" description="Registra una cuenta y define su acceso al sistema." />
</x-slot>
<div class="ui-page">
<x-operation-notice />
<section class="ui-panel p-6">
<form method="POST" action="{{ route('usuarios.store') }}" x-data="claveCuenta" class="space-y-6">
@csrf @include('usuarios._form', ['usuario' => null])<div class="ui-form-actions">
<a class="ui-button ui-button-secondary" href="{{ route('usuarios.index') }}">Cancelar</a>
<x-primary-button>Crear usuario</x-primary-button>
</div>
</form>
</section>
</div>
</x-app-layout>
