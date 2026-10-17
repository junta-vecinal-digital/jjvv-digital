<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white leading-tight">
                {{ $socio->nombre_completo }}
                <x-representante-badge :socio="$socio" />
            </h2>
            <div class="flex items-center gap-4 text-sm">
                @if ($socio->estado === 'activo')
                    <a href="{{ route('admin.certificados.create', ['socio' => $socio->id]) }}" class="underline text-brand-600 dark:text-brand-400">Emitir certificado</a>
                @endif
                <a href="{{ route('socios.index') }}" class="underline text-gray-600 dark:text-gray-400">← Volver al listado</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @php
                $mensajes = [
                    'socio-creado' => 'El socio fue registrado.',
                    'socio-editado' => 'Los datos del socio fueron actualizados.',
                    'socio-por_firmar' => 'Documentos aprobados. La persona ya puede reservar su hora para firmar el libro.',
                    'socio-rechazado' => 'La solicitud fue rechazada.',
                    'socio-activo' => 'El socio quedó activo.',
                    'socio-pendiente' => 'La solicitud fue reabierta. La persona puede corregir sus datos y reenviarla.',
                    'socio-inactivo' => 'El socio fue dado de baja.',
                ];
            @endphp

            @if (isset($mensajes[session('status')]))
                <div class="p-4 bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 rounded-lg text-sm">{{ $mensajes[session('status')] }}</div>
            @endif
            @if (session('status') === 'socio-por_firmar')
                <div role="status" class="rounded-lg bg-brand-50 p-4 text-sm text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    Recuerda habilitar horas disponibles para la firma del libro en la
                    <a href="{{ route('agenda.index') }}" class="font-medium underline">Agenda de firmas</a>.
                    Los horarios públicos de atención no crean estas citas automáticamente.
                </div>
            @endif
            
            @include('socios._cuenta', ['socio' => $socio])
            
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

            @if (in_array($socio->estado, ['pendiente', 'por_firmar']))
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
                        @method('PATCH')

                        <div>
                            <x-input-label for="observaciones" value="Observaciones (obligatorias si rechazas)" />
                            <textarea id="observaciones" name="observaciones" rows="3"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-brand-500 dark:focus:border-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400 rounded-md shadow-sm">{{ old('observaciones') }}</textarea>
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

            @if ($socio->estado === 'rechazado')
                <div class="p-6 ui-panel">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">Reabrir solicitud</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Si la persona puede corregir el problema, reabre la solicitud. Volverá a "pendiente" y ella podrá editar sus datos y documentos. El motivo del rechazo se conserva.
                    </p>
                    <form method="POST" action="{{ route('socios.update', $socio) }}">
                        @csrf
                        @method('PATCH')
                        <x-primary-button name="estado" value="pendiente">Reabrir solicitud</x-primary-button>
                    </form>
                </div>
            @endif

            @if ($socio->estado === 'activo')
                <div class="p-6 ui-panel">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">Dar de baja</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        La persona pierde de inmediato los accesos de socio (certificados en línea y, más adelante, actas y arriendos). Podrás reactivarla después.
                    </p>
                    <form method="POST" action="{{ route('socios.update', $socio) }}" class="space-y-4"
                          onsubmit="return confirm('¿Dar de baja a este socio?');">
                        @csrf
                        @method('PATCH')

                        <div>
                            <x-input-label for="motivo_baja" value="Motivo de la baja" />
                            <textarea id="motivo_baja" name="motivo_baja" rows="2" required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-brand-500 dark:focus:border-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400 rounded-md shadow-sm">{{ old('motivo_baja') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('motivo_baja')" />
                        </div>

                        <div>
                            <x-input-label for="fecha_baja" value="Fecha de la baja" />
                            <x-date-input id="fecha_baja" name="fecha_baja" required
                                max="{{ now()->toDateString() }}" value="{{ old('fecha_baja', now()->toDateString()) }}"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-brand-500 dark:focus:border-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400 rounded-md shadow-sm" />
                            <x-input-error class="mt-2" :messages="$errors->get('fecha_baja')" />
                        </div>

                        <x-danger-button name="estado" value="inactivo">Dar de baja</x-danger-button>
                    </form>
                </div>
            @endif

            @if ($socio->estado === 'inactivo')
                <div class="p-6 ui-panel">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">Reactivar socio</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Recupera sus accesos de socio y conserva su fecha de ingreso original ({{ $socio->fecha_ingreso?->format('d/m/Y') ?? 'sin fecha' }}). La baja queda registrada en la auditoría.
                    </p>
                    <form method="POST" action="{{ route('socios.update', $socio) }}">
                        @csrf
                        @method('PATCH')
                        <x-primary-button name="estado" value="activo">Reactivar socio</x-primary-button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
