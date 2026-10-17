<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white leading-tight">Crear usuario</h2>
            <a href="{{ route('usuarios.index') }}" class="text-sm underline text-gray-600 dark:text-gray-400">← Volver al listado</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 ui-panel">
                <x-form-wizard :steps="['Cuenta', 'Acceso']" :fields="[['name','email'],['role','password','password_confirmation']]">
                    <form novalidate method="POST" action="{{ route('usuarios.store') }}" class="space-y-6" x-data="claveCuenta">
                        @csrf

                        @include('usuarios._form', ['usuario' => null])

                        <div class="space-y-4">
                            <x-wizard-actions />
                            <a href="{{ route('usuarios.index') }}" class="text-sm underline text-gray-600 dark:text-gray-400">Cancelar</a>
                        </div>
                    </form>
                </x-form-wizard>
            </div>
        </div>
    </div>
</x-app-layout>
