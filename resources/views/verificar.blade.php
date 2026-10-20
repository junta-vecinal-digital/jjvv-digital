<x-public-layout><section class="ui-panel p-6 sm:p-8">
    <h1 class="text-3xl font-semibold tracking-tight mb-3">Verificar un certificado</h1>
    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
        Ingresa el código que aparece al pie del certificado para comprobar que es auténtico y está vigente.
    </p>

    <form method="POST" action="{{ route('verificar.buscar') }}" class="space-y-3">
        @csrf
        <x-text-input name="codigo" type="text" class="block w-full font-mono uppercase" placeholder="ABCDE-FGHJK" required
                      :value="old('codigo')" />
        <x-input-error :messages="$errors->get('codigo')" />
        <div class="flex gap-4"><x-primary-button>Verificar</x-primary-button><a href="{{ url('/') }}" class="ui-button ui-button-secondary">Volver</a></div>
    </form>

    @if ($consultado)
        <div class="mt-6">
            @if (! $certificado)
                <div class="p-4 rounded-lg bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200 text-sm">
                    <strong>No encontramos un certificado con ese código.</strong>
                    Revisa que esté bien escrito. Si el problema persiste, contacta a la directiva.
                </div>
            @else
                @php
                    $estilos = [
                        'vigente' => ['Certificado válido', 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200'],
                        'vencido' => ['Certificado vencido', 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-200'],
                        'anulado' => ['Certificado anulado', 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200'],
                    ];
                    [$titulo, $clases] = $estilos[$estado];
                @endphp

                <div class="p-4 rounded-lg text-sm {{ $clases }}">
                    <strong>{{ $titulo }}</strong>
                    @if ($estado === 'vencido')
                        <div>Su vigencia terminó el {{ $certificado->fecha_vencimiento->format('d/m/Y') }}.</div>
                    @elseif ($estado === 'anulado')
                        <div>Fue dejado sin efecto por la directiva y no tiene validez.</div>
                    @endif
                </div>

                <dl class="mt-4 space-y-2 text-sm text-gray-800 dark:text-gray-200">
                    <div><dt class="text-gray-500 dark:text-gray-400">Tipo</dt><dd>{{ $certificado->etiquetaTipo() }}</dd></div>
                    <div><dt class="text-gray-500 dark:text-gray-400">Folio</dt><dd>{{ $certificado->folio }}</dd></div>
                    <div><dt class="text-gray-500 dark:text-gray-400">A nombre de</dt><dd>{{ $certificado->nombre_solicitante }} ({{ $rutEnmascarado }})</dd></div>
                    <div><dt class="text-gray-500 dark:text-gray-400">Presentado ante</dt><dd>{{ $certificado->dirigido_a }}</dd></div>
                    <div><dt class="text-gray-500 dark:text-gray-400">Emitido el</dt><dd>{{ $certificado->fecha_emision->format('d/m/Y') }}</dd></div>
                    <div><dt class="text-gray-500 dark:text-gray-400">Válido hasta</dt><dd>{{ $certificado->fecha_vencimiento->format('d/m/Y') }}</dd></div>
                    @if ($certificado->otorgante_nombre)
                        <div><dt class="text-gray-500 dark:text-gray-400">Otorgado por</dt><dd>{{ $certificado->otorgante_nombre }}@if($certificado->otorgante_cargo) · {{ $certificado->otorgante_cargo }}@endif</dd></div>
                    @endif
                </dl>
            @endif
        </div>
    @endif
</section><x-public-hours :horarios="$horariosAtencion" /></x-public-layout>
