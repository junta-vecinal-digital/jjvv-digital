<?php

use App\Models\Grupo;
use App\Models\HorarioFijo;
use App\Models\Parametro;
use App\Models\Reserva;
use App\Models\Socio;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    $this->withoutVite();
    $this->travelTo(Carbon::parse('2026-10-05 08:00:00'));
    $this->directiva3a = User::factory()->create(['role' => 'presidente']);
    $this->grupo3a = Grupo::create(['nombre' => 'Taller de prueba 3a', 'representante_nombre' => 'Representante de prueba 3a', 'representante_rut' => '12345678-5']);
    $this->actingAs($this->directiva3a);
});

afterEach(function () {
    $this->travelBack();
});

function horarioBloque3a(Grupo $grupo, array $cambios = []): HorarioFijo
{
    return $grupo->horariosFijos()->create(array_replace([
        'dia_semana' => 1, 'hora_inicio' => '10:00', 'hora_fin' => '12:00',
    ], $cambios));
}

function datosReservaBloque3a(array $cambios = []): array
{
    return array_replace([
        'fecha' => '2026-11-02', 'hora_inicio' => '10:00', 'hora_fin' => '11:00', 'tipo' => 'actividad',
    ], $cambios);
}

test('directiva genera todas las ocurrencias del mes confirmadas sin duplicar ni cambiar autor', function (string $rol) {
    $autor = User::factory()->create(['role' => $rol]);
    $horario = horarioBloque3a($this->grupo3a);
    $this->actingAs($autor)->post(route('grupos.horarios.generar', $this->grupo3a), ['mes' => '2026-11'])
        ->assertSessionHasNoErrors()->assertRedirect(route('grupos.show', $this->grupo3a));
    $reservas = Reserva::orderBy('inicio')->get();
    expect($reservas->pluck('inicio')->map->toDateString()->all())->toBe([
        '2026-11-02', '2026-11-09', '2026-11-16', '2026-11-23', '2026-11-30',
    ]);
    foreach ($reservas as $reserva) {
        expect($reserva->estado)->toBe('confirmada')->and($reserva->horario_fijo_id)->toBe($horario->id)
            ->and($reserva->grupo_id)->toBe($this->grupo3a->id)->and($reserva->socio_id)->toBeNull()
            ->and($reserva->responsable_nombre)->toBe('Representante de prueba 3a')
            ->and($reserva->creado_por)->toBe($autor->id)->and($reserva->inicio->format('H:i'))->toBe('10:00');
    }
    $ids = $reservas->modelKeys();
    $this->actingAs($this->directiva3a)->post(route('grupos.horarios.generar', $this->grupo3a), ['mes' => '2026-11'])
        ->assertSessionHasNoErrors();
    expect(Reserva::orderBy('inicio')->pluck('id')->all())->toBe($ids);
    expect(Reserva::where('creado_por', $autor->id)->count())->toBe(5);
})->with(['presidente', 'secretario']);

test('el mes actual omite ocurrencias que ya comenzaron', function () {
    horarioBloque3a($this->grupo3a);
    $this->travelTo(Carbon::parse('2026-10-14 12:00:00'));
    $this->post(route('grupos.horarios.generar', $this->grupo3a), ['mes' => '2026-10'])->assertSessionHasNoErrors();
    expect(Reserva::orderBy('inicio')->get()->pluck('inicio')->map->toDateString()->all())
        ->toBe(['2026-10-19', '2026-10-26']);
});

test('generación rechaza meses fuera del rango o mal formados', function (string $mes, string $campo) {
    horarioBloque3a($this->grupo3a);
    $this->post(route('grupos.horarios.generar', $this->grupo3a), ['mes' => $mes])->assertSessionHasErrors($campo);
    $this->assertDatabaseCount('reservas', 0);
})->with([['2026-09', 'horario'], ['2027-05', 'horario'], ['noviembre', 'mes']]);

test('editar un horario conserva las reservas generadas y aplica el cambio al mes siguiente', function () {
    $horario = horarioBloque3a($this->grupo3a);
    $this->post(route('grupos.horarios.generar', $this->grupo3a), ['mes' => '2026-11'])->assertSessionHasNoErrors();
    $anteriores = Reserva::orderBy('id')->get()->map->getAttributes()->all();
    $this->put(route('grupos.horarios.update', $horario), [
        'dia_semana' => 1, 'hora_inicio' => '14:00', 'hora_fin' => '16:00',
    ])->assertSessionHasNoErrors();
    expect(Reserva::orderBy('id')->get()->map->getAttributes()->all())->toBe($anteriores);
    $this->post(route('grupos.horarios.generar', $this->grupo3a), ['mes' => '2026-12'])->assertSessionHasNoErrors();
    $nuevas = Reserva::whereMonth('inicio', 12)->get();
    expect($nuevas)->toHaveCount(4);
    foreach ($nuevas as $reserva) expect($reserva->inicio->format('H:i'))->toBe('14:00');
});

