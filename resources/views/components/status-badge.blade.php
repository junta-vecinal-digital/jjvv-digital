@props(['state', 'label'])

@php
    $color = match ($state) {
        'pendiente' => 'bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-200',
        'confirmada', 'aprobado', 'devuelto' => 'bg-green-100 text-green-900 dark:bg-green-900/40 dark:text-green-200',
        'entregado' => 'bg-blue-100 text-blue-900 dark:bg-blue-900/40 dark:text-blue-200',
        'atrasado' => 'bg-red-100 text-red-900 dark:bg-red-900/40 dark:text-red-200',
        default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
    };
@endphp
<span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $color }}">{{ $label }}</span>
