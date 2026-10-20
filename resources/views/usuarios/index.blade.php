<x-app-layout>
    <x-slot name="header"><x-page-heading title="Usuarios del sistema" description="Gestiona cuentas, roles y accesos."><a href="{{ route('usuarios.create') }}"
               class="ui-button ui-button-primary">
                Crear usuario
            </a></x-page-heading></x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            @if (session('status') === 'usuario-eliminado')<p role="status" class="ui-panel p-4">Cuenta eliminada. La ficha y los registros históricos se conservan.</p>@endif
            @if (session('status') === 'usuario-creado')
                <div class="p-4 bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 rounded-lg text-sm">
                    El usuario fue creado. Entrégale su contraseña temporal de forma segura.
                </div>
            @endif

            @if (session('status') === 'usuario-actualizado')
                <div class="p-4 bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 rounded-lg text-sm">
                    El usuario fue actualizado.
                </div>
            @endif

            <form method="GET" action="{{ route('usuarios.index') }}" class="flex flex-wrap items-end gap-3 px-4 sm:px-0">
                <div>
                    <x-input-label for="buscar" value="Buscar" />
                    <x-text-input id="buscar" name="buscar" type="text" class="mt-1 block"
                        :value="request('buscar')" placeholder="Nombre o correo" />
                </div>

                <div>
                    <x-input-label for="rol" value="Rol" />
                    <select id="rol" name="rol"
                        class="mt-1 block border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-brand-500 dark:focus:border-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400 rounded-md shadow-sm">
                        <option value="">Todos</option>
                        @foreach (\App\Models\User::ROLES as $valor => $etiqueta)
                            <option value="{{ $valor }}" @selected(request('rol') === $valor)>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>

                <x-secondary-button type="submit">Filtrar</x-secondary-button>

                @if (request()->filled('buscar') || request()->filled('rol'))
                    <a href="{{ route('usuarios.index') }}" class="pb-2 text-sm underline text-gray-600 dark:text-gray-400">Limpiar</a>
                @endif
            </form>

            <x-operation-notice />
            <section class="ui-panel overflow-x-auto"><table class="ui-table"><thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Creado</th><th>Gestión</th></tr></thead>
                @forelse($usuarios as $usuario)<x-record-row :id="'usuario_'.$usuario->id" :columns="5" label="Editar usuario"><x-slot name="summary"><td>{{ $usuario->name }}{{ $usuario->is(auth()->user()) ? ' · tú' : '' }}</td><td>{{ $usuario->email }}</td><td>{{ $usuario->nombreRol() }}</td><td>{{ $usuario->created_at->format('d/m/Y') }}</td></x-slot>@include('usuarios._editar')</x-record-row>
                @empty<tbody><tr><td colspan="5">No se encontraron usuarios.</td></tr></tbody>@endforelse
            </table></section>
            <div class="px-4 sm:px-0">{{ $usuarios->links() }}</div>
        </div>
    </div>
</x-app-layout>