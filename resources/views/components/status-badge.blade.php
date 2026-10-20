@props(['state', 'label'])

@php
    $color = match ($state) {
        'pendiente', 'por_devolver' => 'bg-[#fff5df] text-[#79500b] dark:bg-amber-900/40 dark:text-amber-200',
        'confirmada', 'aprobado', 'devuelto', 'devuelta' => 'bg-[#e9f3ee] text-[#256245] dark:bg-green-900/40 dark:text-green-200',
        'cancelada', 'retenida', 'vencida' => 'bg-gray-100 text-red-700 dark:bg-gray-800 dark:text-red-200',
        'atrasado' => 'bg-red-100 text-red-900 dark:bg-red-900/40 dark:text-red-200',
        default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
    };
@endphp
<span class="inline-flex rounded-md px-2 py-1 text-xs font-medium {{ $color }}">{{ $label }}</span>
