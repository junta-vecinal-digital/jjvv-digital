<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white leading-tight">Editar datos de {{ $socio->nombre_completo }}</h2>
            <a href="{{ route('socios.show', $socio) }}" class="text-sm underline text-gray-600 dark:text-gray-400">← Volver a la ficha</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 ui-panel">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Cada modificación queda registrada con tu nombre y la fecha. El estado de la inscripción se cambia desde la ficha del socio.
                </p>

                <x-form-wizard :steps="['Datos del socio', 'Antecedentes']" :fields="[['nombre_completo','rut','email','direccion','telefono'],['residente_desde','carnet','boleta']]">
                    <form novalidate method="POST" action="{{ route('socios.actualizar', $socio) }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                        @csrf<input type="hidden" name="operacion" value="socio_{{ $socio->id }}">
                        @method('PUT')

                        @include('socios._datos', ['socio' => $socio])

                        <x-wizard-actions :cancel="route('socios.index')" />
                    </form>
                </x-form-wizard>
            </div>
        </div>
    </div>
</x-app-layout>
