<?php

use App\Models\Auditoria;
use App\Models\Ingreso;
use App\Models\Parametro;
use App\Models\User;

test('el administrador puede cambiar un parámetro y queda registrado', function () {
    $administrador = User::factory()->create(['role' => 'admin']);

    $this->actingAs($administrador)
        ->put('/parametros', [
            'valores' => [
                'sede_hora_inicio' => '08:00',
                'sede_hora_fin' => '22:00',
                'sede_duracion_bloque_min' => '60',
                'certificado_vigencia_dias' => '90',
                'certificado_plazo_descarga_dias' => '5',
                'certificado_residencia_valor' => '1000',
                'arriendo_plazo_confirmacion_horas' => '48',
                'arriendo_valor' => '40000',
                'arriendo_garantia' => '40000',
            ],
        ])
        ->assertRedirect(route('parametros.index', ['tab' => 'general']));

    expect(Parametro::valor('sede_hora_inicio'))->toBe('08:00');
    expect(\App\Models\Auditoria::where('auditable_type', Parametro::class)->count())->toBeGreaterThan(0);
});

test('un vecino no puede ver ni cambiar parámetros', function () {
    $vecino = User::factory()->create(['role' => 'vecino']);

    $this->actingAs($vecino)->get('/parametros')->assertForbidden();
});

test('el tesorero registra un ingreso en efectivo con talonario', function () {
    $tesorero = User::factory()->create(['role' => 'tesorero']);

    $this->actingAs($tesorero)->post('/ingresos', [
        'concepto' => 'certificados',
        'fecha_pago' => today()->toDateString(),
        'monto' => 1000,
        'medio_pago' => 'efectivo',
        'talonario' => 'T-0042',
    ])->assertRedirect('/ingresos');

    expect(Ingreso::where('talonario', 'T-0042')->exists())->toBeTrue();
});

test('un ingreso en efectivo sin talonario se rechaza', function () {
    $tesorero = User::factory()->create(['role' => 'tesorero']);

    $this->actingAs($tesorero)->post('/ingresos', [
        'concepto' => 'certificados',
        'fecha_pago' => today()->toDateString(),
        'monto' => 1000,
        'medio_pago' => 'efectivo',
    ])->assertSessionHasErrors('talonario');
});
