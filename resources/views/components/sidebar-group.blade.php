@props(['label', 'active' => false, 'pending' => false])

<details class="group" @if ($active) open @endif>
    <summary class="menu-item cursor-pointer {{ $active ? 'menu-item-active' : 'menu-item-inactive' }}">
        <span class="text-black transition-transform group-open:rotate-90" aria-hidden="true">▶</span>
        {{ $label }}
        @if ($pending)<x-pendiente-dot />@endif
    </summary>
    <div class="ml-3 space-y-1 border-l border-gray-200 pl-2 dark:border-gray-700">{{ $slot }}</div>
</details>
