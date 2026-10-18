<x-app-layout>
    <x-slot name="header"><h1 class="text-2xl font-semibold">Reporte financiero</h1></x-slot>
    <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">
        <a class="underline" href="{{ route('reportes.index') }}">Volver a reportes</a>
        @if ($errors->any())<div class="ui-panel p-4 text-red-600">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        <form method="GET" action="{{ route('reportes.financieros') }}" class="ui-panel p-4 flex flex-wrap gap-4 items-end">
            <div><x-input-label for="desde" value="Desde" /><x-date-input id="desde" name="desde" :value="$filtros['desde']" required /></div>
            <div><x-input-label for="hasta" value="Hasta" /><x-date-input id="hasta" name="hasta" :value="$filtros['hasta']" required /></div>
            <div class="min-w-0 w-full sm:w-80"><x-input-label for="grupo_id" value="Agrupación" /><select id="grupo_id" name="grupo_id" class="ui-input w-full"><option value="">Todas las agrupaciones</option><option value="sin_grupo" @selected(($filtros['grupo_id'] ?? '') === 'sin_grupo')>Sin agrupación</option>@foreach ($grupos as $grupo)<option value="{{ $grupo->id }}" @selected((string) ($filtros['grupo_id'] ?? '') === (string) $grupo->id)>{{ $grupo->nombre }}</option>@endforeach</select></div>
            <button class="ui-button ui-button-primary">Filtrar</button>
            <a class="underline" href="{{ route('reportes.financieros') }}">Restablecer</a>
        </form>
        <div class="flex flex-wrap gap-3 items-center justify-between"><p>{{ $grupoNombre }} · {{ \Carbon\Carbon::parse($filtros['desde'])->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($filtros['hasta'])->format('d/m/Y') }}</p><a class="ui-button ui-button-secondary" href="{{ route('reportes.financieros.pdf', $filtros) }}">Descargar PDF</a></div>
        <section class="ui-panel p-6 grid gap-4 sm:grid-cols-4">
            @foreach (['ingresos' => 'Pagos recibidos vigentes', 'egresos' => 'Egresos vigentes', 'devoluciones' => 'Cuotas devueltas', 'neto' => 'Resultado neto del período'] as $clave => $etiqueta)<div><p>{{ $etiqueta }}</p><strong>${{ number_format($totales[$clave], 0, ',', '.') }}</strong></div>@endforeach
        </section>
        <p class="text-sm">Pagos y egresos por su fecha de pago; cuotas devueltas por su fecha de devolución. Se excluyen los movimientos actualmente anulados. Las garantías recibidas y devueltas no forman parte de estos totales; las retenciones vigentes ya están incluidas como ingresos. El resultado del período no es el saldo histórico de caja.</p>
        <p class="text-sm">Sin agrupación reúne movimientos sin vínculo guardado, incluidos egresos e ingresos manuales. No se asignan grupos por nombres, conceptos o por ser representante.</p>
        <section class="ui-panel p-6 overflow-x-auto">@include('reportes._finanzas-tablas')</section>
    </div>
</x-app-layout>
