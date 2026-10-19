<x-app-layout>
    <x-slot name="header"><x-page-heading title="Historial de préstamos" description="Consulta de entregas y devoluciones de insumos." /></x-slot>
    <div class="mx-auto max-w-6xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
        @include('inventario._historial')
    </div>
</x-app-layout>
