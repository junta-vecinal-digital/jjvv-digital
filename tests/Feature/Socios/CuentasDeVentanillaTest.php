<?php

use App\Models\Auditoria;
use App\Models\Socio;
use App\Models\User;

function socioVentanilla(array $cambios = []): Socio
{
    return Socio::create(array_merge([
        'nombre_completo' => 'Socio de ventanilla',
        'rut' => '12345678-5',
        'email' => 'socio@example.com',
        'direccion' => 'Calle 1',
        'telefono' => '912345678',
        'residente_desde' => '2020-01-01',
        'estado' => 'por_firmar',
    ], $cambios));
}

test('el presidente crea la cuenta de un socio de ventanilla', function () {
    $presidente = User::factory()->create(['role' => 'presidente']);
    $socio = socioVentanilla();

    $this->actingAs($presidente)->post("/socios/{$socio->id}/cuenta", [
        'email' => 'socio@example.com',
        'password' => 'clave-segura-123',
        'password_confirmation' => 'clave-segura-123',
    ])->assertRedirect();

    $socio->refresh();
    $usuario = User::where('email', 'socio@example.com')->first();

    expect($usuario)->not->toBeNull()
        ->and($usuario->role)->toBe('vecino')
        ->and($usuario->email_verified_at)->not->toBeNull()
        ->and($socio->user_id)->toBe($usuario->id)
        ->and(password_verify('clave-segura-123', $usuario->password))->toBeTrue();
});

test('la creación de la cuenta queda en la auditoría', function () {
    $presidente = User::factory()->create(['role' => 'presidente']);
    $socio = socioVentanilla();

    $this->actingAs($presidente)->post("/socios/{$socio->id}/cuenta", [
        'email' => 'socio@example.com',
        'password' => 'clave-segura-123',
        'password_confirmation' => 'clave-segura-123',
    ]);

    expect(Auditoria::where('auditable_type', User::class)->where('evento', 'creado')->exists())->toBeTrue();
});

test('no se crea una segunda cuenta para el mismo socio', function () {
    $presidente = User::factory()->create(['role' => 'presidente']);
    $socio = socioVentanilla();

    $this->actingAs($presidente)->post("/socios/{$socio->id}/cuenta", [
        'email' => 'socio@example.com',
        'password' => 'clave-segura-123',
        'password_confirmation' => 'clave-segura-123',
    ]);

    $this->actingAs($presidente)->post("/socios/{$socio->id}/cuenta", [
        'email' => 'otro@example.com',
        'password' => 'clave-segura-123',
        'password_confirmation' => 'clave-segura-123',
    ])->assertStatus(422);
});

test('un correo ya usado se rechaza', function () {
    $presidente = User::factory()->create(['role' => 'presidente']);
    User::factory()->create(['email' => 'ocupado@example.com']);
    $socio = socioVentanilla();

    $this->actingAs($presidente)->post("/socios/{$socio->id}/cuenta", [
        'email' => 'ocupado@example.com',
        'password' => 'clave-segura-123',
        'password_confirmation' => 'clave-segura-123',
    ])->assertSessionHasErrors('email');
});

test('un vecino no puede crear cuentas de socios', function () {
    $vecino = User::factory()->create(['role' => 'vecino']);
    $socio = socioVentanilla();

    $this->actingAs($vecino)->post("/socios/{$socio->id}/cuenta", [
        'email' => 'socio@example.com',
        'password' => 'clave-segura-123',
        'password_confirmation' => 'clave-segura-123',
    ])->assertForbidden();
});