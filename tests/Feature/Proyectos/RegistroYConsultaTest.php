<?php

use App\Models\Proyecto;
use App\Models\Socio;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
    $this->presidenteProyectos = User::factory()->create(['role' => 'presidente']);
    User::factory()->create(['role' => 'secretario', 'name' => 'Encargada de postulación externa']);
    $this->proyecto = Proyecto::create([
        'titulo' => 'Mejoramiento de la sede', 'organismo' => 'Municipalidad',
        'fecha_postulacion' => '2026-02-01', 'estado' => 'postulado', 'monto' => null,
        'postulado_por' => 'Persona que postuló', 'registrado_por' => $this->presidenteProyectos->id,
    ]);
});

function datosProyectoPrueba(array $cambios = []): array
{
    return array_replace([
        'titulo' => 'Equipamiento comunitario', 'organismo' => 'Gobierno Regional',
        'fecha_postulacion' => '2026-03-01', 'estado' => 'postulado', 'monto' => null,
        'postulador_id' => User::where('role', 'secretario')->value('id'),
    ], $cambios);
}

test('el presidente registra con monto opcional y autor del sistema distinto del postulador externo', function (?int $monto) {
    $otro = User::factory()->create();
    $this->actingAs($this->presidenteProyectos)->get(route('proyectos.create'))->assertOk();
    $respuesta = $this->post(route('proyectos.store'), datosProyectoPrueba([
        'monto' => $monto, 'registrado_por' => $otro->id,
    ]))->assertSessionHasNoErrors();
    $proyecto = Proyecto::latest('id')->first();
    $respuesta->assertRedirect(route('proyectos.index'))->assertSessionHas('_old_input.operacion', 'proyecto_'.$proyecto->id);
    expect($proyecto->registrado_por)->toBe($this->presidenteProyectos->id)
        ->and($proyecto->postulado_por)->toBe('Encargada de postulación externa')
        ->and($proyecto->monto)->toBe($monto);
    $vista = $this->get(route('proyectos.show', $proyecto))->assertOk()
        ->assertSee($this->presidenteProyectos->name)->assertSee('Encargada de postulación externa')->assertSee('01/03/2026');
    $monto === null ? $vista->assertSee('Sin indicar') : $vista->assertSee('$'.number_format($monto, 0, ',', '.'));
})->with(['sin monto' => [null], 'monto cero' => [0], 'con monto' => [2500000]]);

test('el presidente edita todos los datos sin cambiar al registrador original', function (string $estado) {
    $nuevoPresidente = User::factory()->create(['role' => 'presidente']);
    $this->actingAs($nuevoPresidente)->get(route('proyectos.edit', $this->proyecto))->assertOk()
        ->assertSee('Mejoramiento de la sede');
    $this->put(route('proyectos.update', $this->proyecto), datosProyectoPrueba([
        'estado' => $estado, 'monto' => 5000000, 'registrado_por' => $nuevoPresidente->id,
    ]))->assertSessionHasNoErrors()->assertRedirect(route('proyectos.show', $this->proyecto));
    $proyecto = $this->proyecto->fresh();
    expect($proyecto->titulo)->toBe('Equipamiento comunitario')->and($proyecto->organismo)->toBe('Gobierno Regional')
        ->and($proyecto->fecha_postulacion->toDateString())->toBe('2026-03-01')->and($proyecto->estado)->toBe($estado)
        ->and($proyecto->monto)->toBe(5000000)->and($proyecto->postulado_por)->toBe('Encargada de postulación externa')
        ->and($proyecto->registrado_por)->toBe($this->presidenteProyectos->id);
})->with(['postulado', 'adjudicado', 'rechazado']);

test('editar permite dejar el monto sin indicar', function () {
    $this->proyecto->update(['monto' => 2500000]);
    $this->actingAs($this->presidenteProyectos)->put(route('proyectos.update', $this->proyecto), datosProyectoPrueba(['monto' => '']))
        ->assertSessionHasNoErrors();
    expect($this->proyecto->fresh()->monto)->toBeNull();
});

test('todos los roles con sesión consultan proyectos y su estado', function (string $rol) {
    $usuario = User::factory()->create(['role' => $rol]);
    $this->actingAs($usuario)->get(route('proyectos.index'))->assertOk()->assertSee('Mejoramiento de la sede');
    $this->get(route('proyectos.show', $this->proyecto))->assertOk()->assertSee('Municipalidad')->assertSee('Postulado');
    $this->get(route('dashboard'))->assertOk()->assertSee(route('proyectos.index'));
})->with(['presidente', 'secretario', 'tesorero', 'admin', 'vecino']);

