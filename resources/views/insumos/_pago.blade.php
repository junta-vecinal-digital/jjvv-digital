@if ($solicitud->estado !== 'pendiente' && $solicitud->requierePago())
<section class="ui-panel p-6 space-y-4">
    <h2 class="font-semibold">Pago del aporte y la garantía</h2>
    <p>Total: ${{ number_format($solicitud->monto + $solicitud->garantia, 0, ',', '.') }} · {{ $solicitud->pago_validado_en ? 'Pago validado' : 'Pendiente de validación' }}</p>
    @if ($solicitud->comprobante_path)
        <a class="ui-link" href="{{ route('insumos.comprobante', $solicitud) }}" target="_blank" rel="noopener">Ver comprobante</a>
    @endif
    @if ($solicitud->pago_validado_en)
        <p class="text-sm">{{ $solicitud->pago_registrado_en ? 'Registrado en Ingresos.' : 'Pendiente de registro contable en Ingresos. Esto no impide la entrega.' }}</p>
    @elseif ($solicitud->estado === 'aprobado')
        <form method="POST" enctype="multipart/form-data" action="{{ route('insumos.pago', $solicitud) }}" class="space-y-3" x-data="{ medio: @js($valor('medio_pago', $solicitud->medio_pago ?? 'transferencia')) }">
            @csrf
            <input type="hidden" name="operacion" value="{{ $operacion }}">
            <div><x-input-label :for="'pago_fecha_'.$solicitud->id" value="Fecha de pago" /><x-date-input :id="'pago_fecha_'.$solicitud->id" name="fecha_pago" :value="$valor('fecha_pago', $solicitud->fecha_pago?->toDateString() ?? today()->toDateString())" :min="$solicitud->created_at->toDateString()" :max="today()->toDateString()" required /></div>
            <div><x-input-label :for="'pago_medio_'.$solicitud->id" value="Medio de pago" /><select id="pago_medio_{{ $solicitud->id }}" name="medio_pago" x-model="medio" class="ui-input" required>@foreach (\App\Models\Ingreso::MEDIOS as $claveMedio => $nombreMedio)<option value="{{ $claveMedio }}">{{ $nombreMedio }}</option>@endforeach</select></div>
            <div x-show="medio === 'efectivo'"><x-input-label :for="'pago_talonario_'.$solicitud->id" value="Número de talonario" /><x-text-input :id="'pago_talonario_'.$solicitud->id" name="talonario" :value="$valor('talonario', $solicitud->talonario)" maxlength="30" x-bind:disabled="medio !== 'efectivo'" x-bind:required="medio === 'efectivo'" /></div>
            <div x-show="medio === 'transferencia'"><x-input-label :for="'pago_transaccion_'.$solicitud->id" value="Número de transacción (opcional)" /><x-text-input :id="'pago_transaccion_'.$solicitud->id" name="numero_transaccion" :value="$valor('numero_transaccion', $solicitud->numero_transaccion)" maxlength="50" x-bind:disabled="medio !== 'transferencia'" /></div>
            <div><x-input-label :for="'pago_archivo_'.$solicitud->id" value="Comprobante obligatorio (PDF, JPG o PNG, máximo 5 MB)" /><x-file-input :id="'pago_archivo_'.$solicitud->id" name="comprobante" accept=".pdf,.jpg,.jpeg,.png" required /></div>
            <x-primary-button>Guardar comprobante</x-primary-button>
        </form>
        @if ($gestion && $solicitud->comprobante_path)
            <form method="POST" action="{{ route('insumos.validar-pago', $solicitud) }}" class="space-y-3">
                @csrf
                <input type="hidden" name="operacion" value="{{ $operacion }}">
                <p class="text-sm">{{ $solicitud->fecha_pago?->format('d/m/Y') }} · {{ \App\Models\Ingreso::MEDIOS[$solicitud->medio_pago] ?? '' }} @if($solicitud->talonario)· Talonario {{ $solicitud->talonario }}@endif @if($solicitud->numero_transaccion)· Transacción {{ $solicitud->numero_transaccion }}@endif</p>
                <label class="flex gap-2"><input type="checkbox" name="pago_verificado" value="1" required> Comprobé la recepción del aporte y la garantía.</label>
                @if ($solicitud->canal === 'ventanilla')
                    <div x-data="{ entregarAhora: @js((bool) $valor('entregar_ahora', false)) }" class="space-y-3">
                        <label class="flex gap-2"><input type="checkbox" name="entregar_ahora" value="1" x-model="entregarAhora"> Registrar también la entrega ahora</label>
                        <fieldset x-show="entregarAhora" :disabled="!entregarAhora" class="space-y-3">
                            <x-input-label :for="'pago_entrega_'.$solicitud->id" value="Fecha de entrega" />
                            <x-date-input :id="'pago_entrega_'.$solicitud->id" name="fecha_entrega" :value="$valor('fecha_entrega', today()->toDateString())" :min="$solicitud->created_at->toDateString()" :max="min(today()->toDateString(), $solicitud->fecha_comprometida->toDateString())" required />
                            @foreach ($solicitud->lineas as $linea)
                                <div><x-input-label :for="'pago_condicion_'.$linea->id" :value="$linea->nombre_insumo.' · Estado de salida'" /><select id="pago_condicion_{{ $linea->id }}" name="condiciones[{{ $linea->id }}]" class="ui-input" required>@foreach (\App\Models\Prestamo::CONDICIONES as $claveEstado => $nombreEstado)<option value="{{ $claveEstado }}" @selected($valor('condiciones.'.$linea->id, 'bueno') === $claveEstado)>{{ $nombreEstado }}</option>@endforeach</select></div>
                            @endforeach
                        </fieldset>
                    </div>
                @endif
                <x-primary-button>Validar pago</x-primary-button>
            </form>
        @endif
    @endif
</section>
@endif
