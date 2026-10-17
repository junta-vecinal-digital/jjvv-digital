<section class="ui-panel mt-6 space-y-4 p-6">
    <h3 class="text-lg font-semibold">Catálogo de insumos</h3>
    <p class="text-sm">Los insumos de este catálogo se ofrecen al registrar existencias. Las cantidades se gestionan desde Inventario.</p>
    @if (session('status') === 'insumo-agregado')
        <p role="status" class="text-green-700 dark:text-green-400">Insumo agregado al catálogo.</p>
    @endif
    @foreach (\App\Models\TipoInsumo::CATEGORIAS as $categoria => $etiqueta)
        <details class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
            <summary class="cursor-pointer font-medium">{{ $etiqueta }}</summary>
            <ul class="mt-3 space-y-2 text-sm">
                @forelse ($tiposInsumo->get($categoria, collect()) as $tipo)
                    <li>{{ $tipo->nombre }}</li>
                @empty
                    <li>No hay insumos en esta categoría.</li>
                @endforelse
            </ul>
        </details>
    @endforeach
    <form method="POST" action="{{ route('parametros.insumos.store') }}" class="space-y-4 border-t border-gray-200 pt-4 dark:border-gray-700">
        @csrf
        <div>
            <x-input-label for="nombre_insumo" value="Nuevo insumo" />
            <x-text-input id="nombre_insumo" name="nombre_insumo" :value="old('nombre_insumo')" maxlength="255" required class="mt-1 block w-full" />
            <x-input-error :messages="$errors->get('nombre_insumo')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="categoria_insumo" value="Categoría" />
            <select id="categoria_insumo" name="categoria_insumo" required class="mt-1 block w-full">
                <option value="">Seleccionar</option>
                @foreach (\App\Models\TipoInsumo::CATEGORIAS as $valor => $nombre)
                    <option value="{{ $valor }}" @selected(old('categoria_insumo') === $valor)>{{ $nombre }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('categoria_insumo')" class="mt-2" />
        </div>
        <x-primary-button>Agregar insumo</x-primary-button>
    </form>
</section>
