<?php

use App\Models\Certificado;
use App\Models\HorarioAtencion;
use App\Models\Reserva;
use App\Models\Socio;
use App\Models\User;

beforeEach(function () {
    $this->presidente5 = User::factory()->create(['role' => 'presidente']);
    $this->actingAs($this->presidente5);
});

test('presidente y secretario publican varios tramos semanales visibles sin iniciar sesión', function (string $rol) {
    $this->actingAs(User::factory()->create(['role' => $rol]));
    foreach ([['09:00', '12:00'], ['15:00', '18:00']] as [$inicio, $fin]) {
        $this->post(route('atencion.store'), ['dia_semana' => 1, 'hora_inicio' => $inicio, 'hora_fin' => $fin])
            ->assertRedirect(route('atencion.index'))->assertSessionHasNoErrors();
    }
    $this->get(route('atencion.index'))->assertOk();
    $this->assertDatabaseCount('horarios_atencion', 2);
    $this->assertDatabaseCount('citas', 0);
    $this->assertDatabaseCount('reservas', 0);
    auth()->logout();
    $this->get('/')->assertOk()->assertSee('Lunes')->assertSee('09:00–12:00')->assertSee('15:00–18:00');
})->with(['presidente', 'secretario']);

test('los tramos permiten continuidad pero rechazan superposiciones incluso al editar', function () {
    $primero = HorarioAtencion::create(['dia_semana' => 1, 'hora_inicio' => '09:00:00', 'hora_fin' => '12:00:00']);
    $this->post(route('atencion.store'), ['dia_semana' => 1, 'hora_inicio' => '12:00', 'hora_fin' => '13:00'])
        ->assertSessionHasNoErrors();
    $segundo = HorarioAtencion::whereKeyNot($primero->id)->sole();
    $this->from(route('atencion.index'))->post(route('atencion.store'), [
        'dia_semana' => 1, 'hora_inicio' => '11:00', 'hora_fin' => '12:30',
    ])->assertSessionHasErrors('hora_inicio');
    $this->put(route('atencion.update', $segundo), [
        'dia_semana' => 1, 'hora_inicio' => '11:30', 'hora_fin' => '13:00',
    ])->assertSessionHasErrors('hora_inicio');
    expect($segundo->fresh()->hora_inicio)->toBe('12:00:00');
    $this->put(route('atencion.update', $primero), [
        'dia_semana' => 2, 'hora_inicio' => '09:00', 'hora_fin' => '12:00',
    ])->assertSessionHasNoErrors();
    expect($primero->fresh()->dia_semana)->toBe(2);
    $this->delete(route('atencion.destroy', $segundo))->assertRedirect(route('atencion.index'));
    $this->assertDatabaseMissing('horarios_atencion', ['id' => $segundo->id]);
});

test('atención valida día y horas sin aceptar intervalos invertidos', function (array $datos, string $campo) {
    $this->post(route('atencion.store'), $datos)->assertSessionHasErrors($campo);
    $this->assertDatabaseCount('horarios_atencion', 0);
})->with([
    [['dia_semana' => 8, 'hora_inicio' => '09:00', 'hora_fin' => '10:00'], 'dia_semana'],
    [['dia_semana' => 1, 'hora_inicio' => '25:00', 'hora_fin' => '10:00'], 'hora_inicio'],
    [['dia_semana' => 1, 'hora_inicio' => '10:00', 'hora_fin' => '09:00'], 'hora_fin'],
    [['dia_semana' => 1, 'hora_inicio' => '10:00', 'hora_fin' => '10:00'], 'hora_fin'],
]);

test('los demás roles no pueden gestionar atención aunque conozcan las rutas', function (string $rol) {
    $horario = HorarioAtencion::create(['dia_semana' => 1, 'hora_inicio' => '09:00:00', 'hora_fin' => '12:00:00']);
    $this->actingAs(User::factory()->create(['role' => $rol]));
    $datos = ['dia_semana' => 2, 'hora_inicio' => '10:00', 'hora_fin' => '11:00'];
    $this->get(route('atencion.index'))->assertForbidden();
    $this->post(route('atencion.store'), $datos)->assertForbidden();
    $this->put(route('atencion.update', $horario), $datos)->assertForbidden();
    $this->delete(route('atencion.destroy', $horario))->assertForbidden();
    $this->assertDatabaseCount('horarios_atencion', 1);
})->with(['admin', 'tesorero', 'vecino']);

