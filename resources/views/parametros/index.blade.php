<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white leading-tight">Parámetros del sistema</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="ui-panel p-6 ">
                @if (session('status') === 'parametros-guardados')
                    <p class="mb-4 text-green-700 dark:text-green-400">Parámetros guardados.</p>
                @endif

                <form method="POST" action="{{ route('parametros.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    @foreach ($parametros as $parametro)
                        <div>
                            <x-input-label for="valores_{{ $parametro->clave }}" :value="$parametro->clave === 'certificado_residencia_valor' ? 'Aporte voluntario sugerido para certificado de residencia (CLP)' : $parametro->etiqueta" />
                            <x-text-input
                                id="valores_{{ $parametro->clave }}"
                                name="valores[{{ $parametro->clave }}]"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('valores.'.$parametro->clave, $parametro->valor)"
                            />
                            <x-input-error :messages="$errors->get('valores.'.$parametro->clave)" class="mt-2" />
                        </div>
                    @endforeach

                    <x-primary-button>Guardar</x-primary-button>
                </form>
            </div>
            <section class="ui-panel mt-6 space-y-4 p-6">
                <h3 class="text-lg font-semibold">Tipos de actividad gratuita</h3>
                <p class="text-sm">Los tipos nuevos son gratuitos. Desactivar un tipo lo oculta para nuevas reservas manuales; las reservas anteriores y los horarios fijos se conservan. El aporte por una reserva de sede lo define presidente o secretario en cada operación.</p>
                @if (session('status') === 'actividad-guardada')
                    <p class="text-green-700 dark:text-green-400">Actividad guardada.</p>
                @endif
                <x-input-error :messages="$errors->get('nombre_actividad')" />
                <x-input-error :messages="$errors->get('activo')" />
                @foreach ($tiposActividad as $tipoActividad)
                    <form method="POST" action="{{ route('parametros.actividades.update', $tipoActividad) }}" class="flex flex-wrap items-end gap-3 border-b border-gray-200 pb-4 dark:border-gray-700">
                        @csrf
                        @method('PUT')
                        <div class="min-w-0 flex-1">
                            <x-input-label :for="'actividad_'.$tipoActividad->id" value="Nombre de actividad" />
                            <x-text-input :id="'actividad_'.$tipoActividad->id" name="nombre_actividad" :value="$tipoActividad->nombre" maxlength="120" required class="w-full" />
                        </div>
                        <label class="flex items-center gap-2 py-2">
                            <input type="hidden" name="activo" value="0">
                            <input type="checkbox" name="activo" value="1" @checked($tipoActividad->activo)> Activa
                        </label>
                        <x-secondary-button type="submit">Guardar actividad</x-secondary-button>
                    </form>
                @endforeach
                <form method="POST" action="{{ route('parametros.actividades.store') }}" class="space-y-3">
                    @csrf
                    <x-input-label for="nueva_actividad" value="Nueva actividad gratuita" />
                    <x-text-input id="nueva_actividad" name="nombre_actividad" :value="old('nombre_actividad')" maxlength="120" required class="w-full" />
                    <x-primary-button>Agregar actividad</x-primary-button>
                </form>
            </section>
            @include('parametros._insumos')
        </div>
    </div>
</x-app-layout>
