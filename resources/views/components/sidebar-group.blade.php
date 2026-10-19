@props(['label', 'active' => false, 'pending' => false])

<details class="group" @if ($active) open @endif>
    <summary class="menu-item cursor-pointer {{ $active ? 'menu-item-active' : 'menu-item-inactive' }}">
        {{ $label }}
        @if ($pending)<x-pendiente-dot />@endif
        <span class="{{ $pending ? '' : 'ml-auto' }} transition-transform group-open:rotate-180" aria-hidden="true">⌄</span>
    </summary>
    <div class="ml-3 space-y-1 border-l border-gray-200 pl-2 dark:border-gray-700">{{ $slot }}</div>
</details>
