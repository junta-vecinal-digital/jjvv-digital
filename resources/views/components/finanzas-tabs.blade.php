<nav aria-label="Movimientos financieros" class="ui-tabs">
    <a href="{{ route('ingresos.index') }}" @if (request()->routeIs('ingresos.*', 'finanzas.pendientes.*')) aria-current="page" @endif>Ingresos</a>
    <a href="{{ route('egresos.index') }}" @if (request()->routeIs('egresos.*')) aria-current="page" @endif>Egresos</a>
</nav>
