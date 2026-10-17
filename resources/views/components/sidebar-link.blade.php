@props(['active' => false])

<a @if ($active) aria-current="page" @endif
    {{ $attributes->class(['menu-item', 'menu-item-active' => $active, 'menu-item-inactive' => ! $active]) }}>
    {{ $slot }}
</a>
