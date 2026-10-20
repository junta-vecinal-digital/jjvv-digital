@php
    $operacion = 'socio_'.$socio->id;
    $reintento = old('operacion') === $operacion;
    $valor = fn ($campo, $defecto = null) => $reintento ? old($campo, $defecto) : $defecto;
    $errors = $reintento ? $errors : new \Illuminate\Support\ViewErrorBag;
@endphp
<h2 class="text-xl font-semibold">{{ $socio->nombre_completo }} <x-representante-badge :socio="$socio" /></h2>
<details @if(old('operacion') === 'socio_'.$socio->id && old('accion') === 'cuenta') open @endif><summary>Cuenta de usuario</summary>@include('socios._cuenta', ['socio' => $socio])</details>
            
            @if ($errors->has('estado'))
                <div class="p-4 bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200 rounded-lg text-sm">{{ $errors->first('estado') }}</div>
            @endif

            <div class="p-6 ui-panel">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Datos del solicitante</h3>
                    <x-estado-socio :estado="$socio->estado" />
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div><dt class="text-gray-500 dark:text-gray-400">RUT</dt><dd class="text-gray-900 dark:text-gray-100">{{ $socio->rut }}</dd></div>
                    <div><dt class="text-gray-500 dark:text-gray-400">Teléfono</dt><dd class="text-gray-900 dark:text-gray-100">{{ $socio->telefono }}</dd></div>
                    <div><dt class="text-gray-500 dark:text-gray-400">Correo</dt><dd class="text-gray-900 dark:text-gray-100">{{ $socio->email ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500 dark:text-gray-400">Dirección</dt><dd class="text-gray-900 dark:text-gray-100">{{ $socio->direccion }}</dd></div>
                    <div><dt class="text-gray-500 dark:text-gray-400">Reside en el sector desde</dt><dd class="text-gray-900 dark:text-gray-100">{{ $socio->residente_desde->format('d/m/Y') }}</dd></div>
                    <div><dt class="text-gray-500 dark:text-gray-400">Solicitud recibida</dt><dd class="text-gray-900 dark:text-gray-100">{{ $socio->created_at->format('d/m/Y H:i') }}</dd></div>
                    @if ($socio->fecha_ingreso)
                        <div><dt class="text-gray-500 dark:text-gray-400">Fecha de ingreso</dt><dd class="text-gray-900 dark:text-gray-100">{{ $socio->fecha_ingreso->format('d/m/Y') }}</dd></div>
                    @endif
                    @if ($socio->revisor)
                        <div><dt class="text-gray-500 dark:text-gray-400">Última revisión por</dt><dd class="text-gray-900 dark:text-gray-100">{{ $socio->revisor->name }}</dd></div>
                    @endif
                    @if ($socio->observaciones)
                        <div class="sm:col-span-2"><dt class="text-gray-500 dark:text-gray-400">Observaciones</dt><dd class="text-gray-900 dark:text-gray-100">{{ $socio->observaciones }}</dd></div>
                    @endif
                    @if ($socio->estado === 'inactivo')
                        <div><dt class="text-gray-500 dark:text-gray-400">Fecha de baja</dt><dd class="text-gray-900 dark:text-gray-100">{{ $socio->fecha_baja?->format('d/m/Y') }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-gray-500 dark:text-gray-400">Motivo de la baja</dt><dd class="text-gray-900 dark:text-gray-100">{{ $socio->motivo_baja }}</dd></div>
                    @endif
                </dl>
            </div>

            <div class="p-6 ui-panel">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Documentos adjuntos</h3>
                <ul class="space-y-2 text-sm">
                    @if ($socio->carnet_path)
                        <li><a href="{{ route('socios.documento', [$socio, 'carnet']) }}" target="_blank" class="underline text-brand-600 dark:text-brand-400">Ver carnet de identidad</a></li>
                    @endif
                    @if ($socio->boleta_path)
                        <li><a href="{{ route('socios.documento', [$socio, 'boleta']) }}" target="_blank" class="underline text-brand-600 dark:text-brand-400">Ver boleta de servicio básico</a></li>
                    @endif
                    @unless ($socio->carnet_path || $socio->boleta_path)
                        <li class="text-gray-500 dark:text-gray-400">No hay documentos digitales adjuntos.</li>
                    @endunless
                </ul>
            </div>
            
            @if ($socio->estado === 'por_firmar')
                <div class="p-6 ui-panel">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Firma del libro de socios</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        @if ($socio->cita)
                            Hora reservada:
                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                {{ ucfirst($socio->cita->inicio->translatedFormat('l d \d\e F, H:i')) }} hrs
                            </span>
                        @else
                            Esta persona aún no ha reservado una hora.
                        @endif
                    </p>
                </div>
            @endif

            @if ($socio->user_id === auth()->id())
                <p class="ui-panel p-4">Otro presidente o secretario debe resolver tu ficha. No puedes aprobar tu propia inscripción.</p>
            @endif
            @if ($socio->user_id !== auth()->id() && in_array($socio->estado, ['pendiente', 'por_firmar']))
                <div class="p-6 ui-panel">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">Resolución</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        @if ($socio->estado === 'pendiente')
                            Verifica que el carnet y la boleta coincidan con los datos ingresados y que la dirección pertenezca al sector.
                        @else
                            Confirma solo cuando la persona ya haya firmado el libro de registro de socios.
                        @endif
                    </p>

                    <form method="POST" action="{{ route('socios.update', $socio) }}" class="space-y-4">
                        @csrf
<input type="hidden" name="operacion" value="{{ $operacion }}">
                        @method('PATCH')

                        <div>
                            <x-input-label for="observaciones_{{ $socio->id }}" value="Observaciones (obligatorias si rechazas)" />
                            <textarea id="observaciones_{{ $socio->id }}" name="observaciones" rows="3"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-brand-500 dark:focus:border-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400 rounded-md shadow-sm">{{ $valor('observaciones') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('observaciones')" />
                        </div>

                        <div class="flex items-center gap-3">
                            <x-primary-button name="estado" :value="$socio->estado === 'pendiente' ? 'por_firmar' : 'activo'">
                                {{ $socio->estado === 'pendiente' ? 'Aprobar documentos' : 'Confirmar firma y activar socio' }}
                            </x-primary-button>

                            <x-danger-button name="estado" value="rechazado">Rechazar</x-danger-button>
                        </div>
                    </form>
                </div>
            @endif

            @if ($socio->user_id !== auth()->id() && $socio->estado === 'rechazado')
                <div class="p-6 ui-panel">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">Reabrir solicitud</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Si la persona puede corregir el problema, reabre la solicitud. Volverá a "pendiente" y ella podrá editar sus datos y documentos. El motivo del rechazo se conserva.
                    </p>
                    <form method="POST" action="{{ route('socios.update', $socio) }}">
                        @csrf
<input type="hidden" name="operacion" value="{{ $operacion }}">
                        @method('PATCH')
                        <x-primary-button name="estado" value="pendiente">Reabrir solicitud</x-primary-button>
                    </form>
                </div>
            @endif

            @if ($socio->user_id !== auth()->id() && ! $socio->user?->esDirigente() && $socio->estado === 'activo')
                <div class="p-6 ui-panel">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">Dar de baja</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        La persona pierde de inmediato los accesos de socio (certificados en línea y, más adelante, actas y arriendos). Podrás reactivarla después.
                    </p>
                    <form method="POST" action="{{ route('socios.update', $socio) }}" class="space-y-4"
                          onsubmit="return confirm('¿Dar de baja a este socio?');">
                        @csrf
<input type="hidden" name="operacion" value="{{ $operacion }}">
                        @method('PATCH')

                        <div>
                            <x-input-label for="motivo_baja_{{ $socio->id }}" value="Motivo de la baja" />
                            <textarea id="motivo_baja_{{ $socio->id }}" name="motivo_baja" rows="2" required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-brand-500 dark:focus:border-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400 rounded-md shadow-sm">{{ $valor('motivo_baja') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('motivo_baja')" />
                        </div>

                        <div>
                            <x-input-label for="fecha_baja_{{ $socio->id }}" value="Fecha de la baja" />
                            <x-date-input id="fecha_baja_{{ $socio->id }}" name="fecha_baja" required
                                max="{{ now()->toDateString() }}" value="{{ $valor('fecha_baja', now()->toDateString()) }}"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-brand-500 dark:focus:border-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400 rounded-md shadow-sm" />
                            <x-input-error class="mt-2" :messages="$errors->get('fecha_baja')" />
                        </div>

                        <x-danger-button name="estado" value="inactivo">Dar de baja</x-danger-button>
                    </form>
                </div>
            @endif

            @if ($socio->user_id !== auth()->id() && $socio->estado === 'inactivo')
                <div class="p-6 ui-panel">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">Reactivar socio</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Recupera sus accesos de socio y conserva su fecha de ingreso original ({{ $socio->fecha_ingreso?->format('d/m/Y') ?? 'sin fecha' }}). La baja queda registrada en la auditoría.
                    </p>
                    <form method="POST" action="{{ route('socios.update', $socio) }}">
                        @csrf
<input type="hidden" name="operacion" value="{{ $operacion }}">
                        @method('PATCH')
                        <x-primary-button name="estado" value="activo">Reactivar socio</x-primary-button>
                    </form>
                </div>
            @endif
<details @if(old('operacion') === 'socio_'.$socio->id && old('accion') === 'editar') open @endif><summary>Editar datos del socio</summary>@include('socios._editar')</details>
