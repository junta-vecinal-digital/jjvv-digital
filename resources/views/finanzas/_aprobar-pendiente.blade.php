@php
    $clave = $operacionFormulario ?? $tipo.'_'.$registro->id;
    $reintento = old('operacion') === $clave;
    $valor = fn ($campo, $defecto = null) => $reintento ? old($campo, $defecto) : $defecto;
    $reingreso = $tipo === 'insumos' && $registro->pago_registrado_en;
    $ruta = $tipo === 'sede' ? 'finanzas.arriendos.registrar' : ($reingreso ? 'finanzas.insumos.reingresar' : 'finanzas.insumos.registrar');
@endphp
<div>
    <h3 class="text-lg font-semibold">{{ $reingreso ? 'Volver a registrar aporte' : 'Aprobar registro' }}</h3>
    <form method="POST" enctype="multipart/form-data" action="{{ route($ruta, $registro) }}" class="mt-4 space-y-3" x-data="{ medio: @js($valor('medio_pago', 'transferencia')) }">
        @csrf
        <input type="hidden" name="operacion" value="{{ $clave }}">
        @if (! $reingreso && $tipo === 'sede')
            <div><x-input-label :for="'fecha_'.$clave" value="Fecha de recepción" /><x-date-input :id="'fecha_'.$clave" name="fecha_pago" :value="$valor('fecha_pago', now()->toDateString())" :min="$registro->created_at->toDateString()" :max="now()->toDateString()" required /></div>
            <div><x-input-label :for="'medio_'.$clave" value="Medio de recepción" /><select id="{{ 'medio_'.$clave }}" name="medio_pago" x-model="medio" class="ui-input w-full" required>@foreach (\App\Models\Ingreso::MEDIOS as $medioClave => $etiqueta)<option value="{{ $medioClave }}">{{ $etiqueta }}</option>@endforeach</select></div>
            <div x-show="medio === 'efectivo'"><x-input-label :for="'talonario_'.$clave" value="Número de talonario" /><x-text-input :id="'talonario_'.$clave" name="talonario" :value="$valor('talonario')" maxlength="30" x-bind:disabled="medio !== 'efectivo'" x-bind:required="medio === 'efectivo'" /></div>
        @endif
        @if ($tipo === 'insumos')
            <p>Pago validado el {{ $registro->pago_validado_en?->format('d/m/Y H:i') }} · {{ $registro->fecha_pago?->format('d/m/Y') }} · {{ \App\Models\Ingreso::MEDIOS[$registro->medio_pago] ?? '' }}</p>
            @if ($registro->comprobante_path)<a class="ui-link" href="{{ route('insumos.comprobante', $registro) }}" target="_blank" rel="noopener">Ver comprobante</a>@endif
        @endif
        <label class="flex items-start gap-2"><input type="checkbox" required> {{ $reingreso ? 'Confirmo el reingreso del aporte anulado, sin repetir la garantía.' : 'Confirmo la recepción del aporte y la garantía indicados.' }}</label>
        <x-primary-button>{{ $reingreso ? 'Registrar nuevamente' : 'Aprobar registro' }}</x-primary-button>
    </form>
</div>
