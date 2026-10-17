<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $proyecto ? 'Editar proyecto' : 'Registrar proyecto' }}</h1>
            <a href="{{ $proyecto ? route('proyectos.show', $proyecto) : route('proyectos.index') }}" class="text-sm underline">Volver</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="ui-panel p-6">
            <p class="mb-5 text-sm text-gray-500 dark:text-gray-400">Registra una postulación ya ingresada en la plataforma del organismo. Los demás usuarios podrán consultar el proyecto y su estado.</p>
            <x-form-wizard :steps="['Proyecto', 'Postulación']" :fields="[['titulo', 'organismo'], ['fecha_postulacion', 'estado', 'monto', 'postulador_id']]">
                <form novalidate method="POST" action="{{ $proyecto ? route('proyectos.update', $proyecto) : route('proyectos.store') }}" class="space-y-4">
                    @csrf
                    @if ($proyecto) @method('PUT') @endif
                    <x-form-step number="0">
                        <div>
                            <x-input-label for="titulo" value="Título del proyecto" />
                            <x-text-input id="titulo" name="titulo" class="mt-1 block w-full" :value="old('titulo', $proyecto?->titulo)" maxlength="255" required />
                            <x-input-error :messages="$errors->get('titulo')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="organismo" value="Organismo" />
                            <x-text-input id="organismo" name="organismo" list="organismos" class="mt-1 block w-full" :value="old('organismo', $proyecto?->organismo)" maxlength="255" required />
                            <datalist id="organismos">@foreach ($organismos as $organismo)<option value="{{ $organismo }}"></option>@endforeach</datalist>
                            <x-input-error :messages="$errors->get('organismo')" class="mt-2" />
                        </div>
                    </x-form-step>
                    <x-form-step number="1">
                        <div>
                            <x-input-label for="fecha_postulacion" value="Fecha de postulación" />
                            <x-date-input id="fecha_postulacion" name="fecha_postulacion" :value="old('fecha_postulacion', $proyecto?->fecha_postulacion?->toDateString() ?? now()->toDateString())" class="mt-1" required />
                            <x-input-error :messages="$errors->get('fecha_postulacion')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="estado" value="Estado" />
                            <select id="estado" name="estado" class="mt-1 block w-full" required>
                                @foreach (\App\Models\Proyecto::ESTADOS as $estado => $nombre)
                                    <option value="{{ $estado }}" @selected(old('estado', $proyecto?->estado ?? 'postulado') === $estado)>{{ $nombre }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('estado')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="monto" value="Monto (CLP, opcional)" />
                            <x-text-input id="monto" name="monto" type="number" min="0" step="1" class="mt-1 block w-full" :value="old('monto', $proyecto?->monto)" />
                            <x-input-error :messages="$errors->get('monto')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="postulador_id" value="Quién ingresó la postulación en la plataforma del organismo" />
                            <select id="postulador_id" name="postulador_id" class="mt-1 block w-full" required>
                                @if ($proyecto)
                                    <option value="__conservar" @selected(old('postulador_id', '__conservar') === '__conservar')>Conservar: {{ $proyecto->postulado_por }}</option>
                                @else
                                    <option value="">Seleccionar dirigente</option>
                                @endif
                                @foreach ($postuladores as $persona)
                                    <option value="{{ $persona->id }}" @selected((string) old('postulador_id') === (string) $persona->id)>{{ $persona->name }} · {{ $persona->nombreRol() }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Selecciona al dirigente que realizó la postulación. Quien registra el proyecto en JJVV Digital se guarda automáticamente.</p>
                            <x-input-error :messages="$errors->get('postulador_id')" class="mt-2" />
                        </div>
                    </x-form-step>
                    <x-wizard-actions />
                </form>
            </x-form-wizard>
        </div>
    </div>
</x-app-layout>
