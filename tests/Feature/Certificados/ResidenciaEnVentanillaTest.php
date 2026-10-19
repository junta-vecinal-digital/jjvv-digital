<?php

use App\Models\Certificado;
use App\Models\DescargaCertificado;
use App\Models\Ingreso;
use App\Models\Socio;
use App\Models\User;

function datosVentanillaResidencia(array $cambios = []): array
{
    return array_merge([
        'tipo' => 'residencia',
        'nombre_solicitante' => 'Vecina de prueba',
        'rut_solicitante' => '12345678-5',
        'direccion' => 'Calle 1',
        'dirigido_a' => 'Banco',
        'documentos_verificados' => '1',
        'residencia_6_meses' => '1',
        'pago_verificado' => '1',
        'medio_pago' => 'efectivo',
        'talonario' => 'T-9',
    ], $cambios);
}

test('la emisión en ventanilla exige medio de pago', function () {
    $presidente = User::factory()->create(['role' => 'presidente']);

    $datos = datosVentanillaResidencia();
    unset($datos['medio_pago']);

    $this->actingAs($presidente)
        ->post('/admin/certificados', $datos)
        ->assertSessionHasErrors('medio_pago');
});

test('la emisión en ventanilla registra el ingreso con el talonario', function () {
    $presidente = User::factory()->create(['role' => 'presidente']);

    $this->actingAs($presidente)->post('/admin/certificados', datosVentanillaResidencia());

    $ingreso = Ingreso::where('medio_pago', 'efectivo')->where('talonario', 'T-9')->first();
    expect($ingreso)->not->toBeNull()
        ->and($ingreso->monto)->toBe(1000)
        ->and($ingreso->concepto)->toBe('certificados');
});

test('el retiro se registra sin descarga previa', function () {
    $presidente = User::factory()->create(['role' => 'presidente']);
    $this->actingAs($presidente)->post('/admin/certificados', datosVentanillaResidencia());

    $certificado = Certificado::latest('id')->first();
    $vigenciaAntes = $certificado->fecha_vencimiento->toDateString();

    $this->actingAs($presidente)
        ->post("/admin/certificados/{$certificado->id}/retirar", ['fecha_retiro' => today()->toDateString()])
        ->assertRedirect();

    $certificado->refresh();
    expect($certificado->fecha_retiro)->not->toBeNull()
        ->and($certificado->fecha_vencimiento->toDateString())->toBe($vigenciaAntes);
});

test('no se puede retirar un certificado que ya fue descargado', function () {
    $presidente = User::factory()->create(['role' => 'presidente']);
    $this->actingAs($presidente)->post('/admin/certificados', datosVentanillaResidencia());

    $certificado = Certificado::latest('id')->first();
    $vecino = User::factory()->create(['role' => 'vecino']);

    DescargaCertificado::create([
        'certificado_id' => $certificado->id,
        'user_id' => $vecino->id,
        'ip' => '127.0.0.1',
        'descargado_en' => now(),
    ]);

    $this->actingAs($presidente)
        ->post("/admin/certificados/{$certificado->id}/retirar", ['fecha_retiro' => today()->toDateString()])
        ->assertStatus(422);
});

test('un socio inactivo no puede solicitar certificados', function () {
    $usuario = User::factory()->create(['role' => 'vecino']);
    Socio::create([
        'user_id' => $usuario->id,
        'nombre_completo' => 'Socio inactivo',
        'rut' => '12345678-5',
        'email' => $usuario->email,
        'direccion' => 'Calle 1',
        'telefono' => '912345678',
        'residente_desde' => '2020-01-01',
        'fecha_ingreso' => '2026-01-01',
        'estado' => 'inactivo',
    ]);

    $this->actingAs($usuario)
        ->post('/certificados', [
            'tipo' => 'residencia',
            'nombre_solicitante' => 'Socio inactivo',
            'rut_solicitante' => '12345678-5',
            'direccion' => 'Calle 1',
            'dirigido_a' => 'Banco',
            'numero_transaccion' => 'TRX-1',
        ])
        ->assertForbidden();
});