@php
    $esCreacion = ! old('operacion') || old('operacion') === 'crear-reserva';
    $valorCreacion = fn ($campo, $defecto = null) => $esCreacion ? old($campo, $defecto) : $defecto;
@endphp
@php
    $estadoArriendo = [
        'seleccion' => $valorCreacion('solicitante', ''), 'personas' => $solicitantes,
        'grupoId' => (string) $valorCreacion('grupo_id', ''),
        'tipoActividad' => $valorCreacion('actividad_reserva', $esDirigente ? 'actividad' : 'particular'), 'garantia' => $valorCreacion('garantia_reserva', '0'),
        'persona' => ['nombre' => $valorCreacion('responsable_nombre', ''), 'rut' => $valorCreacion('responsable_rut', ''), 'direccion' => $valorCreacion('responsable_direccion', ''), 'telefono' => $valorCreacion('responsable_telefono', '')],
        'inicio' => $valorCreacion('hora_inicio', $bloques['inicios'][0] ?? ''), 'fin' => $valorCreacion('hora_fin', $bloques['fines'][0] ?? ''), 'fines' => $bloques['fines'],
    ];
@endphp
<x-app-layout>
    <x-slot name="header"><x-page-heading :title="$esDirigente ? 'Reservas de sede' : 'Mis reservas de sede'" description="Registra el uso de la sede y revisa las solicitudes recibidas."><a href="{{ route('calendario.index') }}" class="ui-button ui-button-secondary">Consultar calendario</a>@unless(request('crear'))<a class="ui-button ui-button-primary" href="{{ route($esDirigente ? 'reservas.index' : 'arriendos.index', ['crear'=>1]) }}">{{ $esDirigente ? 'Reservar en ventanilla' : 'Solicitar reserva' }}</a>@endunless</x-page-heading></x-slot>
    <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">

        <x-reservas-tabs active="sede" />
        @if (session('status'))<p class="ui-notice" role="status">{{ match(session('status')) { 'reserva-creada' => 'Reserva guardada.', 'reserva-cancelada' => 'Reserva cancelada y horario liberado.', 'aporte-definido' => 'Aporte y garantía definidos.', 'comprobante-adjuntado' => 'Comprobante adjuntado para revisión.', 'arriendo-confirmado' => 'Reserva confirmada.', default => session('status') } }}</p>@endif
        @if ($errors->any())<div role="alert" class="ui-notice text-red-700">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif

        @php($erroresListado = $errors)
        @php($errors = $esCreacion ? $errors : new \Illuminate\Support\ViewErrorBag)
        @if(request('crear'))
        <section class="ui-panel p-6 space-y-4">
            <h2 class="text-lg font-semibold">{{ $esDirigente ? 'Reserva en ventanilla' : 'Solicitar reserva' }}</h2>

            <x-input-error :messages="$errors->get('arriendo')" />
            <x-input-error :messages="$errors->get('reserva')" />
            <x-form-wizard :steps="['Solicitante', 'Horario', 'Aporte']" :fields="[['solicitante', 'tipo', 'actividad_reserva', 'socio_id', 'grupo_id', 'modalidad_solicitante', 'responsable_nombre', 'responsable_rut', 'responsable_direccion', 'responsable_telefono'], ['fecha', 'hora_inicio', 'hora_fin', 'arriendo', 'reserva'], ['comprobante', 'valor_arriendo', 'garantia_reserva']]">
                <form method="POST" action="{{ route('arriendos.store') }}" enctype="multipart/form-data" novalidate class="space-y-5"
                    x-data="arriendoFormulario(@js($estadoArriendo))">
                    @csrf
                    <input type="hidden" name="operacion" value="crear-reserva">
                    <x-form-step number="0" class="ui-form-grid">
                        <div><x-input-label for="actividad_reserva" value="Actividad" /><select id="actividad_reserva" name="actividad_reserva" x-model="tipoActividad" @change="cambiarActividad()" class="ui-input w-full" required>
                            @foreach($tiposActividad as $tipo) @if($esDirigente || in_array($tipo->clave, ['particular','grupo']))<option value="{{ $tipo->clave }}">{{ $tipo->nombre }}</option>@endif @endforeach
                        </select></div>
                        @if($esDirigente)
                            <div><x-input-label for="solicitante" value="Solicitante" /><select id="solicitante" name="solicitante" x-model="seleccion" @change="elegirSolicitante()" class="ui-input w-full" required><option value="">Seleccionar solicitante</option><template x-for="opcion in opciones()" :key="opcion.clave"><option :value="opcion.clave" x-text="opcion.etiqueta"></option></template><option x-show="!['actividad','grupo'].includes(tipoActividad)" :disabled="['actividad','grupo'].includes(tipoActividad)" value="manual">Registrar solicitante</option></select></div>
                            @foreach(['nombre'=>'Nombre', 'rut'=>'RUT', 'direccion'=>'Dirección', 'telefono'=>'Teléfono'] as $campo=>$etiqueta)<div><x-input-label :for="'responsable_'.$campo" :value="$etiqueta" /><x-text-input :id="'responsable_'.$campo" :name="'responsable_'.$campo" x-model="persona.{{ $campo }}" x-bind:disabled="!seleccion" x-bind:readonly="seleccion !== 'manual'" x-bind:required="seleccion === 'manual'" class="w-full" /></div>@endforeach
                            <p class="text-sm text-gray-500 sm:col-span-2" x-show="tipoActividad === 'grupo' && seleccion" x-text="'En representación de: ' + (personas.find(p => p.clave === seleccion)?.etiqueta.split(' · ').slice(1).join(' · ') || '')"></p>
                        @else
                            <p>Solicitante: <strong>{{ auth()->user()->socio->nombre_completo }}</strong></p>
                            @if($grupos->isNotEmpty())<div x-show="tipoActividad === 'grupo'"><x-input-label for="grupo_id" value="En representación de" /><select id="grupo_id" name="grupo_id" :disabled="tipoActividad !== 'grupo'" :required="tipoActividad === 'grupo'" x-model="grupoId" class="ui-input w-full"><option value="">Elegir agrupación</option>@foreach($grupos as $grupo)<option value="{{ $grupo->id }}">{{ $grupo->nombre }}</option>@endforeach</select></div>@endif
                        @endif
                    </x-form-step>
                    <x-form-step number="1">
                        <a class="underline" href="{{ route('calendario.index') }}" target="_blank" rel="noopener">Consultar disponibilidad</a>
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div>
                                <x-input-label for="fecha" value="Fecha" />
                                <x-date-input id="fecha" name="fecha" class="w-full" :value="$valorCreacion('fecha', now()->addDay()->toDateString())" min="{{ now()->toDateString() }}" max="{{ now()->addMonths(6)->toDateString() }}" required />
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
                            <div class="space-y-4" x-data="{ aporte: @js($valorCreacion('valor_arriendo', '0')) }">
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
                    <x-wizard-actions :cancel="route($esDirigente ? 'reservas.index' : 'arriendos.index')" />
                </form>
            </x-form-wizard>
        </section>
        @else
        @php($errors = $erroresListado)
        <section class="ui-panel overflow-hidden">
            <h3 class="p-6 text-lg font-semibold">{{ $esDirigente ? 'Reservas y solicitudes de sede' : 'Mis reservas' }}</h3>
            <div class="overflow-x-auto">
                <table class="ui-table">
                    <thead><tr><th>Fecha y horario</th><th>Solicitante / actividad</th><th>Condición</th><th>Estado</th><th>Detalle</th></tr></thead>
                        @forelse ($arriendos as $arriendo)
                            <x-record-row :id="'reserva_'.$arriendo->id" :columns="5">
                                <x-slot name="summary">
                                <td>{{ $arriendo->inicio->format('d/m/Y H:i') }} a {{ $arriendo->fin->format('H:i') }}</td>
                                <td><strong>{{ $arriendo->responsable_nombre ?? $arriendo->socio?->nombre_completo }}</strong><span class="block text-xs text-gray-500">{{ $arriendo->motivo }}@if ($arriendo->grupo) · {{ $arriendo->grupo->nombre }}@endif</span></td>
                                <td>{{ $arriendo->tipo !== 'arriendo' || ($arriendo->aporte_variable && $arriendo->valor_arriendo !== null && $arriendo->valor_arriendo == 0) ? 'Sin aporte' : ($arriendo->aporte_variable && $arriendo->valor_arriendo === null ? 'Aporte por definir' : 'Con aporte voluntario') }}<br>{{ $arriendo->etiquetaTipo() }}</td>
                                <td><x-status-badge :state="$arriendo->estado" :label="\App\Models\Reserva::ESTADOS[$arriendo->estado] ?? $arriendo->estado" /></td>
                                </x-slot>
                                @include('arriendos._detalle', ['reserva' => $arriendo, 'esFinanciero' => false, 'volverFinanzas' => false])
                            </x-record-row>
                        @empty
                            <tbody><tr><td colspan="5">No hay reservas de sede.</td></tr></tbody>
                        @endforelse
                </table>
            </div>
            <div class="p-4">{{ $arriendos->links() }}</div>
        </section>
        @endif
    </div>
</x-app-layout>
