<?php

use App\Models\Auditoria;
use App\Models\Certificado;
use App\Models\Ingreso;
use App\Models\Socio;
use App\Models\User;

test('una descarga de residencia queda en la auditoría', function () {
    $usuario = User::factory()->create(['role' => 'vecino']);
    $socio = Socio::create([
        'user_id' => $usuario->id,
        'nombre_completo' => 'Socio de prueba',
        'rut' => '12345678-5',
        'email' => $usuario->email,
        'direccion' => 'Calle 1',
        'telefono' => '912345678',
        'residente_desde' => '2020-01-01',
        'fecha_ingreso' => '2026-01-01',
        'estado' => 'activo',
    ]);

    $certificado = Certificado::create([
        'tipo' => 'residencia',
        'user_id' => $usuario->id,
        'socio_id' => $socio->id,
        'nombre_solicitante' => 'Socio de prueba',
        'rut_solicitante' => '12345678-5',
        'direccion' => 'Calle 1',
        'dirigido_a' => 'Banco',
        'numero_transaccion' => 'TRX-9',
        'comprobante_path' => 'comprobantes/prueba.pdf',
    ]);
    $certificado->forceFill([
        'estado' => 'emitido',
        'folio' => '2026-0100',
        'anio' => 2026,
        'correlativo' => 100,
        'fecha_emision' => now()->toDateString(),
        'fecha_vencimiento' => now()->addDays(90)->toDateString(),
        'descarga_disponible_hasta' => now()->addDays(5),
    ])->save();

    $this->actingAs($usuario)->get("/certificados/{$certificado->id}/pdf")->assertOk();

    expect(Auditoria::where('evento', 'descargado')
        ->where('auditable_type', Certificado::class)
        ->where('auditable_id', $certificado->id)
        ->exists())->toBeTrue();
});

test('el filtro de finanzas muestra los ingresos', function () {
    $presidente = User::factory()->create(['role' => 'presidente']);

    Ingreso::create([
        'concepto' => 'otros',
        'fecha_pago' => today()->toDateString(),
        'monto' => 500,
        'medio_pago' => 'efectivo',
        'talonario' => 'T-1',
        'observaciones' => 'Prueba',
        'registrado_por' => $presidente->id,
    ]);

    $this->actingAs($presidente)->get('/auditoria?modelo=finanzas')
        ->assertOk()
        ->assertSee('Ingreso #');
});

test('la auditoría muestra las bajas de socios', function () {
    $presidente = User::factory()->create(['role' => 'presidente']);

    Socio::create([
        'nombre_completo' => 'Socio dado de baja',
        'rut' => '11111111-1',
        'email' => 'baja@example.com',
        'direccion' => 'Calle 2',
        'telefono' => '900000000',
        'residente_desde' => '2019-01-01',
        'fecha_ingreso' => '2020-01-01',
        'estado' => 'inactivo',
        'motivo_baja' => 'Motivo de prueba',
        'fecha_baja' => '2026-09-01',
    ]);

    $this->actingAs($presidente)->get('/auditoria')
        ->assertOk()
        ->assertSee('Socios dados de baja')
        ->assertSee('Motivo de prueba');
});

test('una fecha hasta anterior a la fecha desde se rechaza', function () {
    $presidente = User::factory()->create(['role' => 'presidente']);

    $this->actingAs($presidente)
        ->get('/auditoria?desde=2026-10-10&hasta=2026-10-01')
        ->assertSessionHasErrors('hasta');
});