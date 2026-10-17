<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Acta de {{ mb_strtolower(\App\Models\Acta::TIPOS[$acta->tipo]) }} · {{ $acta->fecha->format('d/m/Y') }}</h1>
            <a href="{{ route('actas.index') }}" class="text-sm underline">Volver a actas</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        @if (session('status') === 'acta-registrada')
            <p role="status" class="rounded-lg bg-green-100 p-4 text-sm text-green-800 dark:bg-green-900/40 dark:text-green-200">Acta registrada.</p>
        @endif
        <section class="ui-panel space-y-4 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Acuerdos</h2>
            <div class="whitespace-pre-wrap break-words text-sm leading-7">{{ $acta->acuerdos }}</div>
        </section>
        <section class="ui-panel space-y-4 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Escaneo</h2>
            @if ($acta->escaneo_path)
                <a href="{{ route('actas.escaneo', $acta) }}" target="_blank" rel="noopener" class="ui-button ui-button-secondary">Ver escaneo</a>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">Esta acta no tiene escaneo adjunto.</p>
            @endif
        </section>
        @if (Auth::user()->esDirigente())
            <p class="text-sm text-gray-500 dark:text-gray-400">Registrada el {{ $acta->created_at->format('d/m/Y H:i') }} por {{ $acta->registrador?->name ?? 'Cuenta eliminada' }}.</p>
        @endif
    </div>
</x-app-layout>
