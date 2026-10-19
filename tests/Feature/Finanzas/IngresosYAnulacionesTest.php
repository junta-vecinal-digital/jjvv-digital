<?php

use App\Models\Auditoria;
use App\Models\Ingreso;
use App\Models\User;

function presidenteFinanzas(): User
{
    $usuario = new User(['name' => 'Presidente', 'email' => uniqid().'@test.cl', 'password' => 'secreto123']);
    $usuario->forceFill(['role' => 'presidente', 'email_verified_at' => now()])->save();

    return $usuario;
}

test('una cuota de socios exige indicar si es ordinaria o extraordinaria', function () {
    $this->actingAs(presidenteFinanzas());

    $this->post(route('ingresos.store'), [
        'concepto' => 'cuota_socios',
        'fecha_pago' => now()->toDateString(),
        'monto' => 5000,
        'medio_pago' => 'efectivo',
        'talonario' => 'T-2',
    ])->assertSessionHasErrors('tipo_cuota');
});

test('anular un ingreso con motivo lo saca del saldo y queda en auditoría', function () {
    $usuario = presidenteFinanzas();
    $this->actingAs($usuario);

    $ingreso = Ingreso::create([
        'concepto' => 'otros',
        'fecha_pago' => now()->toDateString(),
        'monto' => 1000,
        'medio_pago' => 'efectivo',
        'talonario' => 'T-1',
        'observaciones' => 'Prueba',
        'registrado_por' => $usuario->id,
    ]);

    $saldoAntes = Ingreso::saldoCaja();

    $this->post(route('ingresos.anular', $ingreso), ['motivo_anulacion' => 'Pago duplicado'])
        ->assertRedirect(route('ingresos.index'));

    expect($ingreso->fresh()->estaAnulado())->toBeTrue()
        ->and(Ingreso::saldoCaja())->toBe($saldoAntes - 1000)
        ->and(Auditoria::where('auditable_type', Ingreso::class)
            ->where('auditable_id', $ingreso->id)
            ->where('evento', 'modificado')
            ->exists())->toBeTrue();
});

test('no se puede anular sin motivo ni dos veces', function () {
    $usuario = presidenteFinanzas();
    $this->actingAs($usuario);

    $ingreso = Ingreso::create([
        'concepto' => 'otros',
        'fecha_pago' => now()->toDateString(),
        'monto' => 1000,
        'medio_pago' => 'efectivo',
        'talonario' => 'T-1',
        'observaciones' => 'Prueba',
        'registrado_por' => $usuario->id,
    ]);

    $this->post(route('ingresos.anular', $ingreso), ['motivo_anulacion' => ''])
        ->assertSessionHasErrors('motivo_anulacion');

    $this->post(route('ingresos.anular', $ingreso), ['motivo_anulacion' => 'Primera vez']);
    $this->post(route('ingresos.anular', $ingreso), ['motivo_anulacion' => 'Segunda vez'])
        ->assertSessionHasErrors('ingreso');
});