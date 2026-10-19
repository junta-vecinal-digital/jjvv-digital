<x-app-layout>
    <x-slot name="header"><x-page-heading :title="'Solicitud de insumos #'.$solicitud->id" description="Revisa el estado, las unidades solicitadas y las condiciones del préstamo." /></x-slot>
    <div class="max-w-4xl mx-auto px-4 py-6 space-y-6">
        @if (request('consulta') === 'historial' && auth()->user()->hasRole('presidente', 'secretario'))
            <a class="underline" href="{{ route('inventario.index') }}#historial">Volver al historial de inventario</a>
        @else
        <a class="underline" href="{{ route($gestion && in_array($solicitud->estado, ['entregado', 'devuelto']) ? 'inventario.prestamos.index' : 'insumos.index') }}">Volver a {{ $gestion && in_array($solicitud->estado, ['entregado', 'devuelto']) ? 'préstamos entregados' : 'solicitudes' }}</a>
        @endif
        @if (session('status'))<p role="status" class="ui-panel p-4">{{ session('status') }}</p>@endif
        @if ($errors->any())<div class="ui-panel p-4 text-red-600">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        @if (auth()->user()->hasRole('presidente', 'tesorero') && $solicitud->estado !== 'pendiente')
            <a class="underline" href="{{ route('finanzas.insumos.show', $solicitud) }}">Ver aporte y garantía en Finanzas</a>
        @endif
        @if ($gestion && in_array($solicitud->estado, ['pendiente', 'aprobado', 'entregado']))
            <aside class="rounded-xl border border-brand-400/40 bg-brand-50 p-4 text-sm text-brand-600 dark:bg-brand-500/10 dark:text-brand-400" aria-label="Siguiente paso">
                <h2 class="font-semibold">Siguiente paso</h2>
                <p class="mt-1">{{ match ($solicitud->estado) { 'pendiente' => 'Revisa los datos y las unidades. Más abajo puedes definir las condiciones y aprobar el préstamo.', 'aprobado' => 'Las unidades están reservadas. Registra su entrega cuando el solicitante las retire.', 'entregado' => 'Al recibir todos los insumos, registra la devolución y las unidades dañadas, si corresponde.' } }}</p>
            </aside>
        @endif
        <section class="ui-panel p-6 space-y-3">
            <p>Estado: <x-status-badge :state="$solicitud->estado" :label="\App\Models\SolicitudInsumo::ESTADOS[$solicitud->estado]" />@if ($solicitud->atrasado()) <strong class="text-red-600">· Atrasado</strong>@endif</p>
            <p>Solicitante: {{ $solicitud->solicitante_nombre }} · {{ $solicitud->canal === 'ventanilla' ? 'Ventanilla' : 'En línea' }}</p>
            <p>Agrupación: {{ $solicitud->grupo_nombre ?? 'Particular' }}</p>
            @if ($solicitud->solicitante_rut)<p>RUT: {{ $solicitud->solicitante_rut }}</p>@endif
            <p>Contacto: {{ $solicitud->telefono_contacto }}@if ($solicitud->correo_contacto) · {{ $solicitud->correo_contacto }}@endif</p>
            @if ($solicitud->direccion_contacto)<p>Dirección: {{ $solicitud->direccion_contacto }}</p>@endif
            <p>Solicitud: {{ $solicitud->created_at->format('d/m/Y') }} · Devolución comprometida: {{ $solicitud->fecha_comprometida->format('d/m/Y') }}</p>
            @if ($solicitud->responsable_nombre)<p>Dirigente responsable: {{ $solicitud->responsable_nombre }}</p>@endif
            @if ($solicitud->monto !== null)<p>Aporte voluntario del préstamo: ${{ number_format($solicitud->monto, 0, ',', '.') }} · Garantía acordada: ${{ number_format($solicitud->garantia, 0, ',', '.') }}</p>@endif
            @if ($solicitud->fecha_entrega)<p>Entrega: {{ $solicitud->fecha_entrega->format('d/m/Y') }}</p>@endif
            @if ($solicitud->fecha_devolucion)<p>Devolución completa: {{ $solicitud->fecha_devolucion->format('d/m/Y') }}</p>@endif
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
                <x-form-wizard :steps="['Condiciones del préstamo']" :fields="[['responsable_id', 'monto', 'garantia', 'fecha_comprometida']]">
                    <form method="POST" action="{{ route('insumos.aprobar', $solicitud) }}" class="space-y-4" novalidate>
                        @csrf
                        <x-form-step number="0">
                            <div><x-input-label for="responsable_id" value="Dirigente responsable del préstamo" /><select id="responsable_id" name="responsable_id" class="ui-input w-full" required><option value="">Seleccionar dirigente</option>@foreach ($dirigentes as $dirigente)<option value="{{ $dirigente->id }}" @selected((string) old('responsable_id') === (string) $dirigente->id)>{{ $dirigente->name }} · {{ $dirigente->nombreRol() }}</option>@endforeach</select></div>
                            <div class="grid gap-4 sm:grid-cols-2 mt-4">
                                <div><x-input-label for="monto" value="Aporte voluntario del préstamo (CLP, $0 sin aporte)" /><x-text-input id="monto" name="monto" type="number" min="0" max="4294967295" step="1" :value="old('monto', 0)" required class="w-full" /></div>
                                <div><x-input-label for="garantia" value="Garantía total acordada (CLP)" /><x-text-input id="garantia" name="garantia" type="number" min="0" max="4294967295" step="1" :value="old('garantia', 0)" required class="w-full" /></div>
                                <div><x-input-label for="fecha_comprometida" value="Devolución comprometida" /><x-date-input id="fecha_comprometida" name="fecha_comprometida" :value="old('fecha_comprometida', $solicitud->fecha_comprometida->toDateString())" :min="now()->toDateString()" required /></div>
                            </div>
                            <p class="mt-4">Los montos corresponden al préstamo completo; no se multiplican por unidades ni por días. La aprobación reserva todas las unidades o se rechaza completa si faltan existencias.</p>
                        </x-form-step>
                        <x-wizard-actions />
                    </form>
                </x-form-wizard>
            </section>
        @endif
        @if ($gestion && $solicitud->estado === 'aprobado')
            <section class="ui-panel p-6">
                <h2 class="font-semibold mb-4">Registrar entrega completa</h2>
                <x-form-wizard :steps="['Entrega']">
                    <form method="POST" action="{{ route('insumos.entregar', $solicitud) }}" class="space-y-4" novalidate>
                        @csrf
                        <x-form-step number="0">
                            <x-input-label for="fecha_entrega" value="Fecha de entrega" /><x-date-input id="fecha_entrega" name="fecha_entrega" :value="old('fecha_entrega', now()->toDateString())" :min="$solicitud->created_at->toDateString()" :max="min(now()->toDateString(), $solicitud->fecha_comprometida->toDateString())" required />
                            @foreach ($solicitud->lineas as $linea)
                                <div class="mt-4"><x-input-label :for="'condicion_'.$linea->id" :value="$linea->nombre_insumo.' · '.$linea->cantidad.' unidades: estado de salida'" /><select id="condicion_{{ $linea->id }}" name="condiciones[{{ $linea->id }}]" class="ui-input w-full" required>@foreach (\App\Models\Prestamo::CONDICIONES as $clave => $etiqueta)<option value="{{ $clave }}" @selected(old('condiciones.'.$linea->id, 'bueno') === $clave)>{{ $etiqueta }}</option>@endforeach</select></div>
                            @endforeach
                        </x-form-step>
                        <x-wizard-actions />
                    </form>
                </x-form-wizard>
            </section>
        @endif
        @if ($gestion && $solicitud->estado === 'entregado')
            <section class="ui-panel p-6">
                <h2 class="font-semibold mb-4">Registrar devolución completa</h2>
                <x-form-wizard :steps="['Devolución']">
                    <form method="POST" action="{{ route('insumos.devolver', $solicitud) }}" class="space-y-4" novalidate>
                        @csrf
                        <x-form-step number="0">
                            <x-input-label for="fecha_devolucion" value="Fecha efectiva de devolución" /><x-date-input id="fecha_devolucion" name="fecha_devolucion" :value="old('fecha_devolucion', now()->toDateString())" :min="$solicitud->fecha_entrega->toDateString()" :max="now()->toDateString()" required />
                            @foreach ($solicitud->lineas as $linea)
                                <div class="mt-4 grid gap-4 sm:grid-cols-2" x-data="{ condicion: @js(old('devoluciones.'.$linea->id.'.estado_regreso', 'bueno')) }">
                                    <div><x-input-label :for="'regreso_'.$linea->id" :value="$linea->nombre_insumo.' · '.$linea->cantidad.' unidades: regreso'" /><select id="regreso_{{ $linea->id }}" name="devoluciones[{{ $linea->id }}][estado_regreso]" x-model="condicion" class="ui-input w-full" required>@foreach (\App\Models\Prestamo::CONDICIONES as $clave => $etiqueta)<option value="{{ $clave }}">{{ $etiqueta }}</option>@endforeach</select></div>
                                    <div x-show="condicion === 'danado'" x-cloak><x-input-label :for="'danadas_'.$linea->id" value="Unidades dañadas fuera de servicio" /><x-text-input :id="'danadas_'.$linea->id" name="devoluciones[{{ $linea->id }}][cantidad_danada]" type="number" min="1" :max="$linea->cantidad" step="1" :value="old('devoluciones.'.$linea->id.'.cantidad_danada', 1)" x-bind:disabled="condicion !== 'danado'" x-bind:required="condicion === 'danado'" /></div>
                                    <input type="hidden" name="devoluciones[{{ $linea->id }}][cantidad_danada]" value="0" :disabled="condicion === 'danado'">
                                </div>
                            @endforeach
                            <p class="mt-4">Confirma únicamente al recibir todas las unidades de todos los insumos. Las dañadas permanecerán fuera de servicio.</p>
                        </x-form-step>
                        <x-wizard-actions />
                    </form>
                </x-form-wizard>
            </section>
        @endif
    </div>
</x-app-layout>
