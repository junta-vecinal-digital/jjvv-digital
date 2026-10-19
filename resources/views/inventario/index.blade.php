<x-app-layout>
    <x-slot name="header"><x-page-heading title="Registrar insumo" description="Consulta existencias y registra o ajusta las unidades disponibles." /></x-slot>
    <div class="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">

        @if (session('status'))
            <p role="status" class="rounded-lg bg-green-100 p-4 text-sm text-green-800 dark:bg-green-900/40 dark:text-green-200">{{ session('status') }}</p>
        @endif
        <x-input-error :messages="$errors->get('cantidad_total')" />
        <details class="ui-panel" @if ($errors->hasAny(['tipo_insumo_id', 'cantidad'])) open @endif>
            <summary class="cursor-pointer p-4 font-medium">Registrar ítem</summary>
            <form method="POST" action="{{ route('inventario.store') }}" class="space-y-4 p-6 pt-0">
                @csrf
                <div>
                    <x-input-label for="tipo_insumo_id" value="Insumo del catálogo" />
                    <select id="tipo_insumo_id" name="tipo_insumo_id" required class="mt-1 block w-full">
                        <option value="">Seleccionar</option>
                        @foreach (\App\Models\TipoInsumo::CATEGORIAS as $categoria => $etiqueta)
                            @if ($tiposInsumo->where('categoria', $categoria)->isNotEmpty())
                                <optgroup label="{{ $etiqueta }}">
                                    @foreach ($tiposInsumo->where('categoria', $categoria) as $tipo)
                                        <option value="{{ $tipo->id }}" @selected((string) old('tipo_insumo_id') === (string) $tipo->id)>{{ $tipo->nombre }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Los insumos que ya tienen existencias se ajustan con Editar cantidad. Para agregar otro tipo al catálogo, contacta al administrador.</p>
                    <x-input-error :messages="$errors->get('tipo_insumo_id')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="cantidad" value="Cantidad total de unidades" />
                    <x-text-input id="cantidad" name="cantidad" type="number" min="0" max="4294967295" step="1" class="mt-1 block w-full" :value="old('cantidad')" required />
                    <x-input-error :messages="$errors->get('cantidad')" class="mt-2" />
                </div>
                <button class="ui-button ui-button-primary" @disabled($tiposInsumo->isEmpty())>Registrar ítem</button>
            </form>
        </details>
        <div class="ui-panel overflow-x-auto">
            <table class="ui-table">
                <thead><tr><th>Ítem</th><th>Categoría</th><th>Cantidad total</th><th>Reservadas</th><th>En préstamo</th><th>Fuera de servicio</th><th>Disponibles</th><th>Modificar</th></tr></thead>
                <tbody>
                    @forelse ($items as $item)
                        @php($editando = $errors->has('cantidad_total') && old('_item') === (string) $item->id)
                        <tr>
                            <td>{{ $item->nombre }}</td>
                            <td>{{ $item->tipoInsumo ? \App\Models\TipoInsumo::CATEGORIAS[$item->tipoInsumo->categoria] : 'Sin categoría' }}</td>
                            <td>{{ $item->cantidad }}</td><td>{{ (int) $item->reservadas }}</td><td>{{ (int) $item->prestadas }}</td><td>{{ (int) $item->fuera_servicio }}</td><td>{{ $item->disponibles() }}</td>
                            <td>
                                <details @if ($editando) open @endif>
                                    <summary class="cursor-pointer text-brand-500 underline">Editar cantidad</summary>
                                    <form method="POST" action="{{ route('inventario.update', $item) }}" class="mt-3 min-w-48 space-y-3">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="_item" value="{{ $item->id }}">
                                        <x-input-label :for="'cantidad_total_'.$item->id" value="Cantidad total" />
                                        <x-text-input :id="'cantidad_total_'.$item->id" name="cantidad_total" type="number" :min="$item->cantidad - $item->disponibles()" max="4294967295" step="1" :value="$editando ? old('cantidad_total') : $item->cantidad" required class="block w-full" />
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Mínimo actual: {{ $item->cantidad - $item->disponibles() }}. Se comprueba de nuevo al guardar.</p>
                                        <button class="ui-button ui-button-secondary">Guardar cantidad</button>
                                    </form>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No hay ítems registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $items->links() }}
</div>
</x-app-layout>
