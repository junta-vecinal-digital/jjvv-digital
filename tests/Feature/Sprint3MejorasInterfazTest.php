<?php

use App\Models\Grupo;
use App\Models\Proyecto;
use App\Models\Reserva;
use App\Models\Socio;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
    $this->presidenteInterfaz = User::factory()->create(['role' => 'presidente']);
});

test('la gestión se separa del calendario y guarda en Reservas con las mismas validaciones', function (string $rol) {
    $this->actingAs(User::factory()->create(['role' => $rol]));
    $this->get(route('calendario.index'))->assertOk()
        ->assertDontSee('action="'.route('reservas.store').'"', false)->assertDontSee('Cancelar reserva');
    $this->get(route('reservas.index'))->assertOk()->assertSee('action="'.route('reservas.store').'"', false)
        ->assertSee(route('inventario.index'));
    $fecha = now()->addDay();
    $this->post(route('reservas.store'), [
        'fecha' => $fecha->toDateString(), 'hora_inicio' => '10:00', 'hora_fin' => '11:00', 'tipo' => 'actividad',
    ])->assertSessionHasNoErrors()->assertRedirect(route('reservas.index', ['semana' => $fecha->copy()->startOfWeek()->toDateString()]));
    $this->assertDatabaseCount('reservas', 1);
    $this->post(route('reservas.store'), [
        'fecha' => $fecha->toDateString(), 'hora_inicio' => '10:00', 'hora_fin' => '11:00', 'tipo' => 'actividad',
    ])->assertSessionHasErrors('reserva');
    $this->assertDatabaseCount('reservas', 1);
})->with(['presidente', 'secretario']);

test('los demás roles no obtienen gestión de reservas por los nuevos enlaces', function (string $rol) {
    $this->actingAs(User::factory()->create(['role' => $rol]))->get(route('reservas.index'))->assertForbidden();
})->with(['admin', 'tesorero', 'vecino']);

test('la etiqueta de representante conserva su carácter informativo sin conceder permisos', function (bool $vinculoUsuario) {
    $usuario = User::factory()->create(['role' => 'vecino']);
    $socio = Socio::create([
        'user_id' => $usuario->id, 'nombre_completo' => 'Representante inactivo', 'rut' => '12345678-5',
        'direccion' => 'Pasaje 123', 'telefono' => '912345678', 'residente_desde' => '2020-01-01', 'estado' => 'inactivo',
    ]);
    $grupo = Grupo::create([
        'nombre' => 'Grupo de prueba', 'representante_id' => $vinculoUsuario ? null : $socio->id,
        'representante_usuario_id' => $vinculoUsuario ? $usuario->id : null, 'representante_nombre' => $socio->nombre_completo,
    ]);
    $this->actingAs($this->presidenteInterfaz)->get(route('socios.show', $socio))->assertOk()->assertSee('Representante de agrupación');
    $this->actingAs($usuario)->get(route('socios.create'))->assertOk()->assertSee('Representante de agrupación');
    $this->get(route('reservas.index'))->assertForbidden();
    $this->get(route('calendario.index'))->assertForbidden();
    expect($socio->fresh()->estado)->toBe('inactivo')->and($usuario->fresh()->role)->toBe('vecino');
    $grupo->update(['representante_id' => null, 'representante_usuario_id' => null, 'representante_nombre' => null]);
    expect($socio->esRepresentante())->toBeFalse();
})->with([false, true]);

test('la edición de proyectos conserva el nombre histórico sin adivinar una cuenta', function () {
    $proyecto = Proyecto::create([
        'titulo' => 'Proyecto anterior', 'organismo' => 'Municipalidad', 'fecha_postulacion' => '2026-01-01',
        'estado' => 'postulado', 'postulado_por' => 'Persona histórica sin cuenta', 'registrado_por' => $this->presidenteInterfaz->id,
    ]);
    $this->actingAs($this->presidenteInterfaz)->get(route('proyectos.edit', $proyecto))->assertOk()
        ->assertSee('Conservar: Persona histórica sin cuenta');
    $this->put(route('proyectos.update', $proyecto), [
        'titulo' => 'Título editado', 'organismo' => 'Municipalidad', 'fecha_postulacion' => '2026-01-01',
        'estado' => 'adjudicado', 'postulador_id' => '__conservar', 'postulado_por' => 'Nombre manipulado',
    ])->assertSessionHasNoErrors();
    expect($proyecto->fresh()->postulado_por)->toBe('Persona histórica sin cuenta')
        ->and($proyecto->fresh()->postulador_id)->toBeNull()->and($proyecto->fresh()->titulo)->toBe('Título editado');
});

test('proyectos acepta solo dirigentes y guarda su nombre desde el servidor', function () {
    $secretario = User::factory()->create(['role' => 'secretario', 'name' => 'Secretario real']);
    $datos = ['titulo' => 'Proyecto nuevo', 'organismo' => 'Municipalidad', 'fecha_postulacion' => '2026-01-01',
        'estado' => 'postulado', 'postulador_id' => $secretario->id, 'postulado_por' => 'Nombre manipulado'];
    $this->actingAs($this->presidenteInterfaz)->post(route('proyectos.store'), $datos)->assertSessionHasNoErrors();
    expect(Proyecto::sole()->postulado_por)->toBe('Secretario real')->and(Proyecto::sole()->postulador_id)->toBe($secretario->id);
    $vecino = User::factory()->create(['role' => 'vecino']);
    $datos['postulador_id'] = $vecino->id;
    $this->post(route('proyectos.store'), $datos)->assertSessionHasErrors('postulador_id');
    $datos['postulador_id'] = '__conservar';
    $this->post(route('proyectos.store'), $datos)->assertSessionHasErrors('postulador_id');
    $this->assertDatabaseCount('proyectos', 1);
});

test('el detalle de arriendo vuelve a la gestión cuando se abrió desde Reservas', function () {
    $reserva = Reserva::create([
        'tipo' => 'arriendo', 'estado' => 'confirmada', 'inicio' => now()->addDay()->setTime(10, 0),
        'fin' => now()->addDay()->setTime(11, 0), 'motivo' => 'Arriendo', 'valor_arriendo' => 40000, 'valor_garantia' => 40000,
    ]);
    $this->actingAs($this->presidenteInterfaz)->get(route('arriendos.show', [$reserva, 'desde' => 'reservas']))
        ->assertOk()->assertViewHas('volverReservas', true)->assertSee('Volver a reservas');
    $this->get(route('arriendos.show', $reserva))->assertOk()->assertViewHas('volverReservas', false);
});
