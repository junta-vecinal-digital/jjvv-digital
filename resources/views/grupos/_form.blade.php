@php
    $operacion = 'grupo_'.($grupo?->id ?? 'nuevo');
    $valorFormulario = fn ($campo, $defecto = null) => old('operacion') === $operacion ? old($campo, $defecto) : $defecto;
    $errors = old('operacion') === $operacion ? $errors : new \Illuminate\Support\ViewErrorBag;

    $esEdicion = $grupo !== null;
    $representante = $grupo?->datosRepresentante() ?? ['nombre' => '', 'rut' => '', 'correo' => '', 'telefono' => '', 'direccion' => ''];
    $origen = $grupo?->representante_id ? 'socio:'.$grupo->representante_id : ($grupo?->representante_usuario_id ? 'usuario:'.$grupo->representante_usuario_id : 'manual');
    if (! isset($personas[$origen])) $origen = 'manual';
    if (! $grupo?->tieneRepresentanteVigente()) $origen = 'sin_representante';
    $contactoDelRepresentante = ! $grupo || collect($representante)->except('rut')->every(fn ($valor, $campo) => $grupo->{'contacto_'.$campo} === $valor);
    $paso = $errors->hasAny(['contacto_nombre', 'contacto_correo', 'contacto_telefono', 'contacto_direccion'])
        ? 2 : ($errors->hasAny(['origen_representante', 'representante_nombre', 'representante_rut', 'representante_correo', 'representante_telefono', 'representante_direccion']) ? 1 : 0);
    $estado = [
        'paso' => $paso,
        'nombre' => $valorFormulario('nombre', $grupo?->nombre ?? ''),
        'origen' => $valorFormulario('origen_representante', $origen),
        'representante' => collect($representante)->mapWithKeys(fn ($valor, $campo) => [$campo => $valorFormulario('representante_'.$campo, $valor ?? '')])->all(),
        'usarContacto' => (bool) $valorFormulario('usar_contacto_representante', $contactoDelRepresentante),
        'contacto' => [
            'nombre' => $valorFormulario('contacto_nombre', $grupo?->contacto_nombre ?? ''),
            'correo' => $valorFormulario('contacto_correo', $grupo?->contacto_correo ?? ''),
            'telefono' => $valorFormulario('contacto_telefono', $grupo?->contacto_telefono ?? ''),
            'direccion' => $valorFormulario('contacto_direccion', $grupo?->contacto_direccion ?? ''),
        ],
    ];
@endphp

<section class="ui-panel overflow-hidden" x-data="grupoWizard(@js($estado), @js($personas))">
    <div class="border-b border-gray-200 p-6 dark:border-gray-800">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $esEdicion ? 'Editar grupo' : 'Crear grupo' }}</h3>
        <ol class="mt-4 flex flex-wrap gap-4 text-sm" aria-label="Pasos del formulario">
            @foreach (['Grupo', 'Representante', 'Contacto', 'Resumen'] as $numero => $etiqueta)
                <li class="flex items-center gap-2" :aria-current="paso === {{ $numero }} ? 'step' : null">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full"
                        :class="paso === {{ $numero }} ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'">{{ $numero + 1 }}</span>
                    <span>{{ $etiqueta }}</span>
                </li>
            @endforeach
        </ol>
        <p x-ref="tituloPaso" tabindex="-1" class="mt-4 font-medium text-gray-900 outline-none dark:text-white" aria-live="polite"
            x-text="['Nombre del grupo', 'Elegir representante', 'Datos de contacto', 'Revisar y aceptar'][paso]"></p>
    </div>

    <form x-ref="formulario" method="POST" action="{{ $esEdicion ? route('grupos.update', $grupo) : route('grupos.store') }}"
        novalidate @submit="confirmar($event)">
        @csrf
