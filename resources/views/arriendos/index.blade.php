@php
    $representantes = $grupos->mapWithKeys(function ($grupo) use ($socios) {
        $socioGrupo = $grupo->representante?->estado === 'activo' ? $grupo->representante
            : ($grupo->representante_usuario_id ? $socios->firstWhere('user_id', $grupo->representante_usuario_id) : null);
        $persona = $grupo->datosRepresentante();
        return [$grupo->id => [
            'socioId' => (string) ($socioGrupo?->id ?? ''),
            'nombre' => $persona['nombre'] ?? '', 'rut' => $grupo->representante?->rut ?? $socioGrupo?->rut ?? '',
            'direccion' => $persona['direccion'] ?? $socioGrupo?->direccion ?? '',
            'telefono' => $persona['telefono'] ?? $socioGrupo?->telefono ?? '',
        ]];
    });
    $estadoArriendo = [
        'modalidad' => old('modalidad_solicitante', 'socio'), 'socioId' => (string) old('socio_id', ''),
        'grupoId' => (string) old('grupo_id', ''),
        'persona' => ['nombre' => old('responsable_nombre', ''), 'rut' => old('responsable_rut', ''), 'direccion' => old('responsable_direccion', ''), 'telefono' => old('responsable_telefono', '')],
        'inicio' => old('hora_inicio', $bloques['inicios'][0] ?? ''), 'fin' => old('hora_fin', $bloques['fines'][0] ?? ''), 'fines' => $bloques['fines'],
    ];
