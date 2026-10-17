<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white leading-tight">Usuarios del sistema</h2>
            <a href="{{ route('usuarios.create') }}"
               class="ui-button ui-button-primary">
                Crear usuario
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

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

            <div class="ui-panel overflow-x-auto">
                <table class="ui-table divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-left text-gray-600 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-3 font-medium">Nombre</th>
                            <th class="px-4 py-3 font-medium">Correo</th>
                            <th class="px-4 py-3 font-medium">Rol</th>
                            <th class="px-4 py-3 font-medium">Creado</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-200">
                        @forelse ($usuarios as $usuario)
                            <tr>
                                <td class="px-4 py-3">
                                    {{ $usuario->name }}
                                    @if ($usuario->is(auth()->user()))
                                        <span class="text-xs text-gray-500 dark:text-gray-400">(tú)</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $usuario->email }}</td>
                                <td class="px-4 py-3"><x-rol-usuario :rol="$usuario->role" /></td>
                                <td class="px-4 py-3">{{ $usuario->created_at->format('d-m-Y') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('usuarios.edit', $usuario) }}" class="underline text-brand-600 dark:text-brand-400">Editar</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No se encontraron usuarios.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 sm:px-0">{{ $usuarios->links() }}</div>
        </div>
    </div>
</x-app-layout>