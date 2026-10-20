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
        @unless ($reingreso)
            <div><x-input-label :for="'fecha_'.$clave" value="Fecha de recepción" /><x-date-input :id="'fecha_'.$clave" name="fecha_pago" :value="$valor('fecha_pago', now()->toDateString())" :min="$registro->created_at->toDateString()" :max="now()->toDateString()" required /></div>
            <div><x-input-label :for="'medio_'.$clave" value="Medio de recepción" /><select id="{{ 'medio_'.$clave }}" name="medio_pago" x-model="medio" class="ui-input w-full" required>@foreach (\App\Models\Ingreso::MEDIOS as $medioClave => $etiqueta)<option value="{{ $medioClave }}">{{ $etiqueta }}</option>@endforeach</select></div>
            <div x-show="medio === 'efectivo'"><x-input-label :for="'talonario_'.$clave" value="Número de talonario" /><x-text-input :id="'talonario_'.$clave" name="talonario" :value="$valor('talonario')" maxlength="30" x-bind:disabled="medio !== 'efectivo'" x-bind:required="medio === 'efectivo'" /></div>
            @if ($tipo === 'insumos')
                <div x-show="medio === 'transferencia'"><x-input-label :for="'transaccion_'.$clave" value="Número de transacción (si no adjuntas archivo)" /><x-text-input :id="'transaccion_'.$clave" name="numero_transaccion" :value="$valor('numero_transaccion')" maxlength="50" x-bind:disabled="medio !== 'transferencia'" /></div>
                <div><x-input-label :for="'comprobante_'.$clave" value="Comprobante del total recibido" /><x-file-input :id="'comprobante_'.$clave" name="comprobante" accept="application/pdf,image/jpeg,image/png" /><p class="mt-1 text-xs">PDF, JPG o PNG, máximo 5 MB.</p></div>
            @endif
        @endunless
        <label class="flex items-start gap-2"><input type="checkbox" required> {{ $reingreso ? 'Confirmo el reingreso del aporte anulado, sin repetir la garantía.' : 'Confirmo la recepción del aporte y la garantía indicados.' }}</label>
        <x-primary-button>{{ $reingreso ? 'Registrar nuevamente' : 'Aprobar registro' }}</x-primary-button>
    </form>
</div>
