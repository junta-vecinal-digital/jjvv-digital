<x-app-layout>
    <x-slot name="header"><x-page-heading title="Registrar insumo" description="Consulta las existencias y ajusta la cantidad total de cada insumo.">@unless(request('crear'))<a href="{{ route('inventario.index', ['crear' => 1]) }}" class="ui-button ui-button-primary">Registrar insumo</a>@endunless</x-page-heading></x-slot>
    <div class="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">

        @if (session('status'))
            <p role="status" class="rounded-lg bg-green-100 p-4 text-sm text-green-800 dark:bg-green-900/40 dark:text-green-200">{{ session('status') }}</p>
        @endif
        <x-input-error :messages="$errors->get('cantidad_total')" />
        @if(request('crear'))
        <section class="ui-panel p-6 space-y-4">
            <h2 class="text-lg font-semibold">Registrar ítem</h2>
<x-form-wizard :steps="['Insumo y existencias']" :fields="[['tipo_insumo_id','cantidad']]">            <form method="POST" action="{{ route('inventario.store') }}" class="space-y-4 p-6 pt-0" novalidate>
                @csrf
<x-form-step number="0" class="ui-form-grid">
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
</x-form-step><x-wizard-actions :cancel="route('inventario.index')" submit-label="Registrar insumo" />
            </form></x-form-wizard>
        </section>
        @else
        <h2 class="text-xl font-semibold">Existencias registradas</h2>
        <section class="ui-panel overflow-x-auto"><table class="ui-table"><thead><tr><th>Insumo</th><th>Disponibles</th><th>Total</th><th>Comprometidas / fuera de servicio</th><th>Acción</th></tr></thead>
        @forelse($items as $item)<x-record-row :id="'item_'.$item->id" :columns="5" label="Editar cantidad"><x-slot name="summary"><td><strong>{{ $item->nombre }}</strong><span class="ui-sub">{{ $item->tipoInsumo ? \App\Models\TipoInsumo::CATEGORIAS[$item->tipoInsumo->categoria] : 'Sin categoría' }}</span></td><td class="font-semibold">{{ $item->disponibles() }}</td><td>{{ $item->cantidad }}</td><td>{{ (int)$item->reservadas }} reservadas<span class="ui-sub">{{ (int)$item->prestadas }} en préstamo · {{ (int)$item->fuera_servicio }} fuera de servicio</span></td></x-slot>
        <h2 class="text-xl font-semibold">Editar cantidad · {{ $item->nombre }}</h2>
        <form method="POST" action="{{ route('inventario.update', $item) }}">@csrf @method('PUT')<input type="hidden" name="operacion" value="item_{{ $item->id }}"><input type="hidden" name="_item" value="{{ $item->id }}"><x-input-label :for="'total_'.$item->id" value="Cantidad total" /><x-text-input :id="'total_'.$item->id" name="cantidad_total" type="number" :min="$item->cantidad - $item->disponibles()" max="4294967295" :value="old('operacion') === 'item_'.$item->id ? old('cantidad_total') : $item->cantidad" required /><p class="text-sm text-gray-500">Mínimo actual: {{ $item->cantidad - $item->disponibles() }}. La disponibilidad se comprobará al guardar.</p><div><x-primary-button>Guardar cantidad</x-primary-button></div></form>
        </x-record-row>@empty<tbody><tr><td colspan="5">No hay insumos registrados.</td></tr></tbody>@endforelse</table></section>
        {{ $items->links() }}
        @endif
</div>
</x-app-layout>
