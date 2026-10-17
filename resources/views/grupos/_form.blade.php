@php
    $esEdicion = $grupo !== null;
    $contactoDelRepresentante = ! $grupo || (
        $grupo->contacto_nombre === $grupo->representante?->nombre_completo
        && $grupo->contacto_correo === $grupo->representante?->email
        && $grupo->contacto_telefono === $grupo->representante?->telefono
        && $grupo->contacto_direccion === $grupo->representante?->direccion
    );
    $paso = $errors->hasAny(['contacto_nombre', 'contacto_correo', 'contacto_telefono', 'contacto_direccion'])
        ? 2 : ($errors->has('socio_id') ? 1 : 0);
    $estado = [
        'paso' => $paso,
        'nombre' => old('nombre', $grupo?->nombre ?? ''),
        'socioId' => (string) old('socio_id', $grupo?->representante_id ?? ''),
        'usarContacto' => (bool) old('usar_contacto_representante', $contactoDelRepresentante),
        'contacto' => [
            'nombre' => old('contacto_nombre', $grupo?->contacto_nombre ?? ''),
            'correo' => old('contacto_correo', $grupo?->contacto_correo ?? ''),
            'telefono' => old('contacto_telefono', $grupo?->contacto_telefono ?? ''),
            'direccion' => old('contacto_direccion', $grupo?->contacto_direccion ?? ''),
        ],
    ];
    $contactos = $socios->mapWithKeys(fn ($socio) => [(string) $socio->id => [
        'nombre' => $socio->nombre_completo,
        'correo' => $socio->email,
        'telefono' => $socio->telefono,
        'direccion' => $socio->direccion,
    ]]);
@endphp

<section class="ui-panel overflow-hidden" x-data="grupoWizard(@js($estado), @js($contactos))">
    <div class="border-b border-gray-200 p-6 dark:border-gray-800">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $esEdicion ? 'Editar grupo' : 'Crear grupo' }}</h3>
        <ol class="mt-4 flex flex-wrap gap-4 text-sm" aria-label="Pasos del formulario">
            @foreach (['Grupo', 'Representante', 'Contacto'] as $numero => $etiqueta)
                <li class="flex items-center gap-2" :aria-current="paso === {{ $numero }} ? 'step' : null">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full"
                        :class="paso === {{ $numero }} ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'">{{ $numero + 1 }}</span>
                    <span>{{ $etiqueta }}</span>
                </li>
            @endforeach
        </ol>
        <p x-ref="tituloPaso" tabindex="-1" class="mt-4 font-medium text-gray-900 outline-none dark:text-white" aria-live="polite"
            x-text="['Nombre del grupo', 'Elegir representante', 'Revisar y aceptar'][paso]"></p>
    </div>

    <form x-ref="formulario" method="POST" action="{{ $esEdicion ? route('grupos.update', $grupo) : route('grupos.store') }}"
        novalidate @submit="confirmar($event)">
        @csrf
        @if ($esEdicion)
            @method('PUT')
        @endif
        <input type="hidden" name="usar_contacto_representante" :value="usarContacto ? '1' : '0'">

        <div class="flex transition-transform duration-300 ease-out motion-reduce:transition-none"
            :style="{ transform: 'translateX(-' + (paso * 100) + '%)' }">
            <fieldset data-paso="0" :inert="paso !== 0" :aria-hidden="paso !== 0" class="w-full min-w-0 shrink-0 space-y-4 p-6">
                <legend class="sr-only">Nombre del grupo</legend>
                <div>
                    <x-input-label for="grupo_nombre" value="Nombre del grupo" />
                    <x-text-input id="grupo_nombre" name="nombre" x-model="nombre" type="text" maxlength="255" required class="mt-1 block w-full" autocomplete="off" />
                    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Los horarios de la sede se configuran con día y hora después de guardar el grupo.</p>
            </fieldset>

            <fieldset data-paso="1" :inert="paso !== 1" :aria-hidden="paso !== 1" class="w-full min-w-0 shrink-0 space-y-4 p-6">
                <legend class="sr-only">Representante del grupo</legend>
                <div>
                    <x-input-label for="grupo_socio" value="Socio activo con cuenta" />
                    <select id="grupo_socio" name="socio_id" x-model="socioId" @change="elegirRepresentante()" class="mt-1 block w-full">
                        <option value="">Asignar después</option>
                        @foreach ($socios as $socio)
                            <option value="{{ $socio->id }}">{{ $socio->nombre_completo }} · {{ $socio->rut }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('socio_id')" class="mt-2" />
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Sus datos de contacto se completarán automáticamente.</p>
                <p x-show="!socioId" class="text-sm text-amber-700 dark:text-amber-400">Sin representante no podrás agregar horarios ni generar reservas.</p>
            </fieldset>

            <fieldset data-paso="2" :inert="paso !== 2" :aria-hidden="paso !== 2" class="w-full min-w-0 shrink-0 space-y-4 p-6">
                <legend class="sr-only">Contacto y confirmación</legend>
                <p class="font-medium text-gray-900 dark:text-white" x-text="nombre"></p>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" x-model="usarContacto" @change="if (usarContacto) copiarContacto()" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                    Usar datos del representante
                </label>
                <p class="text-sm text-gray-500 dark:text-gray-400">Desmarca la opción para editar el contacto del grupo. La ficha del socio no cambiará.</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach (['nombre' => ['Nombre', 'text', 255], 'correo' => ['Correo', 'email', 255], 'telefono' => ['Teléfono', 'tel', 20], 'direccion' => ['Dirección', 'text', 255]] as $campo => [$etiqueta, $tipo, $maximo])
                        <div>
                            <x-input-label :for="'grupo_contacto_'.$campo" :value="$etiqueta" />
                            <x-text-input :id="'grupo_contacto_'.$campo" :name="'contacto_'.$campo" :type="$tipo" :maxlength="$maximo"
                                x-model="contacto.{{ $campo }}" x-bind:readonly="usarContacto" class="mt-1 block w-full read-only:bg-gray-50 read-only:text-gray-500 dark:read-only:bg-gray-800 dark:read-only:text-gray-400" />
                            <x-input-error :messages="$errors->get('contacto_'.$campo)" class="mt-2" />
                        </div>
                    @endforeach
                </div>
            </fieldset>
        </div>

        <div class="flex items-center justify-between gap-3 border-t border-gray-200 p-6 dark:border-gray-800">
            <x-secondary-button x-bind:disabled="paso === 0 || guardando" @click="paso--; $nextTick(() => $refs.tituloPaso.focus())">Anterior</x-secondary-button>
            <x-primary-button type="button" x-show="paso < 2" @click="avanzar()">Siguiente</x-primary-button>
            <x-primary-button x-cloak x-show="paso === 2" x-bind:disabled="guardando">Aceptar</x-primary-button>
        </div>
    </form>
</section>
