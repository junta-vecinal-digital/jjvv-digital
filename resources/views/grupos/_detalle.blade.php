@php
    $operacion = 'grupo_'.$grupo->id;
    $valorFormulario = fn ($campo, $defecto = null) => old('operacion') === $operacion ? old($campo, $defecto) : $defecto;
    $errors = old('operacion') === $operacion ? $errors : new \Illuminate\Support\ViewErrorBag;
    $representante = $grupo->datosRepresentante();
@endphp
<h2 class="text-xl font-semibold">{{ $grupo->nombre }}</h2>
<dl class="ui-meta"><div><dt>Representante vigente</dt><dd>{{ $representante['nombre'] ?? 'Sin representante' }}</dd></div><div><dt>RUT</dt><dd>{{ $representante['rut'] ?? 'Sin registrar' }}</dd></div><div><dt>Correo del representante</dt><dd>{{ $representante['correo'] ?? 'Sin registrar' }}</dd></div><div><dt>Teléfono / dirección</dt><dd>{{ $representante['telefono'] ?? '' }} · {{ $representante['direccion'] ?? '' }}</dd></div><div><dt>Contacto de la agrupación</dt><dd>{{ $grupo->contacto_nombre }} · {{ $grupo->contacto_correo }}</dd></div><div><dt>Teléfono / dirección de contacto</dt><dd>{{ $grupo->contacto_telefono }} · {{ $grupo->contacto_direccion }}</dd></div></dl>
            <details class="ui-panel" @if (old('accion') === 'editar' && $errors->any()) open @endif>
                <summary class="cursor-pointer p-4 font-medium">Editar grupo</summary>
                @include('grupos._form', ['grupo' => $grupo])
            </details>

            {{-- Horarios fijos (RFU-34, RFS-47, RFS-49) --}}
            @php
                $claseSelect = 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-brand-500 dark:focus:border-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400 rounded-md shadow-sm';
            @endphp
            <div class="ui-panel p-6 space-y-6">
                <div>
                    <h3 class="font-medium text-gray-900 dark:text-gray-100">Horarios fijos</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Reservas que se repiten cada semana. Las reservas de cada mes se generan con el botón de más abajo.
                        Editar o eliminar un horario no cambia las reservas ya generadas.
                    </p>
                </div>

                @error('horario')
                    <div class="p-4 bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200 rounded-lg text-sm">
                        {{ $message }}
                    </div>
                @enderror

                @if (session('mensaje'))
                    <div class="p-4 bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 rounded-lg text-sm">
                        {{ session('mensaje') }}
                    </div>
                @endif

                @if (session('omitidas'))
                    <div class="p-4 bg-amber-100 dark:bg-amber-900/40 text-amber-900 dark:text-amber-100 rounded-lg text-sm">
                        <p class="font-medium">No se generaron estos días:</p>
                        <ul class="mt-1 list-disc ps-5">
                            @foreach (session('omitidas') as $omitida)
                                <li>{{ $omitida }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Horarios existentes --}}
                @forelse ($grupo->horariosFijos as $horario)
@php($editandoHorario = old('operacion') === $operacion && (string)old('horario_id') === (string)$horario->id)
                    <div class="flex flex-wrap items-end gap-3">
                        <form method="POST" action="{{ route('grupos.horarios.update', $horario) }}" class="flex flex-wrap items-end gap-3">
                            @csrf
<input type="hidden" name="operacion" value="{{ $operacion }}"><input type="hidden" name="accion" value="horario">
                            @method('PUT')<input type="hidden" name="horario_id" value="{{ $horario->id }}">
                            <select aria-label="Día del horario fijo" name="dia_semana" class="{{ $claseSelect }}">
                                @foreach (\App\Models\HorarioFijo::DIAS as $numero => $nombre)
                                    <option value="{{ $numero }}" @selected((int) ($editandoHorario ? old('dia_semana') : $horario->dia_semana) === $numero)>{{ $nombre }}</option>
                                @endforeach
                            </select>
                            <select aria-label="Desde" name="hora_inicio" class="{{ $claseSelect }}">
                                @foreach ($horas as $hora)
                                    <option value="{{ $hora }}" @selected(($editandoHorario ? old('hora_inicio') : substr($horario->hora_inicio, 0, 5)) === $hora)>{{ $hora }}</option>
                                @endforeach
                            </select>
                            <span class="pb-2 text-gray-500 dark:text-gray-400">a</span>
                            <select aria-label="Hasta" name="hora_fin" class="{{ $claseSelect }}">
                                @foreach ($horasFin as $hora)
                                    <option value="{{ $hora }}" @selected(($editandoHorario ? old('hora_fin') : substr($horario->hora_fin, 0, 5)) === $hora)>{{ $hora }}</option>
                                @endforeach
                            </select>
                            <x-secondary-button type="submit">Guardar</x-secondary-button>
                        </form>

                        <form method="POST" action="{{ route('grupos.horarios.destroy', $horario) }}"
                              onsubmit="return confirm('¿Eliminar este horario fijo? Las reservas ya generadas se mantienen.')">
                            @csrf