test('las citas de firma se crean fuera del horario público y aprobar documentos solo recuerda habilitarlas', function () {
    HorarioAtencion::create(['dia_semana' => 1, 'hora_inicio' => '09:00:00', 'hora_fin' => '12:00:00']);
    $socio = Socio::create([
        'nombre_completo' => 'Vecina por revisar', 'rut' => '12345678-5', 'direccion' => 'Pasaje 100',
        'telefono' => '912345678', 'residente_desde' => '2020-01-01', 'estado' => 'pendiente',
    ]);
    $this->followingRedirects()->patch(route('socios.update', $socio), ['estado' => 'por_firmar'])
        ->assertOk()->assertSee('Recuerda habilitar horas disponibles')->assertSee(route('agenda.index'));
    $this->assertDatabaseCount('citas', 0);
    $this->post(route('agenda.store'), [
        'fecha' => now()->addDay()->toDateString(), 'hora_inicio' => '18:00', 'hora_fin' => '19:00', 'duracion' => 30,
    ])->assertSessionHasNoErrors();
    $this->assertDatabaseCount('citas', 2);
    $this->assertDatabaseCount('horarios_atencion', 1);
});

test('los puntos verdes indican pendientes para la directiva y desaparecen al resolverlos', function (string $rol) {
    $socio = Socio::create([
        'nombre_completo' => 'Solicitud pendiente', 'rut' => '12345678-5', 'direccion' => 'Pasaje 100',
        'telefono' => '912345678', 'residente_desde' => '2020-01-01', 'estado' => 'pendiente',
    ]);
    $certificado = Certificado::create([
        'tipo' => 'residencia', 'nombre_solicitante' => 'Certificado pendiente', 'rut_solicitante' => '12345678-5',
        'direccion' => 'Pasaje 100', 'dirigido_a' => 'Banco',
    ]);
    $reserva = Reserva::create([
        'tipo' => 'arriendo', 'estado' => 'pendiente', 'inicio' => now()->addDay()->setTime(10, 0),
        'fin' => now()->addDay()->setTime(11, 0), 'motivo' => 'Arriendo', 'vence_en' => now()->addHours(48),
    ]);
    $this->actingAs(User::factory()->create(['role' => $rol]));
    $respuesta = $this->get(route('dashboard'))->assertOk();
    expect(substr_count($respuesta->getContent(), 'Hay operaciones pendientes de revisión'))->toBe(3);

    $socio->update(['estado' => 'por_firmar']);
    $certificado->forceFill(['estado' => 'rechazado'])->save();
    // Aun sin scheduler: una solicitud vencida ya no debe encender el aviso.
    $reserva->update(['vence_en' => now()->subMinute()]);
    $this->get(route('dashboard'))->assertOk()->assertDontSee('Hay operaciones pendientes de revisión');
})->with(['presidente', 'secretario']);

test('el panel del presidente muestra solicitudes por revisar sin exponerlas a otros roles', function () {
    $socio = Socio::create([
        'nombre_completo' => 'Pendiente del panel', 'rut' => '12345678-5', 'direccion' => 'Pasaje 100',
        'telefono' => '912345678', 'residente_desde' => '2020-01-01', 'estado' => 'pendiente',
    ]);
    $certificado = Certificado::create([
        'tipo' => 'residencia', 'nombre_solicitante' => 'Certificado del panel', 'rut_solicitante' => '12345678-5',
        'direccion' => 'Pasaje 100', 'dirigido_a' => 'Banco',
    ]);
    $this->get(route('dashboard'))->assertOk()->assertSee('Pendiente del panel')->assertSee('Certificado del panel')
        ->assertSee(route('socios.index', ['estado' => 'pendientes']))
        ->assertSee(route('admin.certificados.index', ['estado' => 'solicitado']));
    foreach (['admin', 'tesorero', 'vecino'] as $rol) {
        $this->actingAs(User::factory()->create(['role' => $rol]))->get(route('dashboard'))->assertOk()
            ->assertDontSee('Pendiente del panel')->assertDontSee('Certificado del panel')->assertDontSee('Hay operaciones pendientes de revisión')
            ->assertViewHas('sociosPendientes', fn ($lista) => $lista->isEmpty())
            ->assertViewHas('certificadosPendientes', fn ($lista) => $lista->isEmpty());
    }
});

test('finanzas agrupa sus enlaces y conserva los permisos de cada rol', function (string $rol, bool $permitido) {
    $respuesta = $this->actingAs(User::factory()->create(['role' => $rol]))->get(route('dashboard'))->assertOk();
    if ($permitido) {
        $respuesta->assertSee('Finanzas')->assertSee('Ingresos y egresos')->assertSee(route('ingresos.index'))
            ->assertSee(route('finanzas.arriendos.index'));
        $this->get(route('ingresos.index'))->assertOk()->assertSee(route('egresos.index'));
        $this->get(route('egresos.index'))->assertOk()->assertSee(route('ingresos.index'));
        $this->get(route('finanzas.arriendos.index'))->assertOk();
    } else {
        $respuesta->assertDontSee(route('finanzas.arriendos.index'));
        $this->get(route('finanzas.arriendos.index'))->assertForbidden();
    }
})->with([['presidente', true], ['tesorero', true], ['secretario', false], ['admin', false], ['vecino', false]]);
