        @if ($garantia?->resuelta_en)
            <section class="ui-panel p-6 space-y-3">
                <h3 class="text-lg font-semibold">Resolución de la garantía</h3>
                <p>Fecha: {{ $garantia->fecha_resolucion->format('d/m/Y') }}</p>
                <p>Devuelto: ${{ number_format($garantia->monto_devuelto, 0, ',', '.') }}</p>
                <p>Retenido por daños: ${{ number_format($garantia->monto_retenido, 0, ',', '.') }}</p>
                @if ($garantia->motivo_retencion)<p>{{ $garantia->motivo_retencion }}</p>@endif
                @if ($esFinanciero && $garantia->estado === 'retenida')
                    @foreach ($garantia->ingresos as $ingreso)
                        <p>Ingreso por retención #{{ $ingreso->id }}: {{ $ingreso->estaAnulado() ? 'Anulado' : 'Vigente' }}</p>
                    @endforeach
                    @unless ($garantia->ingresos->contains(fn ($ingreso) => ! $ingreso->estaAnulado()))
                        <p>El ingreso de esta retención está anulado. Puedes volver a registrarlo conservando el monto y la resolución original.</p>
                        <form method="POST" action="{{ route('finanzas.garantias.registrar-retencion', $garantia) }}">
                            @csrf
                            <x-primary-button>Volver a registrar ingreso por retención</x-primary-button>
                        </form>
                    @endunless
                @endif
            </section>
        @endif
        @if ($esFinanciero && $garantia?->registrada_en && in_array($garantia->estado, ['recibida', 'por_devolver'], true))
            <section class="ui-panel p-6 space-y-4">
                <h3 class="text-lg font-semibold">Gestionar garantía</h3>
                <p>La garantía no forma parte de los ingresos. Devolverla no descuenta nuevamente del saldo de caja.</p>
                @if ($garantia->estado === 'recibida')
                    <form method="POST" action="{{ route('finanzas.garantias.por-devolver', $garantia) }}">
                        @csrf
                        <x-secondary-button type="submit">Marcar por devolver</x-secondary-button>
                    </form>
                @endif
                <form method="POST" action="{{ route('finanzas.garantias.devolver', $garantia) }}" class="space-y-3" onsubmit="return confirm('¿Confirmar que ya devolviste el total de la garantía?')">
                    @csrf
                    <x-input-label for="fecha_devolucion" value="Fecha de devolución" />
                    <x-date-input id="fecha_devolucion" name="fecha_resolucion" :value="old('fecha_resolucion', now()->toDateString())" min="{{ $minFecha ?? $garantia->fecha_pago->toDateString() }}" max="{{ now()->toDateString() }}" required />
                    <x-input-error :messages="$errors->get('fecha_resolucion')" />
                    <x-primary-button>Registrar devolución total</x-primary-button>
                </form>
                <details @if ($errors->hasAny(['monto_retenido', 'motivo_retencion'])) open @endif>
                    <summary class="cursor-pointer underline">Retener por daños y devolver el resto</summary>
                    <form method="POST" action="{{ route('finanzas.garantias.retener', $garantia) }}" class="mt-4 space-y-4"
                        x-data="{ retenido: @js(old('monto_retenido', '')), total: {{ $garantia->monto }} }"
                        @submit="if (!confirm('¿Confirmar la retención y que ya devolviste el saldo restante?')) $event.preventDefault()">
                        @csrf
                        <div>
                            <x-input-label for="fecha_retencion" value="Fecha de retención y devolución del saldo" />
                            <x-date-input id="fecha_retencion" name="fecha_resolucion" :value="old('fecha_resolucion', now()->toDateString())" min="{{ $minFecha ?? $garantia->fecha_pago->toDateString() }}" max="{{ now()->toDateString() }}" required />
                            <x-input-error :messages="$errors->get('fecha_resolucion')" />
                        </div>
                        <div>
                            <x-input-label for="monto_retenido" value="Monto retenido por daños (CLP)" />
                            <x-text-input id="monto_retenido" name="monto_retenido" type="number" x-model="retenido" min="1" max="{{ $garantia->monto }}" step="1" required />
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
