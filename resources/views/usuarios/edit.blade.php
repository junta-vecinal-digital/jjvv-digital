<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white leading-tight">
                Editar usuario: {{ $usuario->name }}
            </h2>
            <a href="{{ route('usuarios.index') }}" class="text-sm underline text-gray-600 dark:text-gray-400">← Volver al listado</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 ui-panel">
                <x-form-wizard :steps="['Cuenta', 'Acceso']" :fields="[['name','email'],['role','password','password_confirmation']]">
                    <form novalidate method="POST" action="{{ route('usuarios.update', $usuario) }}" class="space-y-6" x-data="claveCuenta">
                        @csrf
                        @method('PATCH')

                        @include('usuarios._form', ['usuario' => $usuario])

                        <div class="space-y-4">
                            <x-wizard-actions />
                            <a href="{{ route('usuarios.index') }}" class="text-sm underline text-gray-600 dark:text-gray-400">Cancelar</a>
                        </div>
                    </form>
                </x-form-wizard>
            </div>
            @if (session('status') === 'dirigente-dado-de-baja')<p role="status" class="ui-panel mt-4 p-4">Ficha del dirigente dada de baja. Conserva los permisos operativos de su cargo.</p>@endif
            @if ($usuario->esDirigente() && $usuario->socio?->estado === 'activo')
                <section class="ui-panel mt-6 p-6 space-y-4">
                    <h2 class="font-semibold">Dar de baja la ficha de socio del dirigente</h2>
                    <p class="text-sm">Retira sus accesos personales de socio y conserva los permisos de su cargo. No elimina su cuenta.</p>
                    <form method="POST" action="{{ route('usuarios.baja', $usuario) }}" class="space-y-4" onsubmit="return confirm('¿Dar de baja la ficha de este dirigente?');">
                        @csrf
                        <x-input-label for="motivo_baja" value="Motivo de baja" />
                        <textarea id="motivo_baja" name="motivo_baja" maxlength="500" required class="w-full">{{ old('motivo_baja') }}</textarea>
                        <x-input-error :messages="$errors->get('motivo_baja')" />
                        <x-input-label for="fecha_baja" value="Fecha de baja" />
                        <x-date-input id="fecha_baja" name="fecha_baja" :value="old('fecha_baja', now()->toDateString())" :max="now()->toDateString()" required />
                        <x-input-error :messages="$errors->get('fecha_baja')" />
                        <x-danger-button>Dar de baja ficha</x-danger-button>
                    </form>
                </section>
            @endif
            @if (! $usuario->is(auth()->user()))
                <section class="ui-panel mt-6 p-6 space-y-4">
                    <h2 class="font-semibold">Eliminar cuenta de usuario</h2>
                    <p class="text-sm">La ficha de socio y los registros históricos se conservan. La cuenta pierde el acceso al sistema.</p>
                    <form method="POST" action="{{ route('usuarios.destroy', $usuario) }}" class="space-y-4" onsubmit="return confirm('¿Eliminar esta cuenta de usuario?');">
                        @csrf @method('DELETE')
                        <label class="flex items-center gap-2"><input type="checkbox" name="confirmar_eliminacion" value="1" required> Confirmo que deseo eliminar esta cuenta.</label>
                        <x-input-error :messages="$errors->get('confirmar_eliminacion')" />
                        <x-danger-button>Eliminar cuenta</x-danger-button>
                    </form>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
