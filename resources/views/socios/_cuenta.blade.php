@if (session('status') === 'cuenta-creada')
    <div class="p-4 bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 rounded-lg text-sm">
        La cuenta fue creada. El socio ya puede ingresar con el correo y la contraseña que definiste.
    </div>
@endif

@if ($socio->user)
    <div class="p-6 ui-panel">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">Cuenta de usuario</h3>
        <p class="text-sm text-gray-700 dark:text-gray-300">Correo de acceso: {{ $socio->user->email }}</p>
    </div>
@elseif ($socio->estado !== 'rechazado')
    <div class="p-6 ui-panel">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">Crear cuenta de usuario</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            Para socios registrados en ventanilla. Define el correo y una contraseña inicial de al menos 10 caracteres.
            El socio puede cambiarla después desde su perfil.
        </p>

        <form method="POST" action="{{ route('socios.cuenta', $socio) }}" class="space-y-4">
            @csrf

            <div>
                <x-input-label for="cuenta_email" value="Correo de acceso" />
                <x-text-input id="cuenta_email" name="email" type="email" class="mt-1 block w-full"
                              :value="old('email', $socio->email)" required />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="cuenta_password" value="Contraseña inicial" />
                <x-text-input id="cuenta_password" name="password" type="password" class="mt-1 block w-full" required />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="cuenta_password_confirmation" value="Repite la contraseña" />
                <x-text-input id="cuenta_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required />
            </div>

            <x-primary-button>Crear cuenta</x-primary-button>
        </form>
    </div>
@endif