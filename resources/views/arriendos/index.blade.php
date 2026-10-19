@php
    $representantes = $grupos->mapWithKeys(function ($grupo) use ($socios) {
        $persona = $grupo->datosRepresentante();
        $socioGrupo = $grupo->representante?->estado === 'activo' ? $grupo->representante
            : ($grupo->representante_usuario_id ? $socios->firstWhere('user_id', $grupo->representante_usuario_id) : null);
        if (! $socioGrupo && \App\Rules\RutChileno::esValido($persona['rut'])) {
            $socioGrupo = $socios->first(fn ($socio) => \App\Rules\RutChileno::normalizar($socio->rut) === \App\Rules\RutChileno::normalizar($persona['rut']));
        }
        return [$grupo->id => [
            'socioId' => (string) ($socioGrupo?->id ?? ''),
            'nombre' => $persona['nombre'] ?? '', 'rut' => $persona['rut'] ?? $socioGrupo?->rut ?? '',
            'direccion' => $persona['direccion'] ?? $socioGrupo?->direccion ?? '',
            'telefono' => $persona['telefono'] ?? $socioGrupo?->telefono ?? '',
        ]];
    });
    $estadoArriendo = [
        'modalidad' => old('modalidad_solicitante', 'socio'), 'socioId' => (string) old('socio_id', ''),
        'grupoId' => (string) old('grupo_id', ''),
        'tipoActividad' => old('actividad_reserva', 'particular'), 'garantia' => old('garantia_reserva', '0'),
        'persona' => ['nombre' => old('responsable_nombre', ''), 'rut' => old('responsable_rut', ''), 'direccion' => old('responsable_direccion', ''), 'telefono' => old('responsable_telefono', '')],
        'inicio' => old('hora_inicio', $bloques['inicios'][0] ?? ''), 'fin' => old('hora_fin', $bloques['fines'][0] ?? ''), 'fines' => $bloques['fines'],
    ];
