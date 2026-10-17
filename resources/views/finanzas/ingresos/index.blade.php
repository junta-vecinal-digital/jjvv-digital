<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white leading-tight">Ingresos</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            @if (session('status') === 'ingreso-anulado')
                <p class="text-green-700 dark:text-green-400">Ingreso anulado.</p>
            @endif
            @error('ingreso')
                <p class="text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror

            @if (session('status') === 'devolucion-registrada')
                <p class="text-green-700 dark:text-green-400">Devolución registrada.</p>
            @endif
            @error('devolucion')
                <p class="text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror

            <div class="flex flex-wrap justify-between items-center gap-3">
                <p class="text-gray-700 dark:text-gray-300">Saldo de caja: <strong>${{ number_format($saldo, 0, ',', '.') }}</strong></p>
                <a href="{{ route('ingresos.create') }}" class="ui-button ui-button-primary">Registrar ingreso</a>
            </div>

            <div class="ui-panel p-6 overflow-x-auto">
                <table class="ui-table">
                    <thead>
                        <tr class="text-left border-b">
                            <th class="py-2">Fecha</th>
                            <th>Concepto</th>
                            <th>Monto</th>
                            <th>Medio</th>
                            <th>Comprobante</th>
                            <th>Registrado por</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ingresos as $ingreso)
                            <tr class="border-b align-top">
                                <td class="py-2">{{ $ingreso->fecha_pago->format('d/m/Y') }}</td>
                                <td>
                                    {{ \App\Models\Ingreso::CONCEPTOS[$ingreso->concepto] ?? $ingreso->concepto }}
                                    @if ($ingreso->tipo_cuota)
                                        <span class="text-xs text-gray-500">({{ \App\Models\Ingreso::TIPOS_CUOTA[$ingreso->tipo_cuota] }})</span>
                                    @endif
                                    @if ($ingreso->estaAnulado())
                                        <div class="text-xs text-red-600 dark:text-red-400">
                                            Anulado el {{ $ingreso->anulado_en->format('d/m/Y') }}: {{ $ingreso->motivo_anulacion }}
                                        </div>
                                    @endif
                                    @if ($ingreso->devolucion)
                                        <div class="text-xs text-amber-700 dark:text-amber-400">
                                            Devuelto el {{ $ingreso->devolucion->fecha_devolucion->format('d/m/Y') }}: {{ $ingreso->devolucion->motivo }}
                                        </div>
                                    @endif
                                </td>
                                <td>${{ number_format($ingreso->monto, 0, ',', '.') }}</td>
                                <td>{{ \App\Models\Ingreso::MEDIOS[$ingreso->medio_pago] }}</td>
                                <td>
                                    {{ $ingreso->talonario ?? $ingreso->numero_transaccion }}
                                    @php($solicitudInsumoId = $ingreso->solicitud_insumo_id ?? $ingreso->garantia?->solicitud_insumo_id)
                                    @if ($solicitudInsumoId)
                                        <a class="underline" href="{{ route('finanzas.insumos.show', $solicitudInsumoId) }}">Préstamo y comprobante</a>
                                    @elseif ($ingreso->reserva_id || $ingreso->garantia)
                                        <a class="underline" href="{{ route('arriendos.show', ['reserva' => $ingreso->reserva_id ?? $ingreso->garantia->reserva_id, 'desde' => 'finanzas']) }}">Reserva y comprobante</a>
                                    @endif
                                </td>
                                <td>{{ $ingreso->registrador?->name ?? '—' }}</td>
                                <td>
                                    @unless ($ingreso->estaAnulado() || $ingreso->devolucion)
                                        <details>
                                            <summary class="cursor-pointer underline text-red-600 dark:text-red-400">Anular</summary>
                                            <form method="POST" action="{{ route('ingresos.anular', $ingreso) }}" class="mt-2 space-y-2">
                                                @csrf
                                                <textarea name="motivo_anulacion" rows="2" required placeholder="Motivo obligatorio"
                                                          class="block w-full rounded-md border-gray-300 text-sm"></textarea>
                                                <button type="submit" class="px-3 py-1 rounded-md bg-red-600 text-white text-xs">
                                                    Confirmar anulación
                                                </button>
                                            </form>
                                        </details>
                                    @endunless

                                    @if ($ingreso->puedeDevolverse())
                                        <details class="mt-1">
                                            <summary class="cursor-pointer underline text-amber-700 dark:text-amber-400">Devolver</summary>
                                            <form method="POST" action="{{ route('ingresos.devolver', $ingreso) }}" class="mt-2 space-y-2">
                                                @csrf
                                                <textarea name="motivo" rows="2" required placeholder="Motivo obligatorio"
                                                          class="block w-full rounded-md border-gray-300 text-sm"></textarea>
                                                <button type="submit" class="px-3 py-1 rounded-md bg-amber-600 text-white text-xs">
                                                    Confirmar devolución
                                                </button>
                                            </form>
                                        </details>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="py-4 text-center text-gray-500">Sin ingresos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $ingresos->links() }}
        </div>
    </div>
</x-app-layout>
