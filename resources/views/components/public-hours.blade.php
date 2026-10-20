@props(['horarios'])
<section class="ui-panel p-6 sm:p-8">
<h2 class="text-xl font-semibold">Horarios de atención al público</h2>
<p class="mt-2 text-sm text-gray-500">Estos horarios se repiten cada semana.</p>
<dl class="mt-5 divide-y divide-gray-200">
@foreach(\App\Models\HorarioAtencion::DIAS as $dia=>$nombre)<div class="grid gap-2 py-4 sm:grid-cols-2">
<dt class="font-medium">{{ $nombre }}</dt>
<dd class="text-sm">
@forelse($horarios->get($dia,collect()) as $horario){{ substr($horario->hora_inicio,0,5) }}–{{ substr($horario->hora_fin,0,5) }}<br>
@empty Sin atención programada @endforelse
</dd>
</div>
@endforeach
</dl>
<p class="mt-6 text-sm">Para firmar el libro de socios, reserva una cita desde tu cuenta una vez aprobada tu documentación.</p>
</section>