test('la consulta no depende del estado de la ficha de socio', function (string $estado) {
    $usuario = User::factory()->create(['role' => 'vecino']);
    Socio::create([
        'user_id' => $usuario->id, 'nombre_completo' => 'Vecina', 'rut' => '12345678-5', 'direccion' => 'Pasaje 123',
        'telefono' => '912345678', 'residente_desde' => '2020-01-01', 'estado' => $estado,
    ]);
    $this->actingAs($usuario)->get(route('proyectos.index'))->assertOk();
    $this->get(route('proyectos.show', $this->proyecto))->assertOk();
})->with(['pendiente', 'por_firmar', 'activo', 'rechazado', 'inactivo']);

test('los demás roles solo consultan y no pueden registrar ni editar por URL', function (string $rol) {
    $this->actingAs(User::factory()->create(['role' => $rol]));
    $this->get(route('proyectos.index'))->assertOk()->assertDontSee(route('proyectos.create'));
    $this->get(route('proyectos.show', $this->proyecto))->assertOk()->assertDontSee(route('proyectos.edit', $this->proyecto));
    $this->get(route('proyectos.create'))->assertForbidden();
    $this->get(route('proyectos.edit', $this->proyecto))->assertForbidden();
    $this->post(route('proyectos.store'), datosProyectoPrueba())->assertForbidden();
    $this->put(route('proyectos.update', $this->proyecto), datosProyectoPrueba())->assertForbidden();
    $this->assertDatabaseCount('proyectos', 1);
    expect($this->proyecto->fresh()->titulo)->toBe('Mejoramiento de la sede');
})->with(['secretario', 'tesorero', 'admin', 'vecino']);

test('el listado filtra por estado y mantiene los resultados paginados', function () {
    $postulador = User::where('role', 'secretario')->firstOrFail();
    for ($indice = 0; $indice < 21; $indice++) {
        Proyecto::create(datosProyectoPrueba([
            'titulo' => 'Proyecto '.$indice, 'estado' => 'adjudicado', 'postulado_por' => $postulador->name,
        ]));
    }
    $this->actingAs(User::factory()->create())->get(route('proyectos.index', ['estado' => 'adjudicado']))->assertOk()
        ->assertViewHas('proyectos', fn ($lista) => $lista->total() === 21 && $lista->count() === 20
            && $lista->every(fn ($proyecto) => $proyecto->estado === 'adjudicado'));
    $this->get(route('proyectos.index', ['estado' => 'adjudicado', 'page' => 2]))->assertOk()
        ->assertViewHas('proyectos', fn ($lista) => $lista->count() === 1);
    $this->getJson(route('proyectos.index', ['estado' => 'inventado']))->assertUnprocessable();
});

test('registro y edición validan los datos sin modificar registros ante errores', function (array $cambios, string $campo) {
    $this->actingAs($this->presidenteProyectos);
    $datos = datosProyectoPrueba($cambios);
    $this->post(route('proyectos.store'), $datos)->assertSessionHasErrors($campo);
    $this->put(route('proyectos.update', $this->proyecto), $datos)->assertSessionHasErrors($campo);
    $this->assertDatabaseCount('proyectos', 1);
    expect($this->proyecto->fresh()->titulo)->toBe('Mejoramiento de la sede');
})->with([
    [['titulo' => '   '], 'titulo'],
    [['organismo' => ''], 'organismo'],
    [['fecha_postulacion' => '31/12/2026'], 'fecha_postulacion'],
    [['estado' => 'pendiente'], 'estado'],
    [['monto' => -1], 'monto'],
    [['monto' => '1.50'], 'monto'],
    [['postulador_id' => ''], 'postulador_id'],
    [['postulador_id' => ['nombre']], 'postulador_id'],
]);

test('el formulario sugiere organismos y exige seleccionar un dirigente como postulador', function () {
    $this->actingAs($this->presidenteProyectos)->get(route('proyectos.create'))->assertOk()
        ->assertViewHas('organismos', fn ($lista) => $lista->contains('Municipalidad'))
        ->assertViewHas('postuladores', fn ($lista) => $lista->every(fn ($usuario) => $usuario->esDirigente()));
    $this->post(route('proyectos.store'), datosProyectoPrueba(['postulador_id' => null, 'postulado_por' => 'Persona sin cuenta']))
        ->assertSessionHasErrors('postulador_id');
    $this->assertDatabaseCount('proyectos', 1);
});

test('los datos se muestran escapados como texto', function () {
    $this->proyecto->update(['titulo' => '<script>alert(1)</script>']);
    $this->actingAs(User::factory()->create())->get(route('proyectos.show', $this->proyecto))->assertOk()
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
});

test('proyectos requiere iniciar sesión incluso para consultar', function () {
    foreach (['proyectos.index', 'proyectos.create'] as $ruta) {
        $this->get(route($ruta))->assertRedirect(route('login'));
    }
    $this->get(route('proyectos.show', $this->proyecto))->assertRedirect(route('login'));
    $this->get(route('proyectos.edit', $this->proyecto))->assertRedirect(route('login'));
    $this->post(route('proyectos.store'), datosProyectoPrueba())->assertRedirect(route('login'));
    $this->put(route('proyectos.update', $this->proyecto), datosProyectoPrueba())->assertRedirect(route('login'));
});
