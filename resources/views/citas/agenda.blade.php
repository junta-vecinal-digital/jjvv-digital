<x-app-layout>
<x-slot name="header">
<x-page-heading title="Agenda de firmas" description="Gestiona las horas para firmar el libro de socios, separadas de la atención al público.">
@unless(request('crear'))<a href="{{ route('agenda.index',['crear'=>1]) }}" class="ui-button ui-button-primary">Crear horarios de firma</a>
@endunless
</x-page-heading>
</x-slot>
<div class="ui-page">
<x-operation-notice />
@if(session('mensaje'))<p class="ui-notice" role="status">{{ session('mensaje') }}</p>
@endif
<p class="ui-notice">Las horas de firma se habilitan para una fecha concreta y se gestionan por separado de la atención semanal de ventanilla.</p>
@if(request('crear'))<section class="ui-panel p-6">
<x-form-wizard :steps="['Horas de firma']" :fields="[['fecha','hora_inicio','hora_fin','duracion']]">
<form method="POST" action="{{ route('agenda.store') }}" class="space-y-6" novalidate>
                    @csrf<x-form-step number="0" class="ui-form-grid">
<div>
<x-input-label for="fecha" value="Fecha" />
<x-date-input id="fecha" name="fecha" class="mt-1 block w-full"
                            :value="old('fecha', now()->toDateString())" min="{{ now()->toDateString() }}" max="{{ now()->addMonths(6)->toDateString() }}" required />
<x-input-error class="mt-2" :messages="$errors->get('fecha')" />
</div>
<div>
<x-input-label for="hora_inicio" value="Desde" />
<x-text-input id="hora_inicio" name="hora_inicio" type="time" class="mt-1 block w-full"
                            :value="old('hora_inicio')" required />
<x-input-error class="mt-2" :messages="$errors->get('hora_inicio')" />
</div>
<div>
<x-input-label for="hora_fin" value="Hasta" />
<x-text-input id="hora_fin" name="hora_fin" type="time" class="mt-1 block w-full"
                            :value="old('hora_fin')" required />
<x-input-error class="mt-2" :messages="$errors->get('hora_fin')" />
</div>
<div>
<x-input-label for="duracion" value="Duración de cada hora" />
<select id="duracion" name="duracion"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-brand-500 dark:focus:border-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400 rounded-md shadow-sm">
                            @foreach ([15, 20, 30, 45, 60] as $minutos)
                                <option value="{{ $minutos }}" @selected(old('duracion', 20) == $minutos)>{{ $minutos }} minutos</option>
                            @endforeach
                        </select>
<x-input-error class="mt-2" :messages="$errors->get('duracion')" />
</div>
</x-form-step>
<x-wizard-actions :cancel="route('agenda.index')" submit-label="Crear horarios de firma" />
</form>
</x-form-wizard>
</section>
@else
@forelse($citas as $lista)<section class="space-y-4">
<h2 class="text-xl font-semibold">{{ $lista->first()->inicio->format('d/m/Y') }} · Citas de firma</h2>
<div class="ui-panel overflow-x-auto">
<table class="ui-table">
<thead>
<tr>
<th>Hora</th>
<th>Reserva</th>
<th>Gestión</th>
</tr>
</thead>
@foreach($lista as $cita)<x-record-row :id="'cita_'.$cita->id" :columns="3" :label="$cita->socio ? 'Ver cita' : 'Gestionar hora'">
<x-slot name="summary">
<td>{{ $cita->inicio->format('H:i') }}</td>
<td>{{ $cita->socio?->nombre_completo ?? 'Disponible' }}</td>
</x-slot>
<h2 class="text-xl font-semibold">{{ $cita->socio ? 'Liberar hora reservada' : 'Eliminar hora disponible' }}</h2>
<form method="POST" action="{{ route($cita->socio ? 'agenda.liberar' : 'agenda.destroy',$cita) }}">
@csrf @method($cita->socio ? 'PATCH' : 'DELETE')<input type="hidden" name="operacion" value="cita_{{ $cita->id }}">
<p>{{ $cita->socio ? 'El socio deberá reservar otra hora.' : 'Esta hora todavía no está reservada.' }}</p>
<label class="flex gap-2">
<input type="checkbox" required>Confirmo esta operación.</label>
<x-primary-button>{{ $cita->socio ? 'Liberar hora' : 'Eliminar hora' }}</x-primary-button>
</form>
</x-record-row>
@endforeach
</table>
</div>
</section>
@empty
<p>No hay horarios creados.</p>
@endforelse
@endif
</div>
</x-app-layout>
