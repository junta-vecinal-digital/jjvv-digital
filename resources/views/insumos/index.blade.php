@php
    $personaInicial = $personas->firstWhere('clave', old('solicitante')) ?? $personas->first();
    $estado = [
        'personas' => $personas, 'persona' => old('solicitante', $personaInicial['clave'] ?? ($gestion ? 'manual' : '')),
        'grupo' => (string) old('grupo_id', count($personaInicial['grupos'] ?? []) === 1 ? $personaInicial['grupos'][0]['id'] : ''),
        'telefono' => old('telefono_contacto', $personaInicial['telefono'] ?? ''),
        'direccion' => old('direccion_contacto', $personaInicial['direccion'] ?? ''),
        'rut' => old('solicitante_rut', $personaInicial['rut'] ?? ''),
        'lineas' => array_values(old('lineas', [['item_inventario_id' => '', 'cantidad' => 1]])),
    ];
@endphp
<x-app-layout>
    <x-slot name="header"><x-page-heading :title="$gestion ? 'Solicitudes de insumos' : 'Mis solicitudes de insumos'" description="Consulta solicitudes y su estado. Abre el formulario para registrar una nueva." /></x-slot>
    <div class="max-w-6xl mx-auto px-4 py-6 space-y-6">
        <x-reservas-tabs active="solicitudes" />
        @if (session('status'))<p class="ui-panel p-4">{{ session('status') }}</p>@endif
        @if ($errors->any())<div class="ui-panel p-4 text-red-600">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        @if (! $gestion) @include('insumos._horarios') @endif
        <details class="ui-panel p-6" @if ($errors->any()) open @endif>
            <summary class="cursor-pointer font-semibold">{{ $gestion ? 'Registrar solicitud propia o en ventanilla' : 'Solicitar insumos' }}</summary>
            <x-form-wizard :steps="['Solicitante', 'Insumos']" :fields="[['solicitante', 'solicitante_nombre', 'solicitante_rut', 'grupo_id', 'telefono_contacto', 'direccion_contacto', 'fecha_comprometida'], ['lineas']]">
                <form method="POST" action="{{ route('insumos.store') }}" class="space-y-4" novalidate
                    x-data="{ ...@js($estado), seleccionada() { return this.personas.find(p => p.clave === this.persona); }, elegir() { const p = this.seleccionada(); this.grupo = p?.grupos.length === 1 ? String(p.grupos[0].id) : ''; this.telefono = p?.telefono ?? ''; this.direccion = p?.direccion ?? ''; this.rut = p?.rut ?? ''; } }">
                    @csrf
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
                                <x-text-input id="solicitante_nombre" name="solicitante_nombre" :value="old('solicitante_nombre')" x-bind:disabled="persona !== 'manual'" x-bind:required="persona === 'manual'" maxlength="255" class="w-full" />
                                <p class="mt-2 text-sm text-gray-500">Se registra en ventanilla sin crear ficha ni cuenta.</p>
                            </div>
                        @endif
                        <div x-show="seleccionada()?.grupos.length > 0" class="mt-4">
                            <x-input-label for="grupo_id" value="Agrupación representada" />
                            <select id="grupo_id" name="grupo_id" x-model="grupo" :disabled="!seleccionada()?.grupos.length" :required="seleccionada()?.grupos.length > 0" class="ui-input w-full">
                                <option value="">Elegir agrupación</option>
                                <template x-for="grupoOpcion in seleccionada()?.grupos ?? []" :key="grupoOpcion.id"><option :value="grupoOpcion.id" x-text="grupoOpcion.nombre"></option></template>
                            </select>
                        </div>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div><x-input-label for="solicitante_rut" value="RUT del solicitante" /><x-text-input id="solicitante_rut" name="solicitante_rut" x-model="rut" x-bind:readonly="!!seleccionada()?.rut" maxlength="12" required class="w-full" /></div>
                            <div><x-input-label for="telefono_contacto" value="Teléfono de contacto" /><x-text-input id="telefono_contacto" name="telefono_contacto" x-model="telefono" maxlength="20" required class="w-full" /></div>
                            <div><x-input-label for="direccion_contacto" value="Dirección de contacto" /><x-text-input id="direccion_contacto" name="direccion_contacto" x-model="direccion" x-bind:required="persona === 'manual'" maxlength="255" class="w-full" /></div>
                            <div><x-input-label for="fecha_comprometida" value="Fecha comprometida de devolución" /><x-date-input id="fecha_comprometida" name="fecha_comprometida" :value="old('fecha_comprometida', now()->addWeek()->toDateString())" :min="now()->toDateString()" required /></div>
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
                        <button type="button" class="ui-button ui-button-secondary" @click="lineas.push({ item_inventario_id: '', cantidad: 1 })" :disabled="lineas.length >= 100">Agregar otro insumo</button>
                        <p class="mt-3">Selecciona cada insumo una sola vez. Las cantidades se comprobarán nuevamente al aprobar.</p>
                    </x-form-step>
                    <p class="text-sm text-gray-500">{{ $gestion ? 'El responsable se registra automáticamente según quién ingresa o aprueba la solicitud.' : 'El responsable se asignará al aprobar la solicitud.' }}</p>
                    <x-wizard-actions />
                </form>
            </x-form-wizard>
        </details>
        <section class="ui-panel overflow-x-auto">
            <table class="ui-table"><thead><tr><th>Solicitud</th><th>Solicitante</th><th>Grupo</th><th>Devolución comprometida</th><th>Estado</th><th></th></tr></thead><tbody>
                @forelse ($solicitudes as $solicitud)
                    <tr><td>#{{ $solicitud->id }} · {{ $solicitud->created_at->format('d/m/Y') }}</td><td>{{ $solicitud->solicitante_nombre }}</td><td>{{ $solicitud->grupo_nombre ?? 'Particular' }}</td><td>{{ $solicitud->fecha_comprometida->format('d/m/Y') }}</td><td><x-status-badge :state="$solicitud->estado" :label="\App\Models\SolicitudInsumo::ESTADOS[$solicitud->estado]" />@if ($solicitud->atrasado()) · Atrasado @endif</td><td><a class="underline" href="{{ route('insumos.show', $solicitud) }}">Ver detalle</a></td></tr>
                @empty<tr><td colspan="6">No hay solicitudes para mostrar.</td></tr>@endforelse
            </tbody></table>
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
    </div>
</x-app-layout>