@endphp
<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold">Arriendos de la sede</h2></x-slot>
    <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">
        <details class="ui-panel p-6 space-y-4" @if ($errors->any()) open @endif>
            <summary class="cursor-pointer text-lg font-semibold">Solicitar arriendo</summary>
            <p>El horario se reserva durante {{ $plazoHoras }} horas para su confirmación. Presidente o secretario define el aporte voluntario de cada reserva; $0 corresponde a una reserva gratuita.</p>
            <x-input-error :messages="$errors->get('arriendo')" />
            <x-form-wizard :steps="['Solicitante', 'Horario', 'Aporte']" :fields="[['socio_id', 'grupo_id', 'modalidad_solicitante', 'responsable_nombre', 'responsable_rut', 'responsable_direccion', 'responsable_telefono'], ['fecha', 'hora_inicio', 'hora_fin', 'arriendo'], ['comprobante', 'valor_arriendo']]">
                <form method="POST" action="{{ route('arriendos.store') }}" enctype="multipart/form-data" novalidate class="space-y-5"
                    x-data="arriendoFormulario(@js($estadoArriendo), @js($representantes))">
                    @csrf
                    <x-form-step number="0">
                        @if ($grupos->isNotEmpty())
                            <div>
                                <x-input-label for="grupo_id" value="Modalidad" />
                                <select id="grupo_id" name="grupo_id" class="ui-input w-full" x-model="grupoId" @change="elegirGrupo()">
                                    <option value="">Arriendo particular</option>
                                    @foreach ($grupos as $grupo)
                                        <option value="{{ $grupo->id }}" @selected((string) old('grupo_id') === (string) $grupo->id)>En representación de {{ $grupo->nombre }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('grupo_id')" />
                            </div>
                        @endif
                        @if ($esDirigente)
                            <div>
                                <x-input-label for="modalidad_solicitante" value="Registro del solicitante" />
                                <select id="modalidad_solicitante" name="modalidad_solicitante" x-model="modalidad" class="ui-input w-full" required>
                                    <option value="socio">Seleccionar socio activo</option>
                                    <option value="manual">Ingresar persona en ventanilla, sin exigir ficha ni cuenta</option>
                                </select>
                                <x-input-error :messages="$errors->get('modalidad_solicitante')" />
                            </div>
                            <div x-show="modalidad === 'socio'">
                                <x-input-label for="socio_id" value="Socio responsable" />
                                <select id="socio_id" name="socio_id" x-model="socioId" class="ui-input w-full" :required="modalidad === 'socio'" :disabled="modalidad !== 'socio'">
                                    <option value="">Elegir socio activo</option>
                                    @foreach ($socios as $socio)
                                        <option value="{{ $socio->id }}">{{ $socio->nombre_completo }} · {{ $socio->rut }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('socio_id')" />
                            </div>
                            <div x-show="modalidad === 'manual'" x-cloak class="grid gap-4 sm:grid-cols-2">
                                @foreach (['nombre' => ['Nombre completo', 255], 'rut' => ['RUT', 12], 'direccion' => ['Domicilio en la unidad vecinal', 255], 'telefono' => ['Teléfono', 20]] as $campo => [$etiqueta, $maximo])
                                    <div>
                                        <x-input-label :for="'responsable_'.$campo" :value="$etiqueta" />
                                        <x-text-input :id="'responsable_'.$campo" :name="'responsable_'.$campo" x-model="persona.{{ $campo }}" :maxlength="$maximo"
                                            x-bind:required="modalidad === 'manual'" x-bind:disabled="modalidad !== 'manual'" class="w-full" />
                                        <x-input-error :messages="$errors->get('responsable_'.$campo)" />
                                    </div>
                                @endforeach
                                <p class="text-sm sm:col-span-2">Estos datos quedan en el arriendo; no se crea una ficha ni una cuenta. Para un grupo, el responsable debe ser su representante.</p>
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
                                    <x-text-input id="valor_arriendo" name="valor_arriendo" type="number" x-model="aporte" min="0" max="4294967295" step="1" required />
                                    <x-input-error :messages="$errors->get('valor_arriendo')" />
                                </div>
                                <p>$0: reserva gratuita. El monto quedará fijado al registrar la solicitud.</p>
                                <div x-show="Number(aporte) > 0" x-cloak>
                                    <x-input-label for="comprobante" value="Comprobante del aporte (si ya fue recibido)" />
                                    <x-file-input id="comprobante" name="comprobante" accept="application/pdf,image/jpeg,image/png" x-bind:disabled="Number(aporte) <= 0" />
                                    <p class="text-sm text-gray-500">PDF, JPG o PNG, máximo 5 MB. Puedes adjuntarlo después desde el detalle; será necesario para confirmar una reserva pagada.</p>
                                    <x-input-error :messages="$errors->get('comprobante')" />
                                </div>
                            </div>
                        @else
                            <p>Presidente o secretario definirá el aporte. Revisa el detalle de tu solicitud: si es mayor que $0, podrás adjuntar allí el comprobante. Si es gratuita, no se exige pago.</p>
                        @endif
                    </x-form-step>
                    <x-wizard-actions />
                </form>
            </x-form-wizard>
        </details>
        <section class="ui-panel overflow-hidden">
            <h3 class="p-6 text-lg font-semibold">{{ $esDirigente ? 'Solicitudes de arriendo' : 'Mis arriendos' }}</h3>
            <div class="overflow-x-auto">
                <table class="ui-table">
                    <thead><tr><th>Fecha y horario</th><th>Solicitante</th><th>Estado</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($arriendos as $arriendo)
                            <tr>
                                <td>{{ $arriendo->inicio->format('d/m/Y H:i') }} a {{ $arriendo->fin->format('H:i') }}</td>
                                <td>{{ $arriendo->responsable_nombre ?? $arriendo->socio?->nombre_completo }}@if ($arriendo->grupo)<br>{{ $arriendo->grupo->nombre }}@endif</td>
                                <td>{{ \App\Models\Reserva::ESTADOS[$arriendo->estado] ?? $arriendo->estado }}</td>
                                <td><a class="underline" href="{{ route('arriendos.show', $arriendo) }}">Ver detalle</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="4">No hay solicitudes de arriendo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $arriendos->links() }}</div>
        </section>
    </div>
</x-app-layout>
