<section class="ui-panel p-4 space-y-2">
    <h3 class="font-semibold">Horarios de atención para retirar insumos</h3>
    @forelse ($horarios as $horario)
        <p>{{ \App\Models\HorarioAtencion::DIAS[$horario->dia_semana] }}: {{ substr($horario->hora_inicio, 0, 5) }} a {{ substr($horario->hora_fin, 0, 5) }}</p>
    @empty
        <p>La directiva aún no publica horarios de atención. Coordina el retiro con presidente o secretario.</p>
    @endforelse
</section>
