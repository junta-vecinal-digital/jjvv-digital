<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white leading-tight">
                {{ $certificado->etiquetaTipo() }} {{ $certificado->folio ? '· Folio '.$certificado->folio : '' }}
            </h2>
            <a href="{{ route('admin.certificados.index') }}" class="text-sm underline text-gray-600 dark:text-gray-400">← Volver al listado</a>
        </div>
    </x-slot>

    @php
        $cls = 'mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-brand-500 dark:focus:border-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400 rounded-md shadow-sm';
        $c = $certificado;
        $mensajes = [
            'certificado-emitido' => 'El certificado fue emitido.',
            'certificado-rechazado' => 'La solicitud fue rechazada.',
            'certificado-anulado' => 'El certificado fue anulado.',
            'certificado-retirado' => 'El retiro fue registrado.',
        ];
    @endphp

    <div class="py-6">
        @if ($certificado->solo_impresion)
            <p class="mx-auto max-w-3xl px-4 pb-4 text-sm">Certificado de socio emitido en ventanilla: disponible solo para impresión, sin acceso desde la cuenta del socio.</p>
        @endif
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (isset($mensajes[session('status')]))
                <div class="p-4 bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 rounded-lg text-sm">{{ $mensajes[session('status')] }}</div>
            @endif

            @include('certificados.admin._detalle')
        </div>
    </div>
</x-app-layout>
