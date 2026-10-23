@php
    $operacion = 'certificado_'.$certificado->id;
    $reintento = old('operacion') === $operacion;
    $valor = fn ($campo, $defecto = null) => $reintento ? old($campo, $defecto) : $defecto;
    $errors = $reintento ? $errors : new \Illuminate\Support\ViewErrorBag;
    $c = $certificado;
    $cls = 'ui-input w-full';
@endphp
            {{-- ===== Resumen ===== --}}
            <div class="p-6 ui-panel">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Datos del certificado</h3>
                    <x-estado-certificado :estado="$c->estado" />
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div><dt class="text-gray-500 dark:text-gray-400">Solicitante</dt><dd class="text-gray-900 dark:text-gray-100">{{ $c->nombre_solicitante }}</dd></div>
                    <div><dt class="text-gray-500 dark:text-gray-400">RUT</dt><dd class="text-gray-900 dark:text-gray-100">{{ $c->rut_solicitante }}</dd></div>
                    <div><dt class="text-gray-500 dark:text-gray-400">Dirección</dt><dd class="text-gray-900 dark:text-gray-100">{{ $c->direccion }}</dd></div>
                    <div><dt class="text-gray-500 dark:text-gray-400">Dirigido a</dt><dd class="text-gray-900 dark:text-gray-100">{{ $c->dirigido_a }}</dd></div>
                    <div><dt class="text-gray-500 dark:text-gray-400">Solicitud registrada</dt><dd class="text-gray-900 dark:text-gray-100">{{ $c->created_at->format('d/m/Y H:i') }}</dd></div>
                    @if ($c->socio)
                        <div><dt class="text-gray-500 dark:text-gray-400">Ficha de socio</dt><dd><a class="underline text-brand-600 dark:text-brand-400" href="{{ route('socios.show', $c->socio) }}">{{ $c->socio->nombre_completo }}</a></dd></div>
                    @endif
                    @if ($c->tipo === 'residencia')<div><dt class="text-gray-500 dark:text-gray-400">Aporte</dt><dd>{{ $c->aporte === null ? 'No registrado en la solicitud histórica' : '$'.number_format($c->aporte, 0, ',', '.') }}</dd></div>@endif
                    @if ($c->medio_pago || $c->numero_transaccion)
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Respaldo del aporte</dt>
                            <dd class="text-gray-900 dark:text-gray-100">
                                {{ \App\Models\Ingreso::MEDIOS[$c->medio_pago] ?? '—' }}
                                @if ($c->talonario) · talonario {{ $c->talonario }} @endif
                                @if ($c->numero_transaccion) · transacción {{ $c->numero_transaccion }} @endif
                            </dd>
                        </div>
                    @endif
                    @if ($c->estado !== 'solicitado' && $c->estado !== 'rechazado')
                        <div><dt class="text-gray-500 dark:text-gray-400">Emitido el</dt><dd class="text-gray-900 dark:text-gray-100">{{ $c->fecha_emision?->format('d/m/Y') }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Vence el</dt><dd class="text-gray-900 dark:text-gray-100">{{ $c->fecha_vencimiento?->format('d/m/Y') }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Otorgado por</dt><dd class="text-gray-900 dark:text-gray-100">{{ $c->otorgante_nombre ?? 'Identidad histórica no registrada' }} @if ($c->otorgante_cargo) ({{ $c->otorgante_cargo }}) @endif</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Código de verificación</dt><dd class="text-gray-900 dark:text-gray-100 font-mono">{{ $c->codigoFormateado() }}</dd></div>
                    @endif
                    @if ($c->observaciones)
                        <div class="sm:col-span-2"><dt class="text-gray-500 dark:text-gray-400">Observaciones</dt><dd class="text-gray-900 dark:text-gray-100">{{ $c->observaciones }}</dd></div>
                    @endif
                </dl>

                <div class="mt-4 flex flex-wrap gap-4 text-sm">
                    <a href="{{ route('admin.certificados.pdf', $c) }}" target="_blank" class="underline text-brand-600 dark:text-brand-400">
                        {{ $c->estado === 'solicitado' ? 'Ver PDF de revisión (borrador)' : 'Ver PDF' }}
                    </a>
                    @if ($c->comprobante_path)
                        <a href="{{ route('admin.certificados.comprobante', $c) }}" target="_blank" class="underline text-brand-600 dark:text-brand-400">Ver comprobante</a>
                    @endif
                </div>
            </div>

            {{-- ===== Revisión y emisión de solicitudes en línea ===== --}}
            @if ($c->estado === 'solicitado')
                <div class="p-6 ui-panel">
                    <form method="POST" action="{{ route('admin.certificados.emitir', $c) }}">
                        @csrf
                        <input type="hidden" name="operacion" value="{{ $operacion }}">
                        <input type="hidden" name="validar_emitir" value="1">
                        <x-primary-button>Validar y emitir certificado</x-primary-button>
                    </form>
                </div>
            @endif

            @if ($c->tipo === 'residencia' && $c->estado === 'emitido')
                <details class="text-xs text-gray-500">
                    <summary>Descargas: {{ $c->descargas->count() }}@if($c->descargas->isNotEmpty()) · Última: {{ $c->descargas->sortByDesc('descargado_en')->first()->descargado_en->format('d/m/Y H:i') }}@endif</summary>
                    @foreach ($c->descargas->sortByDesc('descargado_en') as $descarga)
                        <p>{{ $descarga->descargado_en->format('d/m/Y H:i') }} · {{ $descarga->usuario?->name ?? 'Usuario eliminado' }} · IP {{ $descarga->ip }}</p>
                    @endforeach
                </details>
            @endif

            {{-- ===== Anulación ===== --}}
            @if ($c->estado === 'emitido')
                <div class="p-6 ui-panel">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">Anular certificado</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Úsalo si se emitió con errores. En la verificación en línea aparecerá como anulado.</p>
                    <form method="POST" action="{{ route('admin.certificados.anular', $c) }}" class="space-y-3"
                          onsubmit="return confirm('¿Anular este certificado? Esta acción no se puede deshacer.');">
                        @csrf
<input type="hidden" name="operacion" value="{{ $operacion }}">
                        <textarea name="observaciones" rows="2" required placeholder="Motivo de la anulación" class="{{ $cls }}">{{ $valor('observaciones') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('observaciones')" />
                        <x-danger-button>Anular certificado</x-danger-button>
                    </form>
                </div>
            @endif
