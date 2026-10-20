<nav class="ui-tabs" aria-label="Tipo de reporte">
@if(auth()->user()->hasRole('presidente','tesorero'))<a href="{{ route('reportes.financieros') }}" @if(request()->routeIs('reportes.financieros')) aria-current="page" @endif>Financiero</a>
@endif @if(auth()->user()->hasRole('presidente','secretario'))<a href="{{ route('reportes.socios') }}" @if(request()->routeIs('reportes.socios')) aria-current="page" @endif>Socios activos</a>
@endif
</nav>
