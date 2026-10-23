@php
    $operacion = 'insumo_'.$solicitud->id;
    $reintento = old('operacion') === $operacion;
    $valor = fn ($campo, $defecto = null) => $reintento ? old($campo, $defecto) : $defecto;
    $errors = $reintento ? $errors : new \Illuminate\Support\ViewErrorBag;
@endphp
@if ($gestion && in_array($solicitud->estado, ['pendiente', 'aprobado', 'entregado']))
    <aside class="ui-notice" aria-label="Siguiente paso">
        <h2 class="font-semibold">Siguiente paso</h2>
        <p class="mt-1">{{ match ($solicitud->estado) { 'pendiente' => 'Revisa los datos y las unidades. Más abajo puedes definir las condiciones y aprobar el préstamo.', 'aprobado' => $solicitud->puedeEntregarse() ? 'Las unidades están reservadas. Registra su entrega cuando el solicitante las retire.' : 'Las unidades están reservadas. Adjunta y valida el comprobante antes de registrar la entrega.', 'entregado' => 'Al recibir todos los insumos, registra la devolución y las unidades dañadas, si corresponde.' } }}</p>
    </aside>
@endif
<section class="ui-panel p-6 space-y-3">
    <p>Estado: <x-status-badge :state="$solicitud->estado" :label="\App\Models\SolicitudInsumo::ESTADOS[$solicitud->estado]" />@if ($solicitud->atrasado()) <strong class="text-red-600">· Atrasado</strong>@endif</p>
    <dl class="ui-meta">
        <div><dt>Solicitante / canal</dt><dd>{{ $solicitud->solicitante_nombre }} · {{ $solicitud->canal === 'ventanilla' ? 'Ventanilla' : 'En línea' }}</dd></div>
        <div><dt>Agrupación</dt><dd>{{ $solicitud->grupo_nombre ?? 'Particular' }}</dd></div>
        @if ($solicitud->solicitante_rut)<div><dt>RUT</dt><dd>{{ $solicitud->solicitante_rut }}</dd></div>@endif
        <div><dt>Contacto</dt><dd>{{ $solicitud->telefono_contacto }}@if ($solicitud->correo_contacto) · {{ $solicitud->correo_contacto }}@endif</dd></div>
        @if ($solicitud->direccion_contacto)<div><dt>Dirección</dt><dd>{{ $solicitud->direccion_contacto }}</dd></div>@endif
        <div><dt>Fecha de solicitud</dt><dd>{{ $solicitud->created_at->format('d/m/Y') }}</dd></div>
        <div><dt>Devolución comprometida</dt><dd>{{ $solicitud->fecha_comprometida->format('d/m/Y') }}</dd></div>
        @if ($solicitud->responsable_nombre)<div><dt>Dirigente responsable</dt><dd>{{ $solicitud->responsable_nombre }}@if ($solicitud->responsable_cargo) · {{ \App\Models\User::ROLES[$solicitud->responsable_cargo] ?? $solicitud->responsable_cargo }}@endif</dd></div>@endif
        @if ($solicitud->monto !== null)
            <div><dt>Aporte del préstamo</dt><dd>${{ number_format($solicitud->monto, 0, ',', '.') }}</dd></div>
            <div><dt>Garantía acordada</dt><dd>${{ number_format($solicitud->garantia, 0, ',', '.') }}</dd></div>
        @endif
        @if ($solicitud->fecha_entrega)<div><dt>Entrega</dt><dd>{{ $solicitud->fecha_entrega->format('d/m/Y') }}</dd></div>@endif
        @if ($solicitud->fecha_devolucion)<div><dt>Devolución completa</dt><dd>{{ $solicitud->fecha_devolucion->format('d/m/Y') }}</dd></div>@endif
    </dl>
    <div class="overflow-x-auto"><table class="ui-table"><thead><tr><th>Insumo</th><th>Unidades</th><th>Salida</th><th>Regreso</th><th>Dañadas</th></tr></thead><tbody>
        @foreach ($solicitud->lineas as $linea)
            <tr><td>{{ $linea->nombre_insumo }}</td><td>{{ $linea->cantidad }}</td><td>{{ \App\Models\Prestamo::CONDICIONES[$linea->prestamo?->estado_salida] ?? 'Por entregar' }}</td><td>{{ \App\Models\Prestamo::CONDICIONES[$linea->prestamo?->estado_regreso] ?? 'Por devolver' }}</td><td>{{ $linea->prestamo?->cantidad_danada ?? 0 }}</td></tr>
        @endforeach
    </tbody></table></div>
