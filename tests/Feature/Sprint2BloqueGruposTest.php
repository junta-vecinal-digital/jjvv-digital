<?php

use App\Models\Grupo;
use App\Models\Socio;
use App\Models\User;

function dirigenteGrupos(): User
{
    $usuario = new User(['name' => 'Presidente', 'email' => uniqid().'@test.cl', 'password' => 'secreto123']);
    $usuario->forceFill(['role' => 'presidente', 'email_verified_at' => now()])->save();

    return $usuario;
}

function socioActivoConCuenta(): Socio
{
    $cuenta = User::factory()->create();

    return Socio::create([
        'user_id' => $cuenta->id,
        'nombre_completo' => 'Juan Pérez',
        'rut' => uniqid().'-K',
        'email' => $cuenta->email,
        'direccion' => 'Calle 1',
        'telefono' => '+56911111111',
        'residente_desde' => now()->subYears(2)->toDateString(),
        'estado' => 'activo',
    ]);
}

test('asigna un representante activo con cuenta y copia sus datos al grupo', function () {
    $this->actingAs(dirigenteGrupos());
    $grupo = Grupo::create(['nombre' => 'Club de Adulto Mayor']);
    $socio = socioActivoConCuenta();

    $this->post(route('grupos.representante', $grupo), ['socio_id' => $socio->id])
        ->assertRedirect();

    $grupo->refresh();
    expect($grupo->representante_id)->toBe($socio->id)
        ->and($grupo->contacto_nombre)->toBe('Juan Pérez')
        ->and($grupo->tieneRepresentanteVigente())->toBeTrue();
});

test('acepta como representante a un socio sin cuenta de usuario', function () {
    $this->actingAs(dirigenteGrupos());
    $grupo = Grupo::create(['nombre' => 'Taller de Costura']);
    $socio = socioActivoConCuenta();
    $socio->update(['user_id' => null]);

    $this->post(route('grupos.representante', $grupo), ['socio_id' => $socio->id])
        ->assertSessionHasNoErrors()->assertRedirect();

    expect($grupo->fresh()->representante_id)->toBe($socio->id)
        ->and($grupo->fresh()->tieneRepresentanteVigente())->toBeTrue();
});

test('al dar de baja al representante, el grupo conserva su representante', function () {
    $this->actingAs(dirigenteGrupos());
    $grupo = Grupo::create(['nombre' => 'Junta de Vigilancia']);
    $socio = socioActivoConCuenta();
    $grupo->update(['representante_id' => $socio->id]);

    $this->patch(route('socios.update', $socio), [
        'estado' => 'inactivo',
        'motivo_baja' => 'Renuncia',
        'fecha_baja' => now()->toDateString(),
    ])->assertRedirect();

    expect($grupo->fresh()->representante_id)->toBe($socio->id)
        ->and($grupo->fresh()->tieneRepresentanteVigente())->toBeTrue();
});