<input type="hidden" name="operacion" value="{{ $operacion }}"><input type="hidden" name="accion" value="horario">
                            @method('DELETE')
                            <button class="pb-2 underline text-sm text-red-600 dark:text-red-400">Eliminar</button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">Este grupo aún no tiene horarios fijos.</p>
                @endforelse

                <details @if(old('accion') === 'agregar-horario' && $errors->any()) open @endif>
                    <summary class="cursor-pointer font-medium">Agregar horario fijo</summary>
                @unless ($grupo->tieneRepresentanteVigente())
                    <p class="text-sm text-red-600 dark:text-red-400">
                        Asigna un representante vigente para agregar horarios y generar reservas.
                    </p>
                @endunless
                <form method="POST" action="{{ route('grupos.horarios.store', $grupo) }}"
                      class="flex flex-wrap items-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    @csrf
<input type="hidden" name="operacion" value="{{ $operacion }}"><input type="hidden" name="accion" value="agregar-horario">
                    <div>
                        <x-input-label for="nuevo_dia_grupo_{{ $grupo->id }}" value="Día" />
                        <select id="nuevo_dia_grupo_{{ $grupo->id }}" name="dia_semana" class="mt-1 block {{ $claseSelect }}" required>
                            @foreach (\App\Models\HorarioFijo::DIAS as $numero => $nombre)
                                <option value="{{ $numero }}" @selected(old('accion') === 'agregar-horario' && (string)$valorFormulario('dia_semana') === (string)$numero)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="nuevo_desde_grupo_{{ $grupo->id }}" value="Desde" />
                        <select id="nuevo_desde_grupo_{{ $grupo->id }}" name="hora_inicio" class="mt-1 block {{ $claseSelect }}" required>
                            @foreach ($horas as $hora)
                                <option value="{{ $hora }}" @selected(old('accion') === 'agregar-horario' && $valorFormulario('hora_inicio') === $hora)>{{ $hora }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="nuevo_hasta_grupo_{{ $grupo->id }}" value="Hasta" />
                        <select id="nuevo_hasta_grupo_{{ $grupo->id }}" name="hora_fin" class="mt-1 block {{ $claseSelect }}" required>
                            @foreach ($horasFin as $hora)
                                <option value="{{ $hora }}" @selected(old('accion') === 'agregar-horario' && $valorFormulario('hora_fin') === $hora)>{{ $hora }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-primary-button :disabled="! $grupo->tieneRepresentanteVigente()">Agregar horario</x-primary-button>
                </form>

                </details>

                {{-- Generar reservas del mes --}}
                <form method="POST" action="{{ route('grupos.horarios.generar', $grupo) }}"
                      class="flex flex-wrap items-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    @csrf
<input type="hidden" name="operacion" value="{{ $operacion }}"><input type="hidden" name="accion" value="horario">
                    <div>
                        <x-input-label for="mes_grupo_{{ $grupo->id }}" value="Generar reservas del mes" />
                        <select id="mes_grupo_{{ $grupo->id }}" name="mes" class="ui-input mt-1 block w-full sm:w-64" required>
                            @for ($adelanto = 0; $adelanto <= 6; $adelanto++)
                                @php($mesReserva = now()->startOfMonth()->addMonths($adelanto))
                                <option value="{{ $mesReserva->format('Y-m') }}" @selected($valorFormulario('mes', now()->startOfMonth()->addMonth()->format('Y-m')) === $mesReserva->format('Y-m'))>{{ ucfirst($mesReserva->translatedFormat('F Y')) }}</option>
                            @endfor
                        </select>
                    </div>
                    <x-primary-button :disabled="! $grupo->tieneRepresentanteVigente()">Generar reservas</x-primary-button>
                </form>
            </div>

