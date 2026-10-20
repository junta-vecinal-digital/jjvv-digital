@php
    $operacion = $usuario ? 'usuario_'.$usuario->id : 'usuario_nuevo';
    $valor = fn ($campo, $defecto = null) => (! $usuario || old('operacion') === $operacion) ? old($campo, $defecto) : $defecto;
    $errors = (! $usuario || old('operacion') === $operacion) ? $errors : new \Illuminate\Support\ViewErrorBag;
    $esUnoMismo = $usuario && $usuario->is(auth()->user());
    $rolActual = $valor('role', $usuario?->role ?? 'vecino');
@endphp

<div class="ui-form-grid">
    <div>
        <x-input-label for="name_{{ $usuario?->id ?? 'nuevo' }}" value="Nombre completo" />
        <x-text-input id="name_{{ $usuario?->id ?? 'nuevo' }}" name="name" autocomplete="off" type="text" class="mt-1 block w-full"
            :value="$valor('name', $usuario?->name)" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="email_{{ $usuario?->id ?? 'nuevo' }}" value="Correo electrónico" />
        <x-text-input id="email_{{ $usuario?->id ?? 'nuevo' }}" name="email" autocomplete="off" type="email" class="mt-1 block w-full"
            :value="$valor('email', $usuario?->email)" required />
        <x-input-error class="mt-2" :messages="$errors->get('email')" />
    </div>

</div>
<div class="ui-form-grid mt-6">
    <div>
        <x-input-label for="role_{{ $usuario?->id ?? 'nuevo' }}" value="Rol" />
        <select id="role_{{ $usuario?->id ?? 'nuevo' }}" name="role" @disabled($esUnoMismo)
            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-brand-500 dark:focus:border-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400 rounded-md shadow-sm disabled:opacity-60">
            @foreach (\App\Models\User::ROLES as $claveRol => $etiqueta)
                <option value="{{ $claveRol }}" @selected($rolActual === $claveRol)>{{ $etiqueta }}</option>
            @endforeach
        </select>

        {{-- Un campo deshabilitado no se envía, por eso se repite como oculto --}}
        @if ($esUnoMismo)
            <input type="hidden" name="role" value="{{ $usuario->role }}">
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">No puedes cambiar tu propio rol.</p>
        @else
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                El rol define a qué secciones accede la persona. El administrador gestiona cuentas, parámetros y catálogos; la baja de ficha de un dirigente es una operación específica.
            </p>
        @endif

        <x-input-error class="mt-2" :messages="$errors->get('role')" />
    </div>

    <div class="flex flex-wrap gap-3">
        <x-secondary-button @click="generarClave()">Generar contraseña</x-secondary-button>
    </div>
    <div>
        <x-input-label for="password_{{ $usuario?->id ?? 'nuevo' }}" :value="$usuario ? 'Nueva contraseña (opcional)' : 'Contraseña temporal'" />
        <div class="mt-1 flex items-center gap-2">
            <x-password-input id="password_{{ $usuario?->id ?? 'nuevo' }}" name="password" type="password" x-model="clave" class="min-w-0 flex-1"
                autocomplete="new-password" :required="! $usuario" />
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
        <x-input-label for="password_confirmation_{{ $usuario?->id ?? 'nuevo' }}" value="Confirmar contraseña" />
        <x-password-input id="password_confirmation_{{ $usuario?->id ?? 'nuevo' }}" name="password_confirmation" type="password" x-model="confirmacion" class="mt-1 block w-full"
            autocomplete="new-password" :required="! $usuario" />
    </div>
</div>
