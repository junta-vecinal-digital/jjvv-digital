@php
    $esCreacion = ! old('operacion') || old('operacion') === 'crear-insumo';
    $valorCreacion = fn ($campo, $defecto = null) => $esCreacion ? old($campo, $defecto) : $defecto;
@endphp
@php
    $personaInicial = $personas->firstWhere('clave', $valorCreacion('solicitante')) ?? $personas->first();
    $estado = [
        'personas' => $personas, 'persona' => $valorCreacion('solicitante', $personaInicial['clave'] ?? ($gestion ? 'manual' : '')),
        'grupo' => (string) $valorCreacion('grupo_id', str_starts_with($personaInicial['clave'] ?? '', 'grupo:') ? $personaInicial['grupos'][0]['id'] : ''),
        'telefono' => $valorCreacion('telefono_contacto', $personaInicial['telefono'] ?? ''),
        'direccion' => $valorCreacion('direccion_contacto', $personaInicial['direccion'] ?? ''),
        'rut' => $valorCreacion('solicitante_rut', $personaInicial['rut'] ?? ''),
        'usuarioActual' => auth()->id(),
        'lineas' => array_values($valorCreacion('lineas', [['item_inventario_id' => '', 'cantidad' => 1]])),
    ];
@endphp
<x-app-layout>
    <x-slot name="header"><x-page-heading :title="$gestion ? 'Solicitudes de insumos' : 'Mis solicitudes de insumos'" description="Consulta los detalles y gestiona cada préstamo desde el listado.">@unless(request('crear'))<a class="ui-button ui-button-primary" href="{{ route('insumos.index', ['crear'=>1]) }}">Registrar solicitud</a>@endunless</x-page-heading></x-slot>
    <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">
        <x-reservas-tabs active="solicitudes" />
        @if (session('status'))<p role="status" class="ui-notice">{{ session('status') }}</p>@endif
        @if ($errors->any())<div role="alert" class="ui-notice text-red-700">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        @if (! $gestion) @include('insumos._horarios') @endif
        @php($erroresListado = $errors)
        @php($errors = $esCreacion ? $errors : new \Illuminate\Support\ViewErrorBag)
        @if(request('crear'))
        <section class="ui-panel p-6 space-y-4">
            <h2 class="text-lg font-semibold">{{ $gestion ? 'Registrar solicitud propia o en ventanilla' : 'Solicitar insumos' }}</h2>
            <x-form-wizard :steps="['Solicitante', 'Insumos']" :fields="[['solicitante', 'solicitante_nombre', 'solicitante_rut', 'grupo_id', 'telefono_contacto', 'direccion_contacto', 'fecha_comprometida'], ['lineas', 'monto', 'garantia', 'entregar_ahora']]">
                <form method="POST" action="{{ route('insumos.store') }}" class="space-y-4" novalidate
                    x-data="{ ...@js($estado), seleccionada() { return this.personas.find(p => p.clave === this.persona); }, ventanilla() { return this.persona === 'manual' || this.seleccionada()?.usuario_id !== this.usuarioActual; }, elegir() { const p = this.seleccionada(); this.grupo = this.persona.startsWith('grupo:') ? String(p.grupos[0].id) : ''; this.telefono = p?.telefono ?? ''; this.direccion = p?.direccion ?? ''; this.rut = p?.rut ?? ''; } }">
                    @csrf
                    <input type="hidden" name="operacion" value="crear-insumo">
                    <x-form-step number="0">
                        @if ($gestion)
                            <x-input-label for="solicitante" value="Solicitante" />
                            <select id="solicitante" name="solicitante" x-model="persona" @change="elegir()" class="ui-input w-full" required>
                                <option value="manual">Ingresar vecino en ventanilla</option>
                                @foreach ($personas as $persona)<option value="{{ $persona['clave'] }}">{{ $persona['nombre'] }}{{ str_starts_with($persona['clave'], 'grupo:') ? ' · '.$persona['grupos'][0]['nombre'] : '' }}</option>@endforeach
                            </select>
                        @else
                            <p>Solicitante: {{ $personaInicial['nombre'] ?? auth()->user()->name }}</p>
                        @endif
                        @if ($gestion)
                            <div x-show="persona === 'manual'" x-cloak>
                                <x-input-label for="solicitante_nombre" value="Nombre completo del vecino" />
                                <x-text-input id="solicitante_nombre" name="solicitante_nombre" :value="$valorCreacion('solicitante_nombre')" x-bind:disabled="persona !== 'manual'" x-bind:required="persona === 'manual'" maxlength="255" class="w-full" />
                                <p class="mt-2 text-sm text-gray-500">Se registra en ventanilla sin crear ficha ni cuenta.</p>
                            </div>
                        @endif
                        <div x-show="seleccionada()?.grupos.length > 0" class="mt-4">
                            <x-input-label for="grupo_id" value="Agrupación representada" />
                            <select id="grupo_id" name="grupo_id" x-model="grupo" :disabled="!seleccionada()?.grupos.length" :required="persona.startsWith('grupo:')" class="ui-input w-full">
                                <option value="" x-text="persona.startsWith('grupo:') ? 'Elegir agrupación' : 'Uso personal (sin agrupación)'"></option>
                                <template x-for="grupoOpcion in seleccionada()?.grupos ?? []" :key="grupoOpcion.id"><option :value="grupoOpcion.id" x-text="grupoOpcion.nombre"></option></template>
                            </select>
                        </div>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div><x-input-label for="solicitante_rut" value="RUT del solicitante" /><x-text-input id="solicitante_rut" name="solicitante_rut" x-model="rut" x-bind:readonly="!!seleccionada()?.rut" maxlength="12" required class="w-full" /></div>
                            <div><x-input-label for="telefono_contacto" value="Teléfono de contacto" /><x-text-input id="telefono_contacto" name="telefono_contacto" x-model="telefono" maxlength="20" required class="w-full" /></div>
                            <div><x-input-label for="direccion_contacto" value="Dirección de contacto" /><x-text-input id="direccion_contacto" name="direccion_contacto" x-model="direccion" x-bind:required="persona === 'manual'" maxlength="255" class="w-full" /></div>
                            <div><x-input-label for="fecha_comprometida" value="Fecha comprometida de devolución" /><x-date-input native id="fecha_comprometida" name="fecha_comprometida" :value="$valorCreacion('fecha_comprometida', now()->addWeek()->toDateString())" :min="now()->toDateString()" required /></div>
                        </div>
                    </x-form-step>
                    <x-form-step number="1">
                        <template x-for="(linea, indice) in lineas" :key="indice">
                            <div class="grid gap-3 sm:grid-cols-3 mb-4">
                                <div>
                                    <label :for="'insumo_'+indice">Insumo</label>
                                    <select :id="'insumo_'+indice" :name="'lineas['+indice+'][item_inventario_id]'" x-model="linea.item_inventario_id" class="ui-input w-full" required>
                                        <option value="">Seleccionar insumo</option>
                                        @foreach ($items->groupBy(fn ($item) => $item->tipoInsumo->categoria) as $categoria => $existencias)
                                            <optgroup label="{{ \App\Models\TipoInsumo::CATEGORIAS[$categoria] }}">
                                                @foreach ($existencias as $item)<option value="{{ $item->id }}">{{ $item->nombre }} · {{ $item->disponibles() }} disponibles</option>@endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                                <div><label :for="'cantidad_'+indice">Unidades</label><input type="number" :id="'cantidad_'+indice" :name="'lineas['+indice+'][cantidad]'" x-model="linea.cantidad" min="1" max="4294967295" step="1" class="ui-input w-full" required></div>
                                <button type="button" class="ui-button ui-button-secondary self-end" @click="lineas.splice(indice, 1)" :disabled="lineas.length === 1">Quitar</button>
                            </div>
                        </template>
                        @if ($gestion)
                        <fieldset x-show="ventanilla()" :disabled="!ventanilla()" class="space-y-3">
                            <div><x-input-label for="monto_ventanilla" value="Aporte total del préstamo (CLP)" /><x-text-input id="monto_ventanilla" name="monto" type="number" min="0" max="4294967295" :value="$valorCreacion('monto', 0)" required /></div>
                            <div><x-input-label for="garantia_ventanilla" value="Garantía total (CLP)" /><x-text-input id="garantia_ventanilla" name="garantia" type="number" min="0" max="4294967295" :value="$valorCreacion('garantia', 0)" required /></div>
                            <label class="flex gap-2"><input type="checkbox" name="entregar_ahora" value="1" @checked($valorCreacion('entregar_ahora'))> Entregar ahora si aporte y garantía son $0</label>
                            <template x-for="(lineaSalida, indiceSalida) in lineas" :key="indiceSalida">
                                <div x-show="lineaSalida.item_inventario_id"><label :for="'salida_'+lineaSalida.item_inventario_id">Estado de salida · <span x-text="'Insumo ' + (indiceSalida + 1)"></span></label><select :id="'salida_'+lineaSalida.item_inventario_id" :name="'estados_salida['+lineaSalida.item_inventario_id+']'" class="ui-input">@foreach (\App\Models\Prestamo::CONDICIONES as $claveEstado => $nombreEstado)<option value="{{ $claveEstado }}">{{ $nombreEstado }}</option>@endforeach</select></div>
                            </template>
                        </fieldset>
                        @endif
                        <button type="button" class="ui-button ui-button-secondary" @click="lineas.push({ item_inventario_id: '', cantidad: 1 })" :disabled="lineas.length >= 100">Agregar otro insumo</button>
                    </x-form-step>
                    <x-wizard-actions :cancel="route('insumos.index')" />
                </form>
            </x-form-wizard>
        </section>
        @else
        @php($errors = $erroresListado)
        <section class="ui-panel overflow-x-auto">
            <table class="ui-table"><thead><tr><th>Solicitud / solicitante</th><th>Agrupación</th><th>Devolución comprometida</th><th>Estado</th><th>Detalle</th></tr></thead>
                @forelse ($solicitudes as $solicitud)
                    <x-record-row :id="'insumo_'.$solicitud->id" :columns="5">
                        <x-slot name="summary">
                            <td><strong>{{ $solicitud->solicitante_nombre }}</strong><span class="block text-xs text-gray-500">#{{ $solicitud->id }} · {{ $solicitud->created_at->format('d/m/Y') }}</span></td>
                            <td>{{ $solicitud->grupo_nombre ?? 'Particular' }}</td>
                            <td>{{ $solicitud->fecha_comprometida->format('d/m/Y') }}</td>
                            <td><x-status-badge :state="$solicitud->estado" :label="\App\Models\SolicitudInsumo::ESTADOS[$solicitud->estado]" />@if ($solicitud->atrasado()) <span class="block text-xs text-red-700">Atrasado</span>@endif</td>
                        </x-slot>
                        @include('insumos._detalle')
                    </x-record-row>
                @empty<tbody><tr><td colspan="5">No hay solicitudes para mostrar.</td></tr></tbody>@endforelse
            </table>
        </section>
        {{ $solicitudes->links() }}
        @if ($gestion && $prestamosHistoricos->isNotEmpty())
            <details class="ui-panel p-5">
                <summary class="cursor-pointer font-semibold">Préstamos anteriores pendientes de devolución</summary>
                <ul class="mt-4 space-y-3">
                    @foreach ($prestamosHistoricos as $prestamo)
                        <li><a class="underline" href="{{ route('inventario.prestamos.show', $prestamo) }}">{{ $prestamo->destinatario_nombre }} · {{ $prestamo->item->nombre }} · Registrar devolución</a></li>
                    @endforeach
                </ul>
            </details>
        @endif
        @endif
    </div>
</x-app-layout>