test('eliminar un horario conserva sus reservas y desvincula solo la recurrencia', function () {
    $horario = horarioBloque3a($this->grupo3a);
    $this->post(route('grupos.horarios.generar', $this->grupo3a), ['mes' => '2026-11'])->assertSessionHasNoErrors();
    $antes = Reserva::orderBy('id')->get()->map(fn ($r) => collect($r->getAttributes())->except('horario_fijo_id')->all())->all();
    $this->delete(route('grupos.horarios.destroy', $horario))->assertSessionHasNoErrors();
    $this->assertDatabaseMissing('horarios_fijos', ['id' => $horario->id]);
    $despues = Reserva::orderBy('id')->get();
    expect($despues)->toHaveCount(5)->and($despues->pluck('horario_fijo_id')->unique()->all())->toBe([null]);
    expect($despues->map(fn ($r) => collect($r->getAttributes())->except('horario_fijo_id')->all())->all())->toBe($antes);
});

test('cancelar una ocurrencia libera solo ese día y no la regenera ni imprime como horario fijo', function () {
    horarioBloque3a($this->grupo3a);
    $this->post(route('grupos.horarios.generar', $this->grupo3a), ['mes' => '2026-11'])->assertSessionHasNoErrors();
    $reserva = Reserva::orderBy('inicio')->firstOrFail();
    $this->post(route('reservas.cancelar', $reserva))->assertSessionHasNoErrors();
    $this->post(route('grupos.horarios.generar', $this->grupo3a), ['mes' => '2026-11'])->assertSessionHasNoErrors();
    expect(Reserva::count())->toBe(5)->and($reserva->fresh()->estado)->toBe('cancelada');
    $this->get(route('calendario.index', ['semana' => '2026-11-02']))->assertOk()
        ->assertViewHas('celdas', fn ($celdas) => ! isset($celdas['2026-11-02']['10:00']));
    $this->get(route('calendario.index', ['semana' => '2026-11-09']))->assertOk()
        ->assertViewHas('celdas', fn ($celdas) => isset($celdas['2026-11-09']['10:00']));
    $this->get(route('calendario.imprimir', ['periodo' => 'mes', 'fecha' => '2026-11-02']))->assertOk()
        ->assertViewHas('agenda', fn ($agenda) => collect($agenda)->first(fn ($d) => $d['fecha']->toDateString() === '2026-11-02')['filas']->isEmpty());
    $this->post(route('reservas.store'), datosReservaBloque3a())->assertSessionHasNoErrors();
    $this->post(route('reservas.store'), datosReservaBloque3a(['fecha' => '2026-11-09']))->assertSessionHasErrors('reserva');
});

test('un grupo sin representante no agrega horarios ni genera reservas y conserva las anteriores', function () {
    horarioBloque3a($this->grupo3a);
    $this->post(route('grupos.horarios.generar', $this->grupo3a), ['mes' => '2026-11'])->assertSessionHasNoErrors();
    $this->grupo3a->update(['representante_nombre' => null]);
    $this->post(route('grupos.horarios.store', $this->grupo3a), [
        'dia_semana' => 2, 'hora_inicio' => '10:00', 'hora_fin' => '11:00',
    ])->assertSessionHasErrors('horario');
    $this->post(route('grupos.horarios.generar', $this->grupo3a), ['mes' => '2026-12'])->assertSessionHasErrors('horario');
    $this->assertDatabaseCount('reservas', 5);
    $this->assertDatabaseCount('horarios_fijos', 1);
});

test('horarios fijos sin generar bloquean reservas y otros horarios pero admiten un bloque contiguo', function () {
    horarioBloque3a($this->grupo3a);
    $this->post(route('reservas.store'), datosReservaBloque3a())->assertSessionHasErrors('reserva');
    $otroGrupo = Grupo::create(['nombre' => 'Otro taller 3a', 'representante_nombre' => 'Otra persona', 'representante_rut' => '11111111-1']);
    $this->post(route('grupos.horarios.store', $otroGrupo), [
        'dia_semana' => 1, 'hora_inicio' => '11:00', 'hora_fin' => '13:00',
    ])->assertSessionHasErrors('horario');
    $this->post(route('reservas.store'), datosReservaBloque3a(['hora_inicio' => '12:00', 'hora_fin' => '13:00']))
        ->assertSessionHasNoErrors();
    $this->assertDatabaseCount('reservas', 1);
});

test('los bloques y límites de sede se validan para reservas y horarios fijos', function (int $bloque, string $inicio, string $fin, bool $valido) {
    Parametro::where('clave', 'sede_duracion_bloque_min')->update(['valor' => (string) $bloque]);
    $datos = ['hora_inicio' => $inicio, 'hora_fin' => $fin];
    $respuesta = $this->post(route('reservas.store'), datosReservaBloque3a($datos));
    if ($valido) $respuesta->assertSessionHasNoErrors();
    else $respuesta->assertSessionHasErrors('reserva');
    // Otro día: valida los límites sin chocar con la reserva recién ingresada.
    $respuesta = $this->post(route('grupos.horarios.store', $this->grupo3a), ['dia_semana' => 2] + $datos);
    if ($valido) $respuesta->assertSessionHasNoErrors();
    else $respuesta->assertSessionHasErrors('horario');
    $this->assertDatabaseCount('reservas', $valido ? 1 : 0);
    $this->assertDatabaseCount('horarios_fijos', $valido ? 1 : 0);
})->with([
    [60, '09:00', '10:00', true], [60, '21:00', '22:00', true],
    [60, '09:30', '10:30', false], [30, '09:30', '10:30', true],
    [30, '09:15', '10:15', false], [60, '08:00', '09:00', false], [60, '22:00', '23:00', false],
]);

