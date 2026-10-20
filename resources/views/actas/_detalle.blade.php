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
