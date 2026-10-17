<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold">Arriendo #{{ $reserva->id }}</h2></x-slot>
    <div class="max-w-4xl mx-auto px-4 py-6 space-y-6">
        <a class="underline" href="{{ route($esFinanciero ? 'finanzas.arriendos.index' : 'arriendos.index') }}">Volver a arriendos</a>
        @if (session('status'))
            <p class="ui-panel p-4">{{ match(session('status')) { 'arriendo-solicitado' => 'Solicitud enviada. El horario quedó reservado mientras se revisa el pago.', 'arriendo-confirmado' => 'Arriendo confirmado y garantía registrada como recibida.', 'reserva-cancelada' => 'Reserva cancelada y horario liberado.', 'arriendo-registrado' => 'Pago del arriendo registrado en ingresos y garantía registrada por separado.', 'garantia-por-devolver' => 'Garantía marcada como por devolver.', 'garantia-resuelta' => 'Resolución de garantía registrada.', 'retencion-registrada' => 'Ingreso por retención registrado nuevamente.', default => '' } }}</p>
        @endif
        <x-input-error :messages="$errors->get('arriendo')" />
        <x-input-error :messages="$errors->get('reserva')" />
        <x-input-error :messages="$errors->get('finanzas')" />
        <section class="ui-panel p-6 space-y-4">
            <p>Estado: <strong>{{ \App\Models\Reserva::ESTADOS[$reserva->estado] ?? $reserva->estado }}</strong></p>
            <p>Solicitante: {{ $reserva->socio?->nombre_completo }}</p>
            @if ($reserva->grupo)<p>Grupo: {{ $reserva->grupo->nombre }}</p>@endif
            <p>{{ $reserva->inicio->format('d-m-Y') }}, {{ $reserva->inicio->format('H:i') }} a {{ $reserva->fin->format('H:i') }}</p>
            <p>Arriendo: ${{ number_format($reserva->valor_arriendo, 0, ',', '.') }} · Garantía: ${{ number_format($reserva->valor_garantia, 0, ',', '.') }}</p>
            <p class="font-semibold">Total: ${{ number_format($reserva->valor_arriendo + $reserva->valor_garantia, 0, ',', '.') }}</p>
            @if ($reserva->estado === 'pendiente')
                <p>Plazo para validar: {{ $reserva->vence_en->format('d-m-Y H:i') }}. Sin validación, el horario se libera automáticamente.</p>
            @elseif ($reserva->estado === 'vencida')
                <p>La solicitud venció sin validación. El horario quedó liberado.</p>
            @endif
            @if ($reserva->garantia)
                <p>Garantía: {{ \App\Models\Garantia::ESTADOS[$reserva->garantia->estado] }}</p>
            @endif
            <a class="underline" href="{{ route('arriendos.comprobante', $reserva) }}" target="_blank" rel="noopener">Ver comprobante del total</a>
        </section>
        @if ($esFinanciero && $reserva->validado_en)
            <section class="ui-panel p-6 space-y-4">
                <h3 class="text-lg font-semibold">Registro financiero</h3>
                @if ($reserva->ingresos->contains(fn ($ingreso) => ! $ingreso->estaAnulado()))
                    <p>El pago del arriendo ya tiene un ingreso vigente.</p>
                @else
                    <p>Registrar ${{ number_format($reserva->valor_arriendo, 0, ',', '.') }} como ingreso y ${{ number_format($reserva->valor_garantia, 0, ',', '.') }} como garantía separada. Se reutiliza el comprobante del total.</p>
                    <form method="POST" action="{{ route('finanzas.arriendos.registrar', $reserva) }}" class="space-y-4" x-data="{ medio: @js(old('medio_pago', 'transferencia')) }">
                        @csrf
                        <div>
                            <x-input-label for="fecha_pago" value="Fecha del pago recibido" />
                            <x-text-input id="fecha_pago" name="fecha_pago" type="date" :value="old('fecha_pago', $reserva->validado_en->toDateString())" max="{{ now()->toDateString() }}" required />
                            <x-input-error :messages="$errors->get('fecha_pago')" />
                        </div>
                        <div>
                            <x-input-label for="medio_pago" value="Medio de pago" />
                            <select id="medio_pago" name="medio_pago" x-model="medio" class="ui-input w-full" required>
                                @foreach (\App\Models\Ingreso::MEDIOS as $valor => $etiqueta)<option value="{{ $valor }}">{{ $etiqueta }}</option>@endforeach
                            </select>
                            <x-input-error :messages="$errors->get('medio_pago')" />
                        </div>
                        <div x-show="medio === 'efectivo'" x-cloak>
                            <x-input-label for="talonario" value="Número de talonario" />
                            <x-text-input id="talonario" name="talonario" :value="old('talonario')" maxlength="30" x-bind:disabled="medio !== 'efectivo'" x-bind:required="medio === 'efectivo'" />
                            <x-input-error :messages="$errors->get('talonario')" />
                        </div>
                        <x-primary-button>Registrar pago y garantía</x-primary-button>
                    </form>
                @endif
                @foreach ($reserva->ingresos as $ingreso)
                    <p>Ingreso #{{ $ingreso->id }}: ${{ number_format($ingreso->monto, 0, ',', '.') }} · {{ $ingreso->estaAnulado() ? 'Anulado' : 'Vigente' }}</p>
                @endforeach
            </section>
        @endif
        @if ($reserva->garantia?->resuelta_en)
            <section class="ui-panel p-6 space-y-3">
                <h3 class="text-lg font-semibold">Resolución de la garantía</h3>
                <p>Fecha: {{ $reserva->garantia->fecha_resolucion->format('d-m-Y') }}</p>
                <p>Devuelto: ${{ number_format($reserva->garantia->monto_devuelto, 0, ',', '.') }}</p>
                <p>Retenido por daños: ${{ number_format($reserva->garantia->monto_retenido, 0, ',', '.') }}</p>
                @if ($reserva->garantia->motivo_retencion)<p>{{ $reserva->garantia->motivo_retencion }}</p>@endif
                @if ($esFinanciero && $reserva->garantia->estado === 'retenida')
                    @foreach ($reserva->garantia->ingresos as $ingreso)
                        <p>Ingreso por retención #{{ $ingreso->id }}: {{ $ingreso->estaAnulado() ? 'Anulado' : 'Vigente' }}</p>
                    @endforeach
                    @unless ($reserva->garantia->ingresos->contains(fn ($ingreso) => ! $ingreso->estaAnulado()))
                        <p>El ingreso de esta retención está anulado. Puedes volver a registrarlo conservando el monto y la resolución original.</p>
                        <form method="POST" action="{{ route('finanzas.garantias.registrar-retencion', $reserva->garantia) }}">
                            @csrf
                            <x-primary-button>Volver a registrar ingreso por retención</x-primary-button>
                        </form>
                    @endunless
                @endif
            </section>
        @endif
        @if ($esFinanciero && $reserva->garantia?->registrada_en && in_array($reserva->garantia->estado, ['recibida', 'por_devolver'], true))
            <section class="ui-panel p-6 space-y-4">
                <h3 class="text-lg font-semibold">Gestionar garantía</h3>
                <p>La garantía no forma parte de los ingresos. Devolverla no descuenta nuevamente del saldo de caja.</p>
                @if ($reserva->garantia->estado === 'recibida')
                    <form method="POST" action="{{ route('finanzas.garantias.por-devolver', $reserva->garantia) }}">
                        @csrf
                        <x-secondary-button type="submit">Marcar por devolver</x-secondary-button>
                    </form>
                @endif
                <form method="POST" action="{{ route('finanzas.garantias.devolver', $reserva->garantia) }}" class="space-y-3" onsubmit="return confirm('¿Confirmar que ya devolviste el total de la garantía?')">
                    @csrf
                    <x-input-label for="fecha_devolucion" value="Fecha de devolución" />
                    <x-text-input id="fecha_devolucion" name="fecha_resolucion" type="date" :value="old('fecha_resolucion', now()->toDateString())" min="{{ $reserva->garantia->fecha_pago->toDateString() }}" max="{{ now()->toDateString() }}" required />
                    <x-input-error :messages="$errors->get('fecha_resolucion')" />
                    <x-primary-button>Registrar devolución total</x-primary-button>
                </form>
                <details @if ($errors->hasAny(['monto_retenido', 'motivo_retencion'])) open @endif>
                    <summary class="cursor-pointer underline">Retener por daños y devolver el resto</summary>
                    <form method="POST" action="{{ route('finanzas.garantias.retener', $reserva->garantia) }}" class="mt-4 space-y-4"
                        x-data="{ retenido: @js(old('monto_retenido', '')), total: {{ $reserva->garantia->monto }} }"
                        @submit="if (!confirm('¿Confirmar la retención y que ya devolviste el saldo restante?')) $event.preventDefault()">
                        @csrf
                        <div>
                            <x-input-label for="fecha_retencion" value="Fecha de retención y devolución del saldo" />
                            <x-text-input id="fecha_retencion" name="fecha_resolucion" type="date" :value="old('fecha_resolucion', now()->toDateString())" min="{{ $reserva->garantia->fecha_pago->toDateString() }}" max="{{ now()->toDateString() }}" required />
                            <x-input-error :messages="$errors->get('fecha_resolucion')" />
                        </div>
                        <div>
                            <x-input-label for="monto_retenido" value="Monto retenido por daños (CLP)" />
                            <x-text-input id="monto_retenido" name="monto_retenido" type="number" x-model="retenido" min="1" max="{{ $reserva->garantia->monto }}" step="1" required />
                            <x-input-error :messages="$errors->get('monto_retenido')" />
                        </div>
                        <p>Saldo a devolver: <strong x-text="'$' + Math.max(0, total - Number(retenido || 0)).toLocaleString('es-CL')"></strong></p>
                        <div>
                            <x-input-label for="motivo_retencion" value="Motivo de la retención" />
                            <textarea id="motivo_retencion" name="motivo_retencion" maxlength="500" rows="3" class="ui-input w-full" required>{{ old('motivo_retencion') }}</textarea>
                            <x-input-error :messages="$errors->get('motivo_retencion')" />
                        </div>
                        <p>Solo el monto retenido se sumará a ingresos. Confirma cuando hayas devuelto el saldo restante; si retienes toda la garantía, el saldo será $0.</p>
                        <x-primary-button>Registrar retención y devolución del saldo</x-primary-button>
                    </form>
                </details>
            </section>
        @endif
        @if ($esDirigente && $reserva->estado === 'pendiente')
            <form class="ui-panel p-6 space-y-4" method="POST" action="{{ route('arriendos.confirmar', $reserva) }}">
                @csrf
                <h3 class="text-lg font-semibold">Validar recepción del pago</h3>
                <p>Revisa el comprobante y verifica ambos montos antes de confirmar.</p>
                <label class="flex gap-2"><input type="checkbox" name="pago_verificado" value="1" required @checked(old('pago_verificado'))> Pago del arriendo verificado</label>
                <x-input-error :messages="$errors->get('pago_verificado')" />
                <label class="flex gap-2"><input type="checkbox" name="garantia_verificada" value="1" required @checked(old('garantia_verificada'))> Pago de la garantía verificado</label>
                <x-input-error :messages="$errors->get('garantia_verificada')" />
                <x-primary-button>Confirmar arriendo</x-primary-button>
            </form>
        @endif
        @if ($esDirigente && in_array($reserva->estado, \App\Models\Reserva::ESTADOS_OCUPAN, true))
            <form method="POST" action="{{ route('reservas.cancelar', $reserva) }}" onsubmit="return confirm('¿Cancelar la reserva y liberar el horario? Si hay garantía recibida, quedará por devolver.')">
                @csrf
                <x-secondary-button type="submit">Cancelar reserva</x-secondary-button>
            </form>
        @endif
    </div>
</x-app-layout>
