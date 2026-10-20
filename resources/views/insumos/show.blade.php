<x-app-layout>
    <x-slot name="header"><x-page-heading :title="'Solicitud de insumos #'.$solicitud->id" description="Revisa el estado, las unidades solicitadas y las condiciones del préstamo." /></x-slot>
    <div class="max-w-4xl mx-auto px-4 py-6 space-y-6">
        @if (request('consulta') === 'historial' && auth()->user()->hasRole('presidente', 'secretario'))
            <a class="underline" href="{{ route('inventario.prestamos.index') }}">Volver al historial de inventario</a>
        @else
        <a class="underline" href="{{ route($gestion && in_array($solicitud->estado, ['entregado', 'devuelto']) ? 'inventario.prestamos.index' : 'insumos.index') }}">Volver a {{ $gestion && in_array($solicitud->estado, ['entregado', 'devuelto']) ? 'préstamos entregados' : 'solicitudes' }}</a>
        @endif
        @if (session('status'))<p role="status" class="ui-panel p-4">{{ session('status') }}</p>@endif
        @if ($errors->any())<div class="ui-panel p-4 text-red-600">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        @include('insumos._detalle')
    </div>
</x-app-layout>
