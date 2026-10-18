<?php

use App\Models\Grupo;
use App\Models\Reserva;
use App\Models\TipoActividad;
use App\Models\User;

test('un representante manual puede reservar sin ficha ni cuenta y se conserva su identidad histórica', function () {
    $presidente = User::factory()->create(['role' => 'presidente']);
    $this->actingAs($presidente);
    $datos = [
        'nombre' => 'Taller vecinal', 'origen_representante' => 'manual',
        'representante_nombre' => 'Ana Representante', 'representante_rut' => '12345678-5', 'representante_telefono' => '912345678',
        'usar_contacto_representante' => 1,
    ];
    $this->post(route('grupos.store'), $datos)->assertSessionHasNoErrors()->assertRedirect();
    $grupo = Grupo::sole();
    expect($grupo->tieneRepresentanteVigente())->toBeTrue()
        ->and($grupo->representante_id)->toBeNull()->and($grupo->contacto_nombre)->toBe('Ana Representante');
    $this->assertDatabaseCount('socios', 0);
    $this->assertDatabaseCount('users', 1);
    $this->post(route('reservas.store'), [
        'tipo' => 'grupo', 'grupo_id' => $grupo->id, 'fecha' => now()->addDay()->toDateString(),
        'hora_inicio' => '10:00', 'hora_fin' => '11:00',
    ])->assertSessionHasNoErrors()->assertRedirect();
    $this->put(route('grupos.update', $grupo), array_replace($datos, ['representante_nombre' => 'Otro representante']))
        ->assertSessionHasNoErrors();
    expect(Reserva::sole()->responsable_nombre)->toBe('Ana Representante');
    $this->put(route('grupos.update', $grupo), ['nombre' => $grupo->nombre, 'origen_representante' => 'sin_representante'])
        ->assertSessionHasNoErrors();
    expect($grupo->fresh()->tieneRepresentanteVigente())->toBeFalse();
    $this->post(route('reservas.store'), [
        'tipo' => 'grupo', 'grupo_id' => $grupo->id, 'fecha' => now()->addDay()->toDateString(),
        'hora_inicio' => '11:00', 'hora_fin' => '12:00',
    ])->assertSessionHasErrors('reserva');
    $this->assertDatabaseCount('reservas', 1);
});

test('un dirigente sin ficha puede autocompletar el representante y copiar su contacto', function () {
    $presidente = User::factory()->create(['role' => 'presidente']);
    $this->actingAs($presidente);
    $this->get(route('grupos.index'))->assertOk();
    $this->post(route('grupos.store'), [
        'nombre' => 'Grupo dirigido', 'origen_representante' => 'usuario:'.$presidente->id,
        'representante_nombre' => $presidente->name, 'representante_correo' => $presidente->email,
        'representante_rut' => '12345678-5',
        'usar_contacto_representante' => 1,
    ])->assertSessionHasNoErrors();
    expect(Grupo::sole()->representante_usuario_id)->toBe($presidente->id)
        ->and(Grupo::sole()->contacto_correo)->toBe($presidente->email);
    $this->assertDatabaseCount('socios', 0);
});

test('renombrar y desactivar una actividad gratuita conserva las reservas anteriores', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $presidente = User::factory()->create(['role' => 'presidente']);
    $this->actingAs($admin)->post(route('parametros.actividades.store'), ['nombre_actividad' => 'Taller cultural'])
        ->assertSessionHasNoErrors();
    $tipo = TipoActividad::where('nombre', 'Taller cultural')->sole();
    $datos = ['tipo' => $tipo->clave, 'fecha' => now()->addDay()->toDateString(), 'hora_inicio' => '10:00', 'hora_fin' => '11:00'];
    $this->actingAs($presidente)->post(route('reservas.store'), $datos)->assertSessionHasNoErrors();
    expect(Reserva::sole()->tipo_nombre)->toBe('Taller cultural');
    $this->actingAs($admin)->put(route('parametros.actividades.update', $tipo), ['nombre_actividad' => 'Encuentro cultural', 'activo' => 0])
        ->assertSessionHasNoErrors();
    expect(Reserva::sole()->etiquetaTipo())->toBe('Taller cultural');
    $this->actingAs($presidente)->post(route('reservas.store'), array_replace($datos, ['hora_inicio' => '11:00', 'hora_fin' => '12:00']))
        ->assertSessionHasErrors('tipo');
    $this->assertDatabaseCount('reservas', 1);
});

test('los roles distintos de administrador no pueden modificar el catálogo', function (string $rol) {
    $this->actingAs(User::factory()->create(['role' => $rol]));
    $tipo = TipoActividad::where('clave', 'actividad')->sole();
    $this->post(route('parametros.actividades.store'), ['nombre_actividad' => 'No permitido'])->assertForbidden();
    $this->put(route('parametros.actividades.update', $tipo), ['nombre_actividad' => 'No permitido', 'activo' => 0])->assertForbidden();
    expect($tipo->fresh()->activo)->toBeTrue();
})->with(['presidente', 'secretario', 'tesorero', 'vecino']);