</section>
@if (! auth()->user()->hasRole('presidente', 'secretario') && in_array($solicitud->estado, ['pendiente', 'aprobado'])) @include('insumos._horarios') @endif
@if ($gestion && $solicitud->estado === 'pendiente')
    <section class="ui-panel p-6">
        <h2 class="font-semibold mb-4">Aprobar y reservar todas las unidades</h2>
        
            <form method="POST" action="{{ route('insumos.aprobar', $solicitud) }}" class="space-y-4">
                @csrf
        <input type="hidden" name="operacion" value="{{ $operacion }}">
                <div class="ui-form-grid">
                    <div class="grid gap-4 sm:grid-cols-2 mt-4">
                        <div><x-input-label for="monto_{{ $solicitud->id }}" value="Aporte del préstamo (CLP)" /><x-text-input id="monto_{{ $solicitud->id }}" name="monto" type="number" min="0" max="4294967295" step="1" :value="$valor('monto', 0)" required class="w-full" /></div>
                        <div><x-input-label for="garantia_{{ $solicitud->id }}" value="Garantía total acordada (CLP)" /><x-text-input id="garantia_{{ $solicitud->id }}" name="garantia" type="number" min="0" max="4294967295" step="1" :value="$valor('garantia', 0)" required class="w-full" /></div>
                    </div>
                </div>
                <div class="ui-form-actions"><span></span><x-primary-button>Aprobar y reservar todas las unidades</x-primary-button></div>
            </form>
        
    </section>
@endif
@include('insumos._pago')
@if ($gestion && $solicitud->puedeEntregarse())
    <section class="ui-panel p-6">
        <h2 class="font-semibold mb-4">Registrar entrega completa</h2>
        
            <form method="POST" action="{{ route('insumos.entregar', $solicitud) }}" class="space-y-4">
                @csrf
        <input type="hidden" name="operacion" value="{{ $operacion }}">
                <div class="ui-form-grid">
                    <x-input-label for="fecha_entrega_{{ $solicitud->id }}" value="Fecha de entrega" /><x-date-input id="fecha_entrega_{{ $solicitud->id }}" name="fecha_entrega" :value="$valor('fecha_entrega', now()->toDateString())" :min="$solicitud->created_at->toDateString()" :max="min(now()->toDateString(), $solicitud->fecha_comprometida->toDateString())" required />
                    @foreach ($solicitud->lineas as $linea)
                        <div class="mt-4"><x-input-label :for="'condicion_'.$linea->id" :value="$linea->nombre_insumo.' · '.$linea->cantidad.' unidades: estado de salida'" /><select id="condicion_{{ $linea->id }}" name="condiciones[{{ $linea->id }}]" class="ui-input w-full" required>@foreach (\App\Models\Prestamo::CONDICIONES as $clave => $etiqueta)<option value="{{ $clave }}" @selected($valor('condiciones.'.$linea->id, 'bueno') === $clave)>{{ $etiqueta }}</option>@endforeach</select></div>
                    @endforeach
                </div>
                <div class="ui-form-actions"><span></span><x-primary-button>Registrar entrega completa</x-primary-button></div>
            </form>
        
    </section>
@endif
@if ($gestion && $solicitud->estado === 'entregado')
    <section class="ui-panel p-6">
        <h2 class="font-semibold mb-4">Registrar devolución completa</h2>
        
            <form method="POST" action="{{ route('insumos.devolver', $solicitud) }}" class="space-y-4">
                @csrf
        <input type="hidden" name="operacion" value="{{ $operacion }}">
                <div class="ui-form-grid">
                    <x-input-label for="fecha_devolucion_{{ $solicitud->id }}" value="Fecha efectiva de devolución" /><x-date-input id="fecha_devolucion_{{ $solicitud->id }}" name="fecha_devolucion" :value="$valor('fecha_devolucion', now()->toDateString())" :min="$solicitud->fecha_entrega->toDateString()" :max="now()->toDateString()" required />
                    @foreach ($solicitud->lineas as $linea)
                        <div class="mt-4 grid gap-4 sm:grid-cols-2" x-data="{ condicion: @js($valor('devoluciones.'.$linea->id.'.estado_regreso', 'bueno')) }">
                            <div><x-input-label :for="'regreso_'.$linea->id" :value="$linea->nombre_insumo.' · '.$linea->cantidad.' unidades: regreso'" /><select id="regreso_{{ $linea->id }}" name="devoluciones[{{ $linea->id }}][estado_regreso]" x-model="condicion" class="ui-input w-full" required>@foreach (\App\Models\Prestamo::CONDICIONES as $clave => $etiqueta)<option value="{{ $clave }}">{{ $etiqueta }}</option>@endforeach</select></div>
                            <div x-show="condicion === 'danado'" x-cloak><x-input-label :for="'danadas_'.$linea->id" value="Unidades dañadas fuera de servicio" /><x-text-input :id="'danadas_'.$linea->id" name="devoluciones[{{ $linea->id }}][cantidad_danada]" type="number" min="1" :max="$linea->cantidad" step="1" :value="$valor('devoluciones.'.$linea->id.'.cantidad_danada', 1)" x-bind:disabled="condicion !== 'danado'" x-bind:required="condicion === 'danado'" /></div>
                            <input type="hidden" name="devoluciones[{{ $linea->id }}][cantidad_danada]" value="0" :disabled="condicion === 'danado'">
                        </div>
                    @endforeach
                    <p class="mt-4">Confirma únicamente al recibir todas las unidades de todos los insumos. Las dañadas permanecerán fuera de servicio.</p>
                </div>
                <div class="ui-form-actions"><span></span><x-primary-button>Registrar devolución completa</x-primary-button></div>
            </form>
        
    </section>
@endif
