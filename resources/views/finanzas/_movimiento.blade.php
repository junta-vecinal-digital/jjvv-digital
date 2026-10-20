@php
    $operacion = ($esEgreso ? 'egreso_' : 'ingreso_').$movimiento->id;
    $reintento = old('operacion') === $operacion;
    $valor = fn ($campo, $defecto = null) => $reintento ? old($campo, $defecto) : $defecto;
@endphp
<h2 class="text-xl font-semibold">{{ ($esEgreso ? \App\Models\Egreso::CONCEPTOS : \App\Models\Ingreso::CONCEPTOS)[$movimiento->concepto] ?? $movimiento->concepto }}</h2>
<dl class="ui-meta">
    @unless ($esEgreso)<div><dt>Medio de pago</dt><dd>{{ \App\Models\Ingreso::MEDIOS[$movimiento->medio_pago] ?? $movimiento->medio_pago }}</dd></div>@endunless
    <div><dt>Referencia</dt><dd>{{ $movimiento->talonario ?? $movimiento->numero_transaccion ?? 'Sin referencia' }}</dd></div>
    <div><dt>Registrado por</dt><dd>{{ $movimiento->registrador?->name ?? 'Sin cuenta vinculada' }}</dd></div>
    @if ($esEgreso)<div><dt>Quién pagó</dt><dd>{{ $movimiento->pagado_por }}</dd></div>
    @elseif ($movimiento->tipo_cuota)<div><dt>Clasificación</dt><dd>{{ \App\Models\Ingreso::TIPOS_CUOTA[$movimiento->tipo_cuota] ?? $movimiento->tipo_cuota }}</dd></div>@endif
    @if ($movimiento->observaciones)<div class="sm:col-span-2"><dt>Observaciones</dt><dd>{{ $movimiento->observaciones }}</dd></div>@endif
</dl>
<div class="flex flex-wrap gap-4">
    @if ($esEgreso || $movimiento->comprobante_path)<a class="ui-link" href="{{ route($esEgreso ? 'egresos.comprobante' : 'ingresos.comprobante', $movimiento) }}" target="_blank" rel="noopener">Ver comprobante</a>@endif
    @unless ($esEgreso)
        @php($solicitudId = $movimiento->solicitud_insumo_id ?? $movimiento->garantia?->solicitud_insumo_id)
        @if ($solicitudId)<a class="ui-link" href="{{ route('finanzas.insumos.show', $solicitudId) }}">Préstamo y respaldo original</a>
        @elseif ($movimiento->reserva_id || $movimiento->garantia?->reserva_id)<a class="ui-link" href="{{ route('arriendos.show', ['reserva' => $movimiento->reserva_id ?? $movimiento->garantia->reserva_id, 'desde' => 'finanzas']) }}">Reserva y respaldo original</a>@endif
    @endunless
</div>
@if ($movimiento->estaAnulado())
    <p class="ui-notice">Anulado el {{ $movimiento->anulado_en->format('d/m/Y') }}: {{ $movimiento->motivo_anulacion }}</p>
@endif
@if (! $esEgreso && $movimiento->devolucion)
    <p class="ui-notice">Devuelto el {{ $movimiento->devolucion->fecha_devolucion->format('d/m/Y') }}: {{ $movimiento->devolucion->motivo }}</p>
@endif
@if (! $movimiento->estaAnulado() && ($esEgreso || ! $movimiento->devolucion))
    <details @if ($reintento && $errors->hasAny(['motivo_anulacion', 'ingreso', 'egreso'])) open @endif>
        <summary class="text-red-700">Anular {{ $esEgreso ? 'egreso' : 'ingreso' }}</summary>
        <form method="POST" action="{{ route($esEgreso ? 'egresos.anular' : 'ingresos.anular', $movimiento) }}">
            @csrf
            <input type="hidden" name="operacion" value="{{ $operacion }}">
            <x-input-label :for="'motivo_'.$operacion" value="Motivo obligatorio" />
            <textarea id="motivo_{{ $operacion }}" name="motivo_anulacion" rows="3" required maxlength="500" class="ui-input w-full">{{ $valor('motivo_anulacion') }}</textarea>
            <x-danger-button>Confirmar anulación</x-danger-button>
        </form>
    </details>
@endif
@if (! $esEgreso && $movimiento->puedeDevolverse())
    <details @if ($reintento && $errors->hasAny(['motivo', 'devolucion'])) open @endif>
        <summary>Devolver cuota</summary>
        <form method="POST" action="{{ route('ingresos.devolver', $movimiento) }}">
            @csrf
            <input type="hidden" name="operacion" value="{{ $operacion }}">
            <x-input-label :for="'devolucion_'.$operacion" value="Motivo obligatorio" />
            <textarea id="devolucion_{{ $operacion }}" name="motivo" rows="3" required maxlength="500" class="ui-input w-full">{{ $valor('motivo') }}</textarea>
            <x-secondary-button type="submit">Confirmar devolución</x-secondary-button>
        </form>
    </details>
@endif
@if (! $esEgreso && $movimiento->estaAnulado())
    @if ($movimiento->solicitudInsumo && $movimiento->solicitudInsumo->pago_registrado_en && $movimiento->solicitudInsumo->monto > 0 && ! $movimiento->solicitudInsumo->ingresos->contains(fn ($i) => ! $i->estaAnulado()))
        @include('finanzas._aprobar-pendiente', ['tipo' => 'insumos', 'registro' => $movimiento->solicitudInsumo, 'operacionFormulario' => $operacion])
    @elseif ($movimiento->reserva && $movimiento->reserva->validado_en && $movimiento->reserva->valor_arriendo > 0 && ! $movimiento->reserva->ingresos->contains(fn ($i) => ! $i->estaAnulado()))
        <h3 class="font-semibold">Reingresar aporte anulado</h3>
        <p class="text-sm text-gray-500">Se conservan los datos del aporte original y la garantía recibida.</p>
        <form method="POST" action="{{ route('finanzas.arriendos.registrar', $movimiento->reserva) }}">
            @csrf
            <input type="hidden" name="operacion" value="{{ $operacion }}">
            <input type="hidden" name="fecha_pago" value="{{ $movimiento->fecha_pago->toDateString() }}">
            <input type="hidden" name="medio_pago" value="{{ $movimiento->medio_pago }}">
            <input type="hidden" name="talonario" value="{{ $movimiento->talonario }}">
            <label class="flex items-start gap-2"><input type="checkbox" required> Confirmo el reingreso del aporte anulado sin repetir la garantía.</label>
            <x-primary-button>Reingresar aporte anulado</x-primary-button>
        </form>
    @elseif ($movimiento->garantia && $movimiento->garantia->estado === 'retenida' && ! $movimiento->garantia->ingresos->contains(fn ($i) => ! $i->estaAnulado()))
        <form method="POST" action="{{ route('finanzas.garantias.registrar-retencion', $movimiento->garantia) }}">
            @csrf
            <input type="hidden" name="operacion" value="{{ $operacion }}">
            <p>Se conserva la resolución original de la garantía.</p>
            <x-primary-button>Reingresar ingreso por retención</x-primary-button>
        </form>
    @endif
@endif
