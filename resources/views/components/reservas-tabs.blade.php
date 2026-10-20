@props(['active'])

<nav aria-label="Apartados de reservas" class="ui-tabs">
    @php($gestiona = auth()->user()->hasRole('presidente', 'secretario'))
    @foreach (['sede' => [$gestiona ? 'reservas.index' : 'arriendos.index', 'Reservas de sede'], 'solicitudes' => ['insumos.index', 'Solicitudes de insumos']] as $clave => [$ruta, $etiqueta])
        <a href="{{ route($ruta) }}" @if ($active === $clave) aria-current="page" @endif>{{ $etiqueta }}</a>
    @endforeach
</nav>
