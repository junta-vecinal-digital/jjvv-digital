<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white leading-tight">
                {{ $socio->nombre_completo }}
                <x-representante-badge :socio="$socio" />
            </h2>
            <div class="flex items-center gap-4 text-sm">
                @if ($socio->estado === 'activo')
                    <a href="{{ route('admin.certificados.create', ['socio' => $socio->id]) }}" class="underline text-brand-600 dark:text-brand-400">Emitir certificado</a>
                @endif
                <a href="{{ route('socios.index') }}" class="underline text-gray-600 dark:text-gray-400">← Volver al listado</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @php
                $mensajes = [
                    'socio-creado' => 'El socio fue registrado.',
                    'socio-editado' => 'Los datos del socio fueron actualizados.',
                    'socio-por_firmar' => 'Documentos aprobados. La persona ya puede reservar su hora para firmar el libro.',
                    'socio-rechazado' => 'La solicitud fue rechazada.',
                    'socio-activo' => 'El socio quedó activo.',
                    'socio-pendiente' => 'La solicitud fue reabierta. La persona puede corregir sus datos y reenviarla.',
                    'socio-inactivo' => 'El socio fue dado de baja.',
                ];
            @endphp

            @if (isset($mensajes[session('status')]))
                <div class="p-4 bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 rounded-lg text-sm">{{ $mensajes[session('status')] }}</div>
            @endif
            @if (session('status') === 'socio-por_firmar')
                <div role="status" class="rounded-lg bg-brand-50 p-4 text-sm text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    Recuerda habilitar horas disponibles para la firma del libro en la
                    <a href="{{ route('agenda.index') }}" class="font-medium underline">Agenda de firmas</a>.
                    Los horarios públicos de atención no crean estas citas automáticamente.
                </div>
            @endif
            
            @include('socios._detalle')
        </div>
    </div>
</x-app-layout>