@endphp
<x-app-layout>
    <x-slot name="header"><x-page-heading :title="$esDirigente ? 'Reservas de sede en ventanilla' : 'Mis reservas de sede'" description="Consulta los horarios disponibles y revisa el estado de las reservas."><a href="{{ route('calendario.index') }}" class="ui-button ui-button-secondary">Consultar calendario</a></x-page-heading></x-slot>
    <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">

        @if (in_array(session('status'), ['reserva-creada', 'reserva-cancelada']))<p class="ui-panel p-4" role="status">{{ session('status') === 'reserva-creada' ? 'Reserva guardada.' : 'Reserva cancelada y horario liberado.' }}</p>@endif

        <details class="ui-panel p-6 space-y-4" @if ($errors->any()) open @endif>
            <summary class="cursor-pointer text-lg font-semibold">{{ $esDirigente ? 'Reserva en ventanilla' : 'Solicitar reserva' }}</summary>

            <x-input-error :messages="$errors->get('arriendo')" />
            <x-input-error :messages="$errors->get('reserva')" />
            <x-form-wizard :steps="['Solicitante', 'Horario', 'Aporte']" :fields="[['tipo', 'actividad_reserva', 'socio_id', 'grupo_id', 'modalidad_solicitante', 'responsable_nombre', 'responsable_rut', 'responsable_direccion', 'responsable_telefono'], ['fecha', 'hora_inicio', 'hora_fin', 'arriendo', 'reserva'], ['comprobante', 'valor_arriendo', 'garantia_reserva']]">
                <form method="POST" action="{{ route('arriendos.store') }}" enctype="multipart/form-data" novalidate class="space-y-5"
                    x-data="arriendoFormulario(@js($estadoArriendo), @js($representantes))">
                    @csrf
                    <x-form-step number="0">
                        @if ($esDirigente)
                            <div>
                                <x-input-label for="tipo_actividad" value="Actividad" />
                                <select id="tipo_actividad" name="actividad_reserva" x-model="tipoActividad" @change="if (tipoActividad !== 'grupo') grupoId = ''" class="ui-input w-full" required>

                                    @foreach ($tiposActividad as $tipo)<option value="{{ $tipo->clave }}">{{ $tipo->nombre }}</option>@endforeach
                                </select>
                                <x-input-error :messages="$errors->get('actividad_reserva')" />
                            </div>
                        @elseif ($grupos->isNotEmpty())
                            <div>
                                <x-input-label for="actividad_personal" value="Actividad" />
                                <select id="actividad_personal" name="actividad_reserva" x-model="tipoActividad" @change="if (tipoActividad !== 'grupo') grupoId = ''" class="ui-input w-full" required>
                                    @foreach ($tiposActividad->whereIn('clave', ['particular', 'grupo']) as $tipo)<option value="{{ $tipo->clave }}">{{ $tipo->nombre }}</option>@endforeach
                                </select>
                                <x-input-error :messages="$errors->get('actividad_reserva')" />
                            </div>
                        @else
                            <input type="hidden" name="actividad_reserva" value="particular">
                        @endif
                        @if ($grupos->isNotEmpty())
                            <div>
                                <x-input-label for="grupo_id" value="En representación de" />
                                <select id="grupo_id" name="grupo_id" class="ui-input w-full" x-model="grupoId" @change="elegirGrupo()" :required="tipoActividad === 'grupo'" :disabled="tipoActividad !== 'grupo'">
                                    <option value="">Sin agrupación</option>
                                    @foreach ($grupos as $grupo)
                                        <option value="{{ $grupo->id }}" @selected((string) old('grupo_id') === (string) $grupo->id)>{{ $grupo->nombre }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('grupo_id')" />
                            </div>
                        @endif
                        @if ($esDirigente)
                            <div>
                                <x-input-label for="modalidad_solicitante" value="Registro del solicitante" />
                                <select id="modalidad_solicitante" name="modalidad_solicitante" x-model="modalidad" class="ui-input w-full" :disabled="tipoActividad !== 'particular'" required>
                                    <option value="socio">Seleccionar socio activo</option>
                                    <option value="manual">Ingresar persona en ventanilla</option>
                                </select>
                                <x-input-error :messages="$errors->get('modalidad_solicitante')" />
                            </div>
                            <div x-show="(tipoActividad === 'particular' && modalidad === 'socio') || (tipoActividad === 'grupo' && socioId)">
                                <x-input-label for="socio_id" value="Socio responsable" />
                                <select id="socio_id" name="socio_id" x-model="socioId" class="ui-input w-full" :required="tipoActividad === 'particular' && modalidad === 'socio'" :disabled="tipoActividad !== 'particular' || modalidad !== 'socio'">
                                    <option value="">Elegir socio activo</option>
                                    @foreach ($socios as $socio)
                                        <option value="{{ $socio->id }}">{{ $socio->nombre_completo }} · {{ $socio->rut }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('socio_id')" />
                                <p x-show="grupoId && socioId" class="mt-2 text-sm text-gray-500">Socio autocompletado desde el representante de la agrupación.</p>
                            </div>
                            <p x-show="tipoActividad === 'grupo' && grupoId && !socioId" class="text-sm">Representante: <strong x-text="persona.nombre"></strong> · <span x-text="persona.rut"></span></p>
                            <div x-show="tipoActividad === 'particular' && modalidad === 'manual'" x-cloak class="grid gap-4 sm:grid-cols-2">
                                @foreach (['nombre' => ['Nombre completo', 255], 'rut' => ['RUT', 12], 'direccion' => ['Domicilio en la unidad vecinal', 255], 'telefono' => ['Teléfono', 20]] as $campo => [$etiqueta, $maximo])
                                    <div>
                                        <x-input-label :for="'responsable_'.$campo" :value="$etiqueta" />
                                        <x-text-input :id="'responsable_'.$campo" :name="'responsable_'.$campo" x-model="persona.{{ $campo }}" :maxlength="$maximo"
                                            x-bind:required="tipoActividad === 'particular' && modalidad === 'manual'" x-bind:disabled="tipoActividad !== 'particular' || modalidad !== 'manual'" class="w-full" />
                                        <x-input-error :messages="$errors->get('responsable_'.$campo)" />
                                    </div>
                                @endforeach
                                <p class="text-sm sm:col-span-2">Estos datos quedan en la reserva; no se crea una ficha ni una cuenta. Para un grupo, el responsable debe ser su representante.</p>
                            </div>
                        @else
                            <p>Solicitante: <strong>{{ auth()->user()->socio->nombre_completo }}</strong></p>
                        @endif
                    </x-form-step>
                    <x-form-step number="1">
                        <a class="underline" href="{{ route('calendario.index') }}" target="_blank" rel="noopener">Consultar disponibilidad</a>
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div>
                                <x-input-label for="fecha" value="Fecha" />
                                <x-date-input id="fecha" name="fecha" class="w-full" :value="old('fecha', now()->addDay()->toDateString())" min="{{ now()->toDateString() }}" max="{{ now()->addMonths(6)->toDateString() }}" required />
                                <x-input-error :messages="$errors->get('fecha')" />
                            </div>
                            <div>
                                <x-input-label for="hora_inicio" value="Desde" />
                                <select id="hora_inicio" name="hora_inicio" class="ui-input w-full" x-model="inicio" @change="if (fin <= inicio) fin = fines.find(hora => hora > inicio) ?? ''" required>
                                    @foreach ($bloques['inicios'] as $hora)<option value="{{ $hora }}">{{ $hora }}</option>@endforeach
                                </select>
                                <x-input-error :messages="$errors->get('hora_inicio')" />
                            </div>
                            <div>
                                <x-input-label for="hora_fin" value="Hasta" />
                                <select id="hora_fin" name="hora_fin" class="ui-input w-full" x-model="fin" required>
                                    @foreach ($bloques['fines'] as $hora)<option value="{{ $hora }}" :disabled="'{{ $hora }}' <= inicio">{{ $hora }}</option>@endforeach
                                </select>
                                <x-input-error :messages="$errors->get('hora_fin')" />
                            </div>
                        </div>
                    </x-form-step>
                    <x-form-step number="2">
                        @if ($esDirigente)
                            <div class="space-y-4" x-data="{ aporte: @js(old('valor_arriendo', '0')) }">
                                <div>
                                    <x-input-label for="valor_arriendo" value="Aporte voluntario acordado (CLP)" />
                                    <x-text-input id="valor_arriendo" name="valor_arriendo" type="number" x-model="aporte" x-effect="if (tipoActividad !== 'particular') aporte = '0'" x-bind:readonly="tipoActividad !== 'particular'" min="0" max="4294967295" step="1" required />
                                    <x-input-error :messages="$errors->get('valor_arriendo')" />
                                </div>

                                <div>
                                    <x-input-label for="garantia_reserva" value="Garantía acordada (CLP)" />
                                    <x-text-input id="garantia_reserva" name="garantia_reserva" type="number" x-model="garantia" min="0" max="4294967295" step="1" required />
                                    <x-input-error :messages="$errors->get('garantia_reserva')" />
                                </div>
                                <div x-show="Number(aporte) + Number(garantia) > 0" x-cloak>
                                    <x-input-label for="comprobante" value="Comprobante del aporte y garantía" />
                                    <x-file-input id="comprobante" name="comprobante" accept="application/pdf,image/jpeg,image/png" x-bind:disabled="Number(aporte) + Number(garantia) <= 0" />
                                    <p class="text-sm text-gray-500">PDF, JPG o PNG, máximo 5 MB. Puedes adjuntarlo después desde el detalle; será necesario si el total recibido es mayor que $0.</p>
                                    <x-input-error :messages="$errors->get('comprobante')" />
                                </div>
                            </div>
                        @else
                            <p>Presidente o secretario registrará el aporte y la garantía acordados. El comprobante del total se adjunta desde el detalle de la solicitud.</p>
                        @endif
                    </x-form-step>
                    <x-wizard-actions />
                </form>
            </x-form-wizard>
        </details>
        <section class="ui-panel overflow-hidden">
            <h3 class="p-6 text-lg font-semibold">{{ $esDirigente ? 'Reservas y solicitudes de sede' : 'Mis reservas' }}</h3>
            <div class="overflow-x-auto">
                <table class="ui-table">
                    <thead><tr><th>Fecha y horario</th><th>Solicitante</th><th>Condición</th><th>Estado</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($arriendos as $arriendo)
                            <tr>
                                <td>{{ $arriendo->inicio->format('d/m/Y H:i') }} a {{ $arriendo->fin->format('H:i') }}</td>
                                <td>{{ $arriendo->responsable_nombre ?? $arriendo->socio?->nombre_completo }}@if ($arriendo->grupo)<br>{{ $arriendo->grupo->nombre }}@endif</td>
                                <td>{{ $arriendo->tipo !== 'arriendo' || ($arriendo->aporte_variable && $arriendo->valor_arriendo !== null && $arriendo->valor_arriendo == 0) ? 'Sin aporte' : ($arriendo->aporte_variable && $arriendo->valor_arriendo === null ? 'Aporte por definir' : 'Con aporte voluntario') }}<br>{{ $arriendo->etiquetaTipo() }}</td>
                                <td><x-status-badge :state="$arriendo->estado" :label="\App\Models\Reserva::ESTADOS[$arriendo->estado] ?? $arriendo->estado" /></td>
                                <td>
                                    @if ($arriendo->tipo === 'arriendo')<a class="underline" href="{{ route('arriendos.show', $arriendo) }}">Ver detalle</a>@endif
                                    @if ($esDirigente && in_array($arriendo->estado, ['pendiente', 'confirmada']))
                                        <form method="POST" action="{{ route('reservas.cancelar', $arriendo) }}" onsubmit="return confirm('¿Cancelar esta reserva y liberar su horario?')">@csrf<button class="underline">Cancelar reserva</button></form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5">No hay reservas de sede.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $arriendos->links() }}</div>
        </section>
    </div>
</x-app-layout>
