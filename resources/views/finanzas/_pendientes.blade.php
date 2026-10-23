<details class="ui-panel" open>
    <summary class="cursor-pointer rounded-lg bg-amber-50 px-6 py-4 font-semibold text-amber-900">Ingresos por aprobar</summary>
    <div class="space-y-6 p-4 sm:p-6">
        <p class="text-sm text-gray-500">El aporte se registra como ingreso. La garantía se conserva por separado.</p>
        <div class="overflow-x-auto"><table class="ui-table"><thead><tr><th>Operación / solicitante</th><th>Aporte</th><th>Garantía</th><th>Registro</th></tr></thead>
        @foreach (['sede' => $reservas, 'insumos' => $prestamos] as $tipo => $registros)
                    @forelse ($registros as $registro)
                        <x-record-row :id="$tipo.'_'.$registro->id" :columns="4" label="Revisar recepción">
                            <x-slot name="summary">
                                <td>
                                    <strong>{{ $tipo === 'sede' ? ($registro->responsable_nombre ?? $registro->socio?->nombre_completo) : $registro->solicitante_nombre }}</strong>
                                    <span class="block text-xs text-gray-500">{{ $tipo === 'sede' ? 'Reserva' : 'Préstamo' }} #{{ $registro->id }} · {{ $tipo === 'sede' ? $registro->inicio->format('d/m/Y H:i') : \App\Models\SolicitudInsumo::ESTADOS[$registro->estado] }}</span>
                                    @if ($tipo === 'insumos')<span class="block text-xs text-gray-500">@foreach ($registro->lineas as $linea){{ $linea->nombre_insumo }} × {{ $linea->cantidad }}{{ $loop->last ? '' : ', ' }}@endforeach</span>@endif
                                </td>
                                <td>${{ number_format($tipo === 'sede' ? $registro->valor_arriendo : $registro->monto, 0, ',', '.') }}</td>
                                <td>${{ number_format($tipo === 'sede' ? ($registro->garantia?->monto ?? 0) : $registro->garantia, 0, ',', '.') }}</td>
                            </x-slot>
                            @if ($tipo === 'sede' && $registro->numero_transaccion)<p>Número de transacción: {{ $registro->numero_transaccion }}</p>@endif
                            @if ($tipo === 'sede' && $registro->comprobante_arriendo_path)<a class="ui-link" href="{{ route('arriendos.comprobante', $registro) }}" target="_blank" rel="noopener">Ver comprobante del total</a>@endif
                            @include('finanzas._aprobar-pendiente', ['operacionFormulario' => $tipo.'_'.$registro->id])
                        </x-record-row>
                    @empty @endforelse
        @endforeach
        @foreach ($certificados as $certificado)
            <tbody><tr><td>Certificado {{ $certificado->folio }} · {{ $certificado->nombre_solicitante }}</td><td>${{ number_format($certificado->aporte, 0, ',', '.') }}</td><td>—</td><td>
                @if($certificado->comprobante_path)<a class="ui-link" target="_blank" rel="noopener" href="{{ route('ingresos.certificados.comprobante', $certificado) }}">Ver comprobante</a>@endif
                <form method="POST" action="{{ route('ingresos.certificados.registrar', $certificado) }}">@csrf<x-primary-button>Aprobar ingreso</x-primary-button></form>
            </td></tr></tbody>
        @endforeach
        @if($reservas->isEmpty() && $prestamos->isEmpty() && $certificados->isEmpty())<tbody><tr><td colspan="4">No hay operaciones pendientes de registro financiero.</td></tr></tbody>@endif
        </table></div>
        {{ $reservas->links() }}
        {{ $prestamos->links() }}
        {{ $certificados->links() }}
    </div>
</details>
