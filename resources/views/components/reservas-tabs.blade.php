@props(['active'])

<nav aria-label="Apartados de reservas" class="flex flex-wrap gap-3">
    @php($gestiona = auth()->user()->hasRole('presidente', 'secretario'))
    @foreach (['sede' => [$gestiona ? 'reservas.index' : 'arriendos.index', 'Reservas de sede'], 'solicitudes' => ['insumos.index', 'Solicitudes de insumos']] as $clave => [$ruta, $etiqueta])
        <a href="{{ route($ruta) }}" @if ($active === $clave) aria-current="page" @endif class="ui-button {{ $active === $clave ? 'ui-button-primary' : 'ui-button-secondary' }}">{{ $etiqueta }}</a>
    @endforeach
</nav>
