@php
    $contactos = $grupos->mapWithKeys(function ($grupo) {
        $representante = $grupo->datosRepresentante();
        return [$grupo->id => filled($grupo->contacto_nombre)
            ? ['nombre' => $grupo->contacto_nombre, 'telefono' => $grupo->contacto_telefono ?? '']
            : ['nombre' => $representante['nombre'] ?? '', 'telefono' => $representante['telefono'] ?? '']];
    });
    $existencias = $items->mapWithKeys(fn ($item) => [$item->id => $item->disponibles()]);
    $estadoFormulario = [
        'itemId' => (string) old('item_inventario_id', ''), 'destino' => old('destino', 'grupo'),
        'grupoId' => (string) old('grupo_id', ''), 'nombre' => old('responsable_nombre', ''),
        'telefono' => old('responsable_telefono', ''),
    ];
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Registrar préstamo</h1>
            <a href="{{ route('inventario.prestamos.index') }}" class="text-sm underline">Volver a préstamos</a>
        </div>
    </x-slot>
    <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-4"><x-reservas-tabs active="insumos" /></div>
        <div class="ui-panel p-6">
            @if ($items->every(fn ($item) => $item->disponibles() === 0))
                <p>No hay unidades disponibles para prestar. Revisa el <a href="{{ route('inventario.index') }}" class="text-brand-500 underline">inventario</a>.</p>
            @else
                <p class="mb-5 text-sm text-gray-500 dark:text-gray-400">Registra los insumos entregados. La cantidad disponible se comprueba nuevamente al guardar.</p>
                <x-form-wizard :steps="['Insumos', 'Destinatario', 'Entrega']" :fields="[['item_inventario_id', 'cantidad'], ['destino', 'grupo_id', 'destinatario_nombre', 'responsable_nombre', 'responsable_telefono'], ['fecha_solicitud', 'fecha_prestamo', 'estado_salida']]">
                    <form novalidate method="POST" action="{{ route('inventario.prestamos.store') }}" class="space-y-4" x-data="prestamoFormulario(@js($estadoFormulario), @js($contactos), @js($existencias))">
                        @csrf
                        <x-form-step number="0">
                            <div>
                                <x-input-label for="item_inventario_id" value="Ítem" />
                                <select id="item_inventario_id" name="item_inventario_id" x-model="itemId" class="mt-1 block w-full" required>
                                    <option value="">Seleccionar</option>
                                    @foreach ($items as $item)
                                        <option value="{{ $item->id }}" @disabled($item->disponibles() === 0)>{{ $item->nombre }} ({{ $item->disponibles() }} disponibles)</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('item_inventario_id')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="cantidad" value="Cantidad a prestar" />
                                <x-text-input id="cantidad" name="cantidad" type="number" min="1" x-bind:max="disponibles" step="1" :value="old('cantidad', 1)" class="mt-1 block w-full" required />
                                <x-input-error :messages="$errors->get('cantidad')" class="mt-2" />
                            </div>
                        </x-form-step>
                        <x-form-step number="1">
                            <div>
                                <x-input-label for="destino" value="Destinatario" />
                                <select id="destino" name="destino" x-model="destino" @change="cambiarDestino()" class="mt-1 block w-full" required>
                                    <option value="grupo">Grupo registrado</option><option value="libre">Otro destinatario</option>
                                </select>
                                <x-input-error :messages="$errors->get('destino')" class="mt-2" />
                            </div>
                            <div x-show="destino === 'grupo'">
                                <x-input-label for="grupo_id" value="Grupo" />
                                <select id="grupo_id" name="grupo_id" x-model="grupoId" @change="elegirGrupo()" :disabled="destino !== 'grupo'" :required="destino === 'grupo'" class="mt-1 block w-full">
                                    <option value="">Seleccionar</option>
                                    @foreach ($grupos as $grupo)<option value="{{ $grupo->id }}">{{ $grupo->nombre }}</option>@endforeach
                                </select>
                                <x-input-error :messages="$errors->get('grupo_id')" class="mt-2" />
                            </div>
                            <div x-cloak x-show="destino === 'libre'">
                                <x-input-label for="destinatario_nombre" value="Nombre del destinatario" />
                                <x-text-input id="destinatario_nombre" name="destinatario_nombre" list="destinatarios" :value="old('destinatario_nombre')" x-bind:disabled="destino !== 'libre'" x-bind:required="destino === 'libre'" maxlength="255" class="mt-1 block w-full" />
                                <datalist id="destinatarios">@foreach ($destinatarios as $nombre)<option value="{{ $nombre }}"></option>@endforeach</datalist>
                                <x-input-error :messages="$errors->get('destinatario_nombre')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="responsable_nombre" value="Nombre del responsable" />
                                <x-text-input id="responsable_nombre" name="responsable_nombre" x-model="nombre" maxlength="255" class="mt-1 block w-full" required />
                                <x-input-error :messages="$errors->get('responsable_nombre')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="responsable_telefono" value="Teléfono del responsable" />
                                <x-text-input id="responsable_telefono" name="responsable_telefono" type="tel" x-model="telefono" maxlength="20" class="mt-1 block w-full" required />
                                <x-input-error :messages="$errors->get('responsable_telefono')" class="mt-2" />
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Al elegir un grupo se sugieren sus datos de contacto o representante. Puedes indicar a otra persona responsable; no necesita ficha ni cuenta.</p>
                        </x-form-step>
                        <x-form-step number="2">
                            <div>
                                <x-input-label for="fecha_solicitud" value="Fecha de solicitud" />
                                <x-date-input id="fecha_solicitud" name="fecha_solicitud" :value="old('fecha_solicitud', now()->toDateString())" :max="now()->toDateString()" class="mt-1" required />
                                <x-input-error :messages="$errors->get('fecha_solicitud')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="fecha_prestamo" value="Fecha de entrega del préstamo" />
                                <x-date-input id="fecha_prestamo" name="fecha_prestamo" :value="old('fecha_prestamo', now()->toDateString())" :max="now()->toDateString()" class="mt-1" required />
                                <x-input-error :messages="$errors->get('fecha_prestamo')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="estado_salida" value="Estado de salida" />
                                <select id="estado_salida" name="estado_salida" class="mt-1 block w-full" required>
                                    @foreach (\App\Models\Prestamo::CONDICIONES as $valor => $nombre)<option value="{{ $valor }}" @selected(old('estado_salida', 'bueno') === $valor)>{{ $nombre }}</option>@endforeach
                                </select>
                                <x-input-error :messages="$errors->get('estado_salida')" class="mt-2" />
                            </div>
                        </x-form-step>
                        <x-wizard-actions />
                    </form>
                </x-form-wizard>
            @endif
        </div>
    </div>
</x-app-layout>
