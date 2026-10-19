<x-app-layout>
    <x-slot name="header"><h1 class="text-2xl font-semibold">Reportes</h1></x-slot>
    <div class="max-w-5xl mx-auto px-4 py-6 grid gap-6 sm:grid-cols-2">
        @if (auth()->user()->hasRole('presidente', 'tesorero'))
            <section class="ui-panel p-6 space-y-4"><h2 class="font-semibold">Reporte financiero</h2><p>Aportes voluntarios recibidos, egresos, cuotas devueltas y reservas de sede por período y agrupación.</p><a class="ui-button ui-button-primary" href="{{ route('reportes.financieros') }}">Consultar finanzas</a></section>
        @endif
        @if (auth()->user()->hasRole('presidente', 'secretario'))
            <section class="ui-panel p-6 space-y-4"><h2 class="font-semibold">Listado de socios activos</h2><p>Nombre, RUT, estado y fecha de ingreso, sin datos de contacto. Descarga en Excel o PDF.</p><a class="ui-button ui-button-primary" href="{{ route('reportes.socios') }}">Consultar socios activos</a></section>
        @endif
    </div>
</x-app-layout>
