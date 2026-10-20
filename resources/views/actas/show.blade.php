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
        @include('actas._detalle')
    </div>
</x-app-layout>
