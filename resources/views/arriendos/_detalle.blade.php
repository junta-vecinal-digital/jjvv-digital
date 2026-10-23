@php
    $operacion = 'reserva_'.$reserva->id;
    $reintento = old('operacion') === $operacion;
    $valor = fn ($campo, $defecto = null) => $reintento ? old($campo, $defecto) : $defecto;
    $errors = $reintento ? $errors : new \Illuminate\Support\ViewErrorBag;
@endphp
@if ($esDirigente && $reserva->aporte_variable && $reserva->estado === 'pendiente')
    <aside class="ui-notice" aria-label="Siguiente paso">
        <h2 class="font-semibold">Siguiente paso</h2>
        <p class="mt-1">{{ $reserva->valor_arriendo === null ? 'Define el aporte y la garantía acordados en el formulario de abajo.' : (($reserva->valor_arriendo > 0 || $reserva->valor_garantia > 0) && ! $reserva->comprobante_arriendo_path && ! $reserva->numero_transaccion ? 'Falta registrar el número de transacción del aporte.' : 'Revisa los antecedentes y utiliza el formulario de confirmación de más abajo.') }}</p>
    </aside>
@endif
<section class="ui-panel p-6 space-y-4">
    <p>Estado: <x-status-badge :state="$reserva->estado" :label="\App\Models\Reserva::ESTADOS[$reserva->estado] ?? $reserva->estado" /></p>
    <dl class="ui-meta">
        <div><dt>Solicitante</dt><dd>{{ $reserva->responsable_nombre ?? $reserva->socio?->nombre_completo }}</dd></div>
        <div><dt>Actividad</dt><dd>{{ $reserva->motivo }}</dd></div>
        @if ($reserva->responsable_rut)<div><dt>RUT</dt><dd>{{ $reserva->responsable_rut }}</dd></div>@endif
        @if ($reserva->responsable_direccion)<div><dt>Domicilio</dt><dd>{{ $reserva->responsable_direccion }}</dd></div>@endif
        @if ($reserva->responsable_telefono)<div><dt>Teléfono</dt><dd>{{ $reserva->responsable_telefono }}</dd></div>@endif
        @if ($reserva->canal)<div><dt>Canal</dt><dd>{{ $reserva->canal === 'ventanilla' ? 'Ventanilla' : 'En línea' }}</dd></div>@endif
        @if ($reserva->grupo)<div><dt>Agrupación</dt><dd>{{ $reserva->grupo->nombre }}</dd></div>@endif
        <div><dt>Fecha y horario</dt><dd>{{ $reserva->inicio->format('d/m/Y') }} · {{ $reserva->inicio->format('H:i') }} a {{ $reserva->fin->format('H:i') }}</dd></div>
    </dl>
    @if ($reserva->tipo !== 'arriendo')
        <p>Actividad: {{ $reserva->motivo }} · Sin aporte</p>
    @elseif ($reserva->aporte_variable)
        <p class="font-semibold">Aporte: {{ $reserva->valor_arriendo === null ? 'Pendiente de definir por presidente o secretario' : ($reserva->valor_arriendo == 0 ? '$0 · Reserva sin aporte' : '$'.number_format($reserva->valor_arriendo, 0, ',', '.')) }}</p>
    @else
        <p>Arriendo histórico: ${{ number_format($reserva->valor_arriendo, 0, ',', '.') }} · Garantía: ${{ number_format($reserva->valor_garantia, 0, ',', '.') }}</p>
        <p class="font-semibold">Total: ${{ number_format($reserva->valor_arriendo + $reserva->valor_garantia, 0, ',', '.') }}</p>
    @endif
    @if ($reserva->aporte_variable && $reserva->valor_garantia > 0)<p>Garantía acordada: ${{ number_format($reserva->valor_garantia, 0, ',', '.') }}</p>@endif
    @if ($reserva->estado === 'pendiente')
        <p>Plazo para validar: {{ $reserva->vence_en->format('d/m/Y H:i') }}. Sin validación, el horario se libera automáticamente.</p>
    @elseif ($reserva->estado === 'vencida')
        <p>La solicitud venció sin validación. El horario quedó liberado.</p>
    @endif
    @if ($reserva->garantia)
        <p>Garantía: {{ \App\Models\Garantia::ESTADOS[$reserva->garantia->estado] }}</p>
    @endif
    <p>Número de transacción: {{ $reserva->numero_transaccion ?? 'Sin registrar' }}</p>
    @if ($reserva->comprobante_arriendo_path)
        <a class="underline" href="{{ route('arriendos.comprobante', $reserva) }}" target="_blank" rel="noopener">Ver comprobante del total</a>
    @endif
