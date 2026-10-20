<x-app-layout>
    <x-slot name="header"><x-page-heading title="Registro garantías" description="Consulta y resuelve las garantías de sede e insumos en un solo listado." /></x-slot>
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        @include('finanzas._avisos')
        <p class="ui-notice">Las garantías se mantienen separadas de los ingresos. Solo una retención genera ingreso por el monto retenido.</p>
        <section class="ui-panel overflow-x-auto"><table class="ui-table">
            <thead><tr><th>Origen / operación</th><th>Solicitante</th><th>Garantía</th><th>Estado</th><th>Detalle</th></tr></thead>
            @forelse ($garantias as $garantia)
                @php
                    $esInsumo = (bool) $garantia->solicitud_insumo_id;
                    $registro = $esInsumo ? $garantia->solicitudInsumo : $garantia->reserva;
                    $puedeResolver = ! $esInsumo || $registro?->estado === 'devuelto';
                    $minFecha = max($garantia->fecha_pago?->toDateString() ?? '', $esInsumo ? ($registro?->fecha_devolucion?->toDateString() ?? '') : '');
                @endphp
                <x-record-row :id="'garantia_'.$garantia->id" :columns="5" :label="$puedeResolver && ! $garantia->resuelta_en ? 'Gestionar garantía' : 'Ver detalles'">
                    <x-slot name="summary">
                        <td><strong>{{ $esInsumo ? 'Insumos' : 'Sede' }}</strong><span class="block text-xs text-gray-500">{{ $esInsumo ? 'Préstamo' : 'Reserva' }} #{{ $garantia->solicitud_insumo_id ?? $garantia->reserva_id }} · {{ $esInsumo ? (\App\Models\SolicitudInsumo::ESTADOS[$registro?->estado] ?? 'Histórico') : (\App\Models\Reserva::ESTADOS[$registro?->estado] ?? 'Histórico') }}</span></td>
                        <td>{{ $esInsumo ? $registro?->solicitante_nombre : ($registro?->responsable_nombre ?? $registro?->socio?->nombre_completo) }}</td>
                        <td class="font-semibold">${{ number_format($garantia->monto, 0, ',', '.') }}</td>
                        <td><x-status-badge :state="$garantia->estado" :label="\App\Models\Garantia::ESTADOS[$garantia->estado] ?? $garantia->estado" /></td>
                    </x-slot>
                    <dl class="ui-meta">
                        <div><dt>Recepción registrada</dt><dd>{{ $garantia->fecha_pago?->format('d/m/Y') ?? 'Pendiente de registro financiero' }}</dd></div>
                        <div><dt>Medio / referencia</dt><dd>{{ \App\Models\Ingreso::MEDIOS[$garantia->medio_pago] ?? 'Sin registrar' }}@if ($garantia->talonario) · Talonario {{ $garantia->talonario }}@endif</dd></div>
                    </dl>
                    @if ($registro)
                        <a class="ui-link" href="{{ $esInsumo ? route('finanzas.insumos.show', $registro) : route('arriendos.show', ['reserva' => $registro, 'desde' => 'finanzas']) }}">Consultar operación y respaldo</a>
                    @endif
                    @unless ($garantia->registrada_en)<p class="ui-notice">La recepción está pendiente de registro en <a class="underline" href="{{ route('ingresos.index') }}">Ingresos por aprobar</a>.</p>@endunless
                    @unless ($puedeResolver)<p class="ui-notice">La garantía se resuelve después de registrar la devolución completa de los insumos.</p>@endunless
                    @include('finanzas._garantia', ['esFinanciero' => $puedeResolver, 'minFecha' => $minFecha])
                </x-record-row>
            @empty<tbody><tr><td colspan="5">No hay garantías registradas.</td></tr></tbody>@endforelse
        </table></section>
        {{ $garantias->links() }}
    </div>
</x-app-layout>
