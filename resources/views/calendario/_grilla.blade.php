<div class="ui-panel overflow-x-auto">
<table class="ui-calendar">
<caption class="sr-only">Ocupación de la sede</caption>
<thead>
<tr>
<th scope="col">Hora</th>
@foreach($diasVisibles as $dia)<th scope="col">{{ ucfirst($dia->translatedFormat('D')) }}<span class="ui-sub">{{ $dia->format('d/m') }}{{ $dia->isToday() ? ' · Hoy' : '' }}</span>
</th>
@endforeach
</tr>
</thead>
<tbody>
@foreach($horas as $hora)<tr>
<th scope="row">{{ $hora }}</th>
@foreach($diasVisibles as $dia)@php($celda = $celdas[$dia->toDateString()][$hora] ?? null)<td class="{{ $celda ? ($celda['tipo'] === 'fijo' ? 'ui-calendar-fixed' : 'bg-brand-50') : '' }}">
@if($esDirigente && $celda)@foreach($celda['detalles'] as $evento)<button type="button" class="ui-calendar-event" @click="detalle = @js($evento)">{{ $evento['titulo'] }}<span class="ui-sub">{{ $evento['estado'] }}</span>
</button>
@endforeach @else
<span class="text-xs text-gray-500">{{ $celda ? 'Ocupado' : 'Libre' }}</span>
@endif
</td>
@endforeach
</tr>
@endforeach
</tbody>
</table>
</div>