<input type="hidden" name="operacion" value="{{ $operacion }}"><input type="hidden" name="accion" value="editar">
        @if ($esEdicion)
            @method('PUT')
        @endif
        <input type="hidden" name="usar_contacto_representante" :value="usarContacto ? '1' : '0'">

        <div class="flex transition-transform duration-300 ease-out motion-reduce:transition-none"
            :style="{ transform: 'translateX(-' + (paso * 100) + '%)' }">
            <fieldset data-paso="0" :inert="paso !== 0" :aria-hidden="paso !== 0" class="w-full min-w-0 shrink-0 space-y-4 p-6">
                <legend class="sr-only">Nombre del grupo</legend>
                <div>
                    <x-input-label for="grupo_nombre_{{ $grupo?->id ?? 'nuevo' }}" value="Nombre del grupo" />
                    <x-text-input id="grupo_nombre_{{ $grupo?->id ?? 'nuevo' }}" name="nombre" x-model="nombre" type="text" maxlength="255" required class="mt-1 block w-full" autocomplete="off" />
                    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Los horarios de la sede se configuran con día y hora después de guardar el grupo.</p>
            </fieldset>

            <fieldset data-paso="1" :inert="paso !== 1" :aria-hidden="paso !== 1" class="w-full min-w-0 shrink-0 space-y-4 p-6">
                <legend class="sr-only">Representante del grupo</legend>
                <div>
                    <x-input-label for="grupo_persona_{{ $grupo?->id ?? 'nuevo' }}" value="Representante" />
                    <select id="grupo_persona_{{ $grupo?->id ?? 'nuevo' }}" name="origen_representante" x-model="origen" @change="elegirRepresentante()" class="mt-1 block w-full">
                        <option value="sin_representante">Asignar después / retirar representante</option>
                        <option value="manual">Ingresar datos manualmente</option>
                        @foreach ($personas as $clave => $persona)
                            <option value="{{ $clave }}">{{ $persona['etiqueta'] }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('origen_representante')" class="mt-2" />
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Selecciona una persona para autocompletar o registra a cualquier persona sin crearle una ficha ni cuenta.</p>
                <div x-show="origen !== 'sin_representante'" class="grid gap-4 sm:grid-cols-2">
                    @foreach (['nombre' => ['Nombre', 'text', 255], 'rut' => ['RUT', 'text', 12], 'correo' => ['Correo', 'email', 255], 'telefono' => ['Teléfono', 'tel', 20], 'direccion' => ['Dirección', 'text', 255]] as $campo => [$etiqueta, $tipo, $maximo])
                        <div>
                            <x-input-label :for="'representante_'.$campo.'_'.$operacion" :value="$etiqueta.' del representante'" />
                            <x-text-input :id="'representante_'.$campo.'_'.$operacion" :name="'representante_'.$campo" :type="$tipo" :maxlength="$maximo"
                                x-model="representante.{{ $campo }}" x-bind:disabled="origen === 'sin_representante'" x-bind:required="origen !== 'sin_representante' && @js(in_array($campo, ['nombre', 'rut']))" @input="if (usarContacto) copiarContacto()" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('representante_'.$campo)" class="mt-2" />
                        </div>
                    @endforeach
                </div>
                <p x-show="origen === 'sin_representante'" class="text-sm text-amber-700 dark:text-amber-400">Sin representante no podrás agregar horarios ni generar reservas.</p>
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
                            <x-input-label :for="'grupo_contacto_'.$campo.'_'.$operacion" :value="$etiqueta.' de contacto'" />
                            <x-text-input :id="'grupo_contacto_'.$campo.'_'.$operacion" :name="'contacto_'.$campo" :type="$tipo" :maxlength="$maximo"
                                x-model="contacto.{{ $campo }}" x-bind:readonly="usarContacto" class="mt-1 block w-full read-only:bg-gray-50 read-only:text-gray-500 dark:read-only:bg-gray-800 dark:read-only:text-gray-400" />
                            <x-input-error :messages="$errors->get('contacto_'.$campo)" class="mt-2" />
                        </div>
                    @endforeach
                </div>
            </fieldset>
            <fieldset data-paso="3" :inert="paso !== 3" :aria-hidden="paso !== 3" class="w-full min-w-0 shrink-0 space-y-4 p-6">
                <legend class="sr-only">Resumen</legend>
                <p>Revisa los datos antes de aceptar. Puedes volver con Anterior para corregirlos.</p>
                <x-form-summary />
            </fieldset>
        </div>

        <div class="flex items-center justify-between gap-3 border-t border-gray-200 p-6 dark:border-gray-800">
            <span><x-secondary-button x-show="paso > 0" x-bind:disabled="guardando" @click="paso--; $nextTick(() => $refs.tituloPaso.focus())">Anterior</x-secondary-button>@if($esEdicion)<x-secondary-button x-show="paso === 0" @click="$el.closest('details').open = false">Cancelar</x-secondary-button>@else<a x-show="paso === 0" href="{{ route('grupos.index') }}" class="ui-button ui-button-secondary">Cancelar</a>@endif</span>
            <x-primary-button type="button" x-show="paso < 3" @click="avanzar()">Continuar</x-primary-button>
            <x-primary-button x-cloak x-show="paso === 3" x-bind:disabled="guardando">Guardar agrupación</x-primary-button>
        </div>
    </form>
</section>
