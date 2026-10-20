<x-app-layout>
<x-slot name="header">
<x-page-heading title="Calendario de la sede" description="Consulta la ocupación de la sede y los horarios disponibles.">
@if($esDirigente)<a href="{{ route('reservas.index') }}" class="ui-button ui-button-primary">Gestionar reservas</a>
@endif
</x-page-heading>
</x-slot>
<div class="ui-page" x-data="{ dia: 0, detalle: null, imprimir: false }">
<div class="flex flex-wrap items-center justify-between gap-4">
<h2 class="text-xl font-semibold">{{ $dias->first()->format('d/m/Y') }} al {{ $dias->last()->format('d/m/Y') }}</h2>
<div class="flex flex-wrap gap-3">
<a class="ui-button ui-button-secondary" href="{{ route('calendario.index',['semana'=>$lunes->copy()->subWeek()->toDateString()]) }}">← Anterior</a>
<a class="ui-button ui-button-secondary" href="{{ route('calendario.index') }}">Esta semana</a>
<a class="ui-button ui-button-secondary" href="{{ route('calendario.index',['semana'=>$lunes->copy()->addWeek()->toDateString()]) }}">Siguiente →</a>
</div>
</div>
<div class="flex flex-wrap gap-6 text-sm text-gray-500">
<span>□ Libre</span>
<span>■ Reserva confirmada o pendiente</span>
<span>▧ Horario fijo de agrupación</span>
</div>
<div class="hidden md:block">@include('calendario._grilla',['diasVisibles'=>$dias])</div>
<div class="space-y-4 md:hidden">
<div class="flex flex-wrap gap-2" aria-label="Día de la semana">
@foreach($dias as $indice=>$dia)<button type="button" class="ui-button ui-button-secondary" @click="dia = {{ $indice }}" :aria-pressed="dia === {{ $indice }}">{{ ucfirst($dia->translatedFormat('D j')) }}</button>
@endforeach
</div>
@foreach($dias as $indice=>$dia)<div x-show="dia === {{ $indice }}">@include('calendario._grilla',['diasVisibles'=>collect([$dia])])</div>
@endforeach
</div>
<p class="text-sm text-gray-500">Las solicitudes pendientes y los horarios fijos también ocupan la sede.</p>
@if($esDirigente)<section x-cloak x-show="detalle" class="ui-panel p-6 space-y-6" aria-live="polite">
<h2 class="text-xl font-semibold" x-text="detalle?.titulo">
</h2>
<dl class="ui-meta">
<div>
<dt>Fecha y horario</dt>
<dd x-text="detalle?.horario">
</dd>
</div>
<div>
<dt>Estado</dt>
<dd x-text="detalle?.estado">
</dd>
</div>
<div>
<dt>Responsable / representante</dt>
<dd x-text="detalle?.persona">
</dd>
</div>
<div>
<dt>Registrado por</dt>
<dd x-text="detalle?.autor">
</dd>
</div>
</dl>
<p x-show="detalle?.fijo" class="ui-notice">Este horario ocupa la sede aunque su reserva aún no se haya generado.</p>
<button class="ui-button ui-button-secondary" type="button" @click="detalle = null">Cerrar detalle</button>
</section>
<details class="ui-panel p-6">
<summary class="cursor-pointer font-semibold">Imprimir horario semanal o mensual</summary>
<form method="GET" action="{{ route('calendario.imprimir') }}" target="horario-imprimible" class="ui-tools mt-6" @submit="imprimir = true">
<div>
<x-input-label for="fecha_impresion" value="Fecha del período" />
<x-date-input id="fecha_impresion" name="fecha" :value="$lunes->toDateString()" required />
</div>
<div>
<x-input-label for="periodo_impresion" value="Período" />
<select id="periodo_impresion" name="periodo" class="ui-input">
<option value="semana">Semana de la fecha elegida</option>
<option value="mes">Mes de la fecha elegida</option>
</select>
</div>
<x-primary-button>Ver horario imprimible</x-primary-button>
</form>
</details>
<section x-cloak x-show="imprimir" class="ui-panel p-6 space-y-4">
<h2 class="text-xl font-semibold">Vista previa del horario</h2>
<iframe title="Horario imprimible" name="horario-imprimible" class="h-[640px] w-full border-0">
</iframe>
<button type="button" class="ui-button ui-button-secondary" @click="imprimir = false">Cerrar vista previa</button>
</section>
@endif
</div>
</x-app-layout>
