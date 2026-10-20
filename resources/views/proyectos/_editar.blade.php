@php
    $operacion = 'proyecto_'.$proyecto->id;
    $reintento = old('operacion') === $operacion;
    $valor = fn ($campo, $defecto = null) => $reintento ? old($campo, $defecto) : $defecto;
    $errors = $reintento ? $errors : new \Illuminate\Support\ViewErrorBag;
@endphp
            
                <form method="POST" action="{{ $proyecto ? route('proyectos.update', $proyecto) : route('proyectos.store') }}" class="space-y-4">
                    @csrf
<input type="hidden" name="operacion" value="{{ $operacion }}">
                    @if ($proyecto) @method('PUT') @endif
                    <div class="ui-form-grid">
                        <div>
                            <x-input-label for="titulo_{{ $proyecto->id }}" value="Título del proyecto" />
                            <x-text-input id="titulo_{{ $proyecto->id }}" name="titulo" class="mt-1 block w-full" :value="$valor('titulo', $proyecto?->titulo)" maxlength="255" required />
                            <x-input-error :messages="$errors->get('titulo')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="organismo_{{ $proyecto->id }}" value="Organismo" />
                            <x-text-input id="organismo_{{ $proyecto->id }}" name="organismo" list="organismos_{{ $proyecto->id }}" class="mt-1 block w-full" :value="$valor('organismo', $proyecto?->organismo)" maxlength="255" required />
                            <datalist id="organismos_{{ $proyecto->id }}">@foreach ($organismos as $organismo)<option value="{{ $organismo }}"></option>@endforeach</datalist>
                            <x-input-error :messages="$errors->get('organismo')" class="mt-2" />
                        </div>
                    </div>
                    <div class="ui-form-grid">
                        <div>
                            <x-input-label for="fecha_postulacion_{{ $proyecto->id }}" value="Fecha de postulación" />
                            <x-date-input id="fecha_postulacion_{{ $proyecto->id }}" name="fecha_postulacion" :value="$valor('fecha_postulacion', $proyecto?->fecha_postulacion?->toDateString() ?? now()->toDateString())" class="mt-1" required />
                            <x-input-error :messages="$errors->get('fecha_postulacion')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="estado_{{ $proyecto->id }}" value="Estado" />
                            <select id="estado_{{ $proyecto->id }}" name="estado" class="mt-1 block w-full" required>
                                @foreach (\App\Models\Proyecto::ESTADOS as $estado => $nombre)
                                    <option value="{{ $estado }}" @selected($valor('estado', $proyecto?->estado ?? 'postulado') === $estado)>{{ $nombre }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('estado')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="monto_{{ $proyecto->id }}" value="Monto (CLP, opcional)" />
                            <x-text-input id="monto_{{ $proyecto->id }}" name="monto" type="number" min="0" step="1" class="mt-1 block w-full" :value="$valor('monto', $proyecto?->monto)" />
                            <x-input-error :messages="$errors->get('monto')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="postulador_id_{{ $proyecto->id }}" value="Quién ingresó la postulación en la plataforma del organismo" />
                            <select id="postulador_id_{{ $proyecto->id }}" name="postulador_id" class="mt-1 block w-full" required>
                                @if ($proyecto)
                                    <option value="__conservar" @selected($valor('postulador_id', '__conservar') === '__conservar')>Conservar: {{ $proyecto->postulado_por }}</option>
                                @else
                                    <option value="">Seleccionar dirigente</option>
                                @endif
                                @foreach ($postuladores as $persona)
                                    <option value="{{ $persona->id }}" @selected((string) $valor('postulador_id') === (string) $persona->id)>{{ $persona->name }} · {{ $persona->nombreRol() }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Selecciona al dirigente que realizó la postulación. Quien registra el proyecto en JJVV Digital se guarda automáticamente.</p>
                            <x-input-error :messages="$errors->get('postulador_id')" class="mt-2" />
                        </div>
                    </div>
                    <div class="ui-form-actions"><button type="reset" class="ui-button ui-button-secondary" @click="$el.closest('details').open = false">Cancelar</button><x-primary-button>Guardar proyecto</x-primary-button></div>
                </form>
            
