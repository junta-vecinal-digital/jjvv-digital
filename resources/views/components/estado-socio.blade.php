@props(['estado'])

@php
    $mapa = [
        'pendiente'  => ['Pendiente de revisión', 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-200'],
        'por_firmar' => ['Pendiente de firma',    'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200'],
        'activo'     => ['Socio activo',          'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200'],
        'rechazado'  => ['Rechazada',             'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200'],
    ];
    [$etiqueta, $clases] = $mapa[$estado];
@endphp

<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $clases }}">
    {{ $etiqueta }}
</span>