@props(['active'])

<nav aria-label="Apartados de reservas" class="flex flex-wrap gap-3">
    <a href="{{ route('reservas.index') }}" @if ($active === 'sede') aria-current="page" @endif class="ui-button {{ $active === 'sede' ? 'ui-button-primary' : 'ui-button-secondary' }}">Sede</a>
    <a href="{{ route('inventario.index') }}" @if ($active === 'insumos') aria-current="page" @endif class="ui-button {{ $active === 'insumos' ? 'ui-button-primary' : 'ui-button-secondary' }}">Insumos</a>
</nav>
