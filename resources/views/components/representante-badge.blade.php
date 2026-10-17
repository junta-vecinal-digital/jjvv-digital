@props(['socio'])

@if ($socio->esRepresentante())
    <span class="inline-flex rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">Representante de agrupación</span>
@endif