</section>
@if ($esDirigente && $reserva->aporte_variable && $reserva->estado === 'pendiente' && $reserva->valor_arriendo === null)
    <form class="ui-panel p-6 space-y-4" method="POST" action="{{ route('arriendos.aporte', $reserva) }}">
        @csrf
        <input type="hidden" name="operacion" value="{{ $operacion }}">
        <h3 class="text-lg font-semibold">Definir aporte y garantía</h3>
        <p>Ingresa el monto acordado con el solicitante. $0 corresponde a una reserva sin aporte. El monto se fija una sola vez.</p>
        <x-input-label for="valor_arriendo_{{ $reserva->id }}" value="Aporte acordado (CLP)" />
        <x-text-input id="valor_arriendo_{{ $reserva->id }}" name="valor_arriendo" type="number" :value="$valor('valor_arriendo', 0)" min="0" max="4294967295" step="1" required />
        <x-input-error :messages="$errors->get('valor_arriendo')" />
        <x-input-label for="garantia_reserva_{{ $reserva->id }}" value="Garantía acordada (CLP)" />
        <x-text-input id="garantia_reserva_{{ $reserva->id }}" name="garantia_reserva" type="number" :value="$valor('garantia_reserva', 0)" min="0" max="4294967295" step="1" required />
        <x-input-error :messages="$errors->get('garantia_reserva')" />
        <x-primary-button>Definir aporte y garantía</x-primary-button>
    </form>
@endif
@if ($reserva->aporte_variable && $reserva->estado === 'pendiente' && ($reserva->valor_arriendo > 0 || $reserva->valor_garantia > 0) && ! $reserva->comprobante_arriendo_path && ! $reserva->numero_transaccion && ($reserva->canal === 'ventanilla' ? $esDirigente : (auth()->user()->esSocioActivo() && $reserva->socio_id === auth()->user()->socio->id)))
    <form class="ui-panel p-6 space-y-4" method="POST" action="{{ route('arriendos.comprobante.store', $reserva) }}">
        @csrf
        <input type="hidden" name="operacion" value="{{ $operacion }}">
        <x-datos-transferencia :monto="$reserva->valor_arriendo + $reserva->valor_garantia" />
        <x-input-label :for="'transaccion_'.$reserva->id" value="Número de transacción" />
        <x-text-input :id="'transaccion_'.$reserva->id" name="numero_transaccion" maxlength="50" :value="$valor('numero_transaccion')" required />
        <x-input-error :messages="$errors->get('numero_transaccion')" />
        <x-primary-button>Guardar número de transacción</x-primary-button>
    </form>
@endif
@if ($volverFinanzas) @include('finanzas._garantia', ['garantia' => $reserva->garantia, 'esFinanciero' => $esFinanciero]) @endif
@if ($esDirigente && $reserva->tipo === 'arriendo' && $reserva->estado === 'pendiente' && (! $reserva->aporte_variable || ($reserva->valor_arriendo !== null && (($reserva->valor_arriendo == 0 && $reserva->valor_garantia <= 0) || $reserva->comprobante_arriendo_path || $reserva->numero_transaccion))))
    <form class="ui-panel p-6 space-y-4" method="POST" action="{{ route('arriendos.confirmar', $reserva) }}">
        @csrf
        <input type="hidden" name="operacion" value="{{ $operacion }}">
        <h3 class="text-lg font-semibold">Confirmar reserva</h3>
        @if (! $reserva->aporte_variable || $reserva->valor_arriendo > 0)
            <p>Revisa la transacción y verifica el aporte recibido antes de confirmar.</p>
            <label class="flex gap-2"><input type="checkbox" name="pago_verificado" value="1" required @checked($valor('pago_verificado'))> Aporte verificado</label>
            <x-input-error :messages="$errors->get('pago_verificado')" />
        @else
            <p>La reserva puede confirmarse sin aporte.</p>
        @endif
        @if (! $reserva->aporte_variable || $reserva->valor_garantia > 0)
            <label class="flex gap-2"><input type="checkbox" name="garantia_verificada" value="1" required @checked($valor('garantia_verificada'))> Pago de la garantía verificado</label>
            <x-input-error :messages="$errors->get('garantia_verificada')" />
        @endif
        <x-primary-button>Confirmar reserva</x-primary-button>
    </form>
@endif
@if (($esDirigente || (auth()->user()->esSocioActivo() && $reserva->socio_id === auth()->user()->socio->id)) && in_array($reserva->estado, \App\Models\Reserva::ESTADOS_OCUPAN, true))
    <form method="POST" action="{{ route('reservas.cancelar', $reserva) }}" onsubmit="return confirm('¿Cancelar la reserva y liberar el horario? Si hay garantía recibida, quedará por devolver.')">
        @csrf
        <input type="hidden" name="operacion" value="{{ $operacion }}">
        <x-secondary-button type="submit">Cancelar reserva</x-secondary-button>
    </form>
@endif
