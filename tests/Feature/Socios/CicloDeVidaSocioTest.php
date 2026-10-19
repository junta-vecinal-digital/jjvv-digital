<?php

use App\Models\Socio;
use App\Models\User;

function usuarioCiclo(string $rol): User
{
    $usuario = User::factory()->create();
    $usuario->forceFill(['role' => $rol])->save();

    return $usuario->fresh();
}

function fichaCiclo(string $estado, ?User $usuario = null, array $extra = [], string $rut = '12345678-5'): Socio
{
    $ficha = (new Socio)->forceFill($extra + [
        'user_id' => $usuario?->id,
        'nombre_completo' => 'María González',
        'rut' => $rut,
        'direccion' => 'Calle Falsa 123',
        'telefono' => '111',
        'residente_desde' => now()->subYear()->toDateString(),
        'estado' => $estado,
    ]);
    $ficha->save();

    return $ficha;
}

it('permite las transiciones válidas del ciclo de vida', function (string $desde, string $hasta, array $extra) {
    $ficha = fichaCiclo($desde);

    $this->actingAs(usuarioCiclo('secretario'))
        ->patch(route('socios.update', $ficha), ['estado' => $hasta] + $extra)
        ->assertRedirect(route('socios.show', $ficha));

    expect($ficha->fresh()->estado)->toBe($hasta);
})->with([
    'aprobar documentos' => ['pendiente', 'por_firmar', []],
    'rechazar' => ['pendiente', 'rechazado', ['observaciones' => 'Boleta ilegible']],
    'activar tras la firma' => ['por_firmar', 'activo', []],
    'reabrir una solicitud rechazada' => ['rechazado', 'pendiente', []],
    'dar de baja' => ['activo', 'inactivo', ['motivo_baja' => 'Se cambió de sector', 'fecha_baja' => '2020-01-01']],
    'reactivar' => ['inactivo', 'activo', []],
]);

it('bloquea los saltos de estado inválidos', function (string $desde, string $hasta) {
    $ficha = fichaCiclo($desde);

    $this->actingAs(usuarioCiclo('secretario'))
        ->patch(route('socios.update', $ficha), [
            'estado' => $hasta,
            'motivo_baja' => 'x',
            'fecha_baja' => '2020-01-01',
            'observaciones' => 'x',
        ])
        ->assertSessionHasErrors('estado');

    expect($ficha->fresh()->estado)->toBe($desde);
})->with([
    'pendiente a activo (sin firma)' => ['pendiente', 'activo'],
    'pendiente a inactivo' => ['pendiente', 'inactivo'],
    'activo a pendiente' => ['activo', 'pendiente'],
    'rechazado a activo' => ['rechazado', 'activo'],
    'por firmar a inactivo' => ['por_firmar', 'inactivo'],
]);

it('exige motivo para rechazar y motivo y fecha para dar de baja', function () {
    $secretario = usuarioCiclo('secretario');

    $pendiente = fichaCiclo('pendiente');
    $this->actingAs($secretario)
        ->patch(route('socios.update', $pendiente), ['estado' => 'rechazado'])
        ->assertSessionHasErrors('observaciones');

    $activo = fichaCiclo('activo', null, [], '11111111-1');
    $this->actingAs($secretario)
        ->patch(route('socios.update', $activo), ['estado' => 'inactivo'])
        ->assertSessionHasErrors(['motivo_baja', 'fecha_baja']);

    expect($pendiente->fresh()->estado)->toBe('pendiente')
        ->and($activo->fresh()->estado)->toBe('activo');
});

it('al reactivar conserva la fecha de ingreso y limpia los datos de la baja', function () {
    $ficha = fichaCiclo('inactivo', null, [
        'fecha_ingreso' => '2024-05-01',
        'motivo_baja' => 'Se cambió de sector',
        'fecha_baja' => '2025-01-10',
    ]);

    $this->actingAs(usuarioCiclo('presidente'))
        ->patch(route('socios.update', $ficha), ['estado' => 'activo'])
        ->assertRedirect();

    $ficha->refresh();

    expect($ficha->estado)->toBe('activo')
        ->and($ficha->fecha_ingreso->toDateString())->toBe('2024-05-01')
        ->and($ficha->motivo_baja)->toBeNull()
        ->and($ficha->fecha_baja)->toBeNull();
});

it('retira los accesos de socio apenas se da de baja', function () {
    $this->withoutVite();

    $vecino = usuarioCiclo('vecino');
    $ficha = fichaCiclo('activo', $vecino);

    $this->actingAs($vecino)->get(route('certificados.index'))->assertOk();

    $this->actingAs(usuarioCiclo('presidente'))
        ->patch(route('socios.update', $ficha), [
            'estado' => 'inactivo',
            'motivo_baja' => 'Se cambió de sector',
            'fecha_baja' => '2020-01-01',
        ])
        ->assertRedirect();

    $this->actingAs($vecino->fresh())->get(route('certificados.index'))->assertForbidden();
});

it('deja que el vecino corrija su solicitud mientras está pendiente', function () {
    $vecino = usuarioCiclo('vecino');
    $ficha = fichaCiclo('pendiente', $vecino, ['observaciones' => 'Boleta ilegible']);

    $this->actingAs($vecino)
        ->put(route('socios.reenviar'), [
            'nombre_completo' => 'María González',
            'rut' => '12345678-5',
            'direccion' => 'Calle Nueva 456',
            'telefono' => '999',
            'residente_desde' => now()->subYear()->toDateString(),
        ])
        ->assertRedirect(route('socios.create'));

    $ficha->refresh();

    expect($ficha->direccion)->toBe('Calle Nueva 456')
        ->and($ficha->telefono)->toBe('999')
        ->and($ficha->estado)->toBe('pendiente');
});

it('no deja que el vecino modifique su ficha fuera del estado pendiente', function (string $estado) {
    $vecino = usuarioCiclo('vecino');
    fichaCiclo($estado, $vecino);

    $this->actingAs($vecino)
        ->put(route('socios.reenviar'), [
            'nombre_completo' => 'María González',
            'rut' => '12345678-5',
            'direccion' => 'Calle Nueva 456',
            'telefono' => '999',
            'residente_desde' => now()->subYear()->toDateString(),
        ])
        ->assertForbidden();
})->with(['por_firmar', 'activo', 'rechazado', 'inactivo']);

it('no deja que un vecino cambie estados de socios', function () {
    $ficha = fichaCiclo('pendiente');

    $this->actingAs(usuarioCiclo('vecino'))
        ->patch(route('socios.update', $ficha), ['estado' => 'por_firmar'])
        ->assertForbidden();
});