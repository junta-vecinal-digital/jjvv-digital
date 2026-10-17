@php
    $representantes = $grupos->mapWithKeys(fn ($grupo) => [$grupo->id => (string) $grupo->representante_id]);
@endphp
<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold">Arriendos de la sede</h2></x-slot>
    <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">
        <section class="ui-panel p-6 space-y-4">
            <h3 class="text-lg font-semibold">Solicitar arriendo</h3>
            <p>La solicitud reserva el horario durante {{ $plazoHoras }} horas, mientras presidente o secretario valida el pago y la garantía.</p>
            <x-input-error :messages="$errors->get('arriendo')" />
            <x-form-wizard :steps="['Solicitante', 'Horario', 'Comprobante']" :fields="[['socio_id', 'grupo_id'], ['fecha', 'hora_inicio', 'hora_fin', 'arriendo'], ['comprobante', 'valor_arriendo', 'valor_garantia']]">
                <form method="POST" action="{{ route('arriendos.store') }}" enctype="multipart/form-data" novalidate class="space-y-5"
                    x-data="{ socioId: @js((string) old('socio_id', '')), representantes: @js($representantes), inicio: @js(old('hora_inicio', $bloques['inicios'][0] ?? '')), fin: @js(old('hora_fin', $bloques['fines'][0] ?? '')), fines: @js($bloques['fines']) }">
                    @csrf
                    <input type="hidden" name="valor_arriendo" value="{{ $valorArriendo }}">
                    <input type="hidden" name="valor_garantia" value="{{ $valorGarantia }}">
                    <x-form-step number="0">
                        @if ($grupos->isNotEmpty())
                            <div>
                                <x-input-label for="grupo_id" value="Modalidad" />
                                <select id="grupo_id" name="grupo_id" class="ui-input w-full" @change="if ($event.target.value) socioId = representantes[$event.target.value]">
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
                                <x-input-label for="socio_id" value="Socio responsable" />
                                <select id="socio_id" name="socio_id" x-model="socioId" class="ui-input w-full" required>
                                    <option value="">Elegir socio activo</option>
                                    @foreach ($socios as $socio)
                                        <option value="{{ $socio->id }}">{{ $socio->nombre_completo }} · {{ $socio->rut }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('socio_id')" />
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
                                <x-text-input id="fecha" name="fecha" type="date" class="w-full" :value="old('fecha', now()->addDay()->toDateString())" min="{{ now()->toDateString() }}" max="{{ now()->addMonths(6)->toDateString() }}" required />
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
                        <p>Arriendo: ${{ number_format($valorArriendo, 0, ',', '.') }} · Garantía: ${{ number_format($valorGarantia, 0, ',', '.') }}</p>
                        <p class="font-semibold">Total: ${{ number_format($valorArriendo + $valorGarantia, 0, ',', '.') }}</p>
                        <div>
                            <x-input-label for="comprobante" value="Comprobante del total pagado" />
                            <input id="comprobante" name="comprobante" type="file" accept="application/pdf,image/jpeg,image/png" required class="block w-full mt-2">
                            <p class="text-sm text-gray-500">Un archivo PDF, JPG o PNG, máximo 5 MB. Incluye arriendo y garantía.</p>
                            <x-input-error :messages="$errors->get('comprobante')" />
                        </div>
                        <x-input-error :messages="$errors->get('valor_arriendo')" />
                        <x-input-error :messages="$errors->get('valor_garantia')" />
                    </x-form-step>
                    <x-wizard-actions />
                </form>
            </x-form-wizard>
        </section>
        <section class="ui-panel overflow-hidden">
            <h3 class="p-6 text-lg font-semibold">{{ $esDirigente ? 'Solicitudes de arriendo' : 'Mis arriendos' }}</h3>
            <div class="overflow-x-auto">
                <table class="ui-table">
                    <thead><tr><th>Fecha y horario</th><th>Solicitante</th><th>Estado</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($arriendos as $arriendo)
                            <tr>
                                <td>{{ $arriendo->inicio->format('d-m-Y H:i') }} a {{ $arriendo->fin->format('H:i') }}</td>
                                <td>{{ $arriendo->socio?->nombre_completo }}@if ($arriendo->grupo)<br>{{ $arriendo->grupo->nombre }}@endif</td>
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
