@php
    $esUnoMismo = $usuario && $usuario->is(auth()->user());
    $rolActual = old('role', $usuario?->role ?? 'vecino');
@endphp

<x-form-step number="0">
    <div>
        <x-input-label for="name" value="Nombre completo" />
        <x-text-input id="name" name="name" autocomplete="off" type="text" class="mt-1 block w-full"
            :value="old('name', $usuario?->name)" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="email" value="Correo electrónico" />
        <x-text-input id="email" name="email" autocomplete="off" type="email" class="mt-1 block w-full"
            :value="old('email', $usuario?->email)" required />
        <x-input-error class="mt-2" :messages="$errors->get('email')" />
    </div>

</x-form-step>
<x-form-step number="1">
    <div>
        <x-input-label for="role" value="Rol" />
        <select id="role" name="role" @disabled($esUnoMismo)
            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-brand-500 dark:focus:border-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400 rounded-md shadow-sm disabled:opacity-60">
            @foreach (\App\Models\User::ROLES as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected($rolActual === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>

        {{-- Un campo deshabilitado no se envía, por eso se repite como oculto --}}
        @if ($esUnoMismo)
            <input type="hidden" name="role" value="{{ $usuario->role }}">
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">No puedes cambiar tu propio rol.</p>
        @else
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                El rol define a qué secciones accede la persona. El administrador solo gestiona usuarios y roles; no accede a fichas de socios ni a finanzas.
            </p>
        @endif

        <x-input-error class="mt-2" :messages="$errors->get('role')" />
    </div>

    <div class="flex flex-wrap gap-3">
        <x-secondary-button @click="generarClave()">Generar contraseña</x-secondary-button>
    </div>
    <div>
        <x-input-label for="password" :value="$usuario ? 'Nueva contraseña (opcional)' : 'Contraseña temporal'" />
        <div class="mt-1 flex items-center gap-2">
            <x-text-input id="password" name="password" type="password" x-model="clave" x-bind:type="mostrarClave ? 'text' : 'password'" class="min-w-0 flex-1"
                autocomplete="new-password" :required="! $usuario" />
            <x-secondary-button class="shrink-0" @click="mostrarClave = !mostrarClave" x-bind:aria-pressed="mostrarClave" aria-controls="password password_confirmation" x-text="mostrarClave ? 'Ocultar' : 'Mostrar'">Mostrar</x-secondary-button>
        </div>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            @if ($usuario)
                Déjala en blanco para mantener la contraseña actual.
            @else
                Entrégasela a la persona de forma segura y pídele que la cambie desde su perfil.
            @endif
        </p>
        <x-input-error class="mt-2" :messages="$errors->get('password')" />
    </div>

    <div>
        <x-input-label for="password_confirmation" value="Confirmar contraseña" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" x-model="confirmacion" x-bind:type="mostrarClave ? 'text' : 'password'" class="mt-1 block w-full"
            autocomplete="new-password" :required="! $usuario" />
    </div>
</x-form-step>