test('cambiar apertura y bloques conserva la ocupación de los intervalos originales', function (string $apertura, array $ocupadas, array $libres) {
    Reserva::create([
        'tipo' => 'actividad', 'estado' => 'confirmada', 'motivo' => 'Reserva de media hora original',
        'inicio' => '2026-11-02 10:30:00', 'fin' => '2026-11-02 11:30:00', 'creado_por' => $this->directiva3a->id,
    ]);
    Parametro::where('clave', 'sede_hora_inicio')->update(['valor' => $apertura]);
    Parametro::where('clave', 'sede_duracion_bloque_min')->update(['valor' => '60']);
    $this->get(route('calendario.index', ['semana' => '2026-11-02']))->assertOk()
        ->assertViewHas('celdas', function ($celdas) use ($ocupadas, $libres) {
            foreach ($ocupadas as $hora) if (($celdas['2026-11-02'][$hora]['tipo'] ?? null) !== 'reserva') return false;
            foreach ($libres as $hora) if (isset($celdas['2026-11-02'][$hora])) return false;
            return true;
        });
    $this->post(route('reservas.store'), datosReservaBloque3a([
        'hora_inicio' => $ocupadas[0], 'hora_fin' => Carbon::parse($ocupadas[0])->addHour()->format('H:i'),
    ]))->assertSessionHasErrors('reserva');
})->with([
    ['09:00', ['10:00', '11:00'], ['09:00', '12:00']],
    ['09:30', ['10:30'], ['09:30', '11:30']],
]);

test('el socio consulta solo ocupación sin detalles y no puede gestionar ni imprimir', function () {
    horarioBloque3a($this->grupo3a);
    $usuario = User::factory()->create(['role' => 'vecino']);
    Socio::create([
        'user_id' => $usuario->id, 'nombre_completo' => 'Socio calendario 3a', 'rut' => '12345678-5',
        'direccion' => 'Calle 123', 'telefono' => '912345678', 'residente_desde' => '2020-01-01', 'estado' => 'activo',
    ]);
    $this->actingAs($usuario)->get(route('calendario.index', ['semana' => '2026-11-02']))->assertOk()
        ->assertSee('Ocupado')->assertDontSee($this->grupo3a->nombre)->assertDontSee('Representante de prueba 3a')
        ->assertViewHas('celdas', fn ($celdas) => $celdas['2026-11-02']['10:00']['texto'] === 'Ocupado');
    $this->post(route('reservas.store'), datosReservaBloque3a())->assertForbidden();
    $this->post(route('grupos.horarios.generar', $this->grupo3a), ['mes' => '2026-11'])->assertForbidden();
    $this->get(route('calendario.imprimir', ['periodo' => 'mes', 'fecha' => '2026-11-02']))->assertForbidden();
});

test('impresión semanal y mensual incluye fijos sin duplicar reservas y omite canceladas y vencidas', function (string $rol, string $periodo, int $dias) {
    horarioBloque3a($this->grupo3a);
    $this->post(route('grupos.horarios.generar', $this->grupo3a), ['mes' => '2026-11'])->assertSessionHasNoErrors();
    foreach (['cancelada', 'pendiente'] as $estado) Reserva::create([
        'tipo' => 'arriendo', 'estado' => $estado, 'motivo' => 'Actividad excluida '.$estado,
        'inicio' => '2026-11-03 15:00:00', 'fin' => '2026-11-03 16:00:00',
        'vence_en' => $estado === 'pendiente' ? now()->subMinute() : null,
    ]);
    $this->actingAs(User::factory()->create(['role' => $rol]))
        ->get(route('calendario.imprimir', ['periodo' => $periodo, 'fecha' => '2026-11-02']))->assertOk()
        ->assertSee('Lunes 02/11/2026')->assertDontSee('Actividad excluida')
        ->assertViewHas('agenda', function ($agenda) use ($dias) {
            $lunes = collect($agenda)->first(fn ($d) => $d['fecha']->toDateString() === '2026-11-02');
            return count($agenda) === $dias && $lunes['filas']->count() === 1;
        });
    $this->get(route('calendario.imprimir', ['periodo' => 'semana', 'fecha' => '2026-12-07']))->assertOk()
        ->assertSee('Programado (reserva aún sin generar)');
})->with(['presidente', 'secretario'])->with([['semana', 7], ['mes', 30]]);
