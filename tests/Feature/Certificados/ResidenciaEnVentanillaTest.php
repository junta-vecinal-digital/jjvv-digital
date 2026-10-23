<?php

use App\Models\Certificado;
use App\Models\DescargaCertificado;
use App\Models\Ingreso;
use App\Models\Socio;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->withoutVite();
    Storage::fake('local');
});

function datosVentanillaResidencia(array $cambios = []): array
{
    return array_merge([
        'tipo' => 'residencia',
        'aporte' => 1000,
        'nombre_solicitante' => 'Vecina de prueba',
        'rut_solicitante' => '12345678-5',
        'direccion' => 'Calle 1',
        'dirigido_a' => 'Banco',
        'documentos_verificados' => '1',
        'residencia_6_meses' => '1',
        'pago_verificado' => '1',
        'medio_pago' => 'efectivo',
        'talonario' => 'T-9',
        'numero_transaccion' => 'TRX-VENTANILLA-9',
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

test('la emisión valida el pago y el ingreso se aprueba después conservando el talonario', function (string $rol) {
    $presidente = User::factory()->create(['role' => $rol]);

    $this->actingAs($presidente)->post('/admin/certificados', datosVentanillaResidencia())->assertSessionHasNoErrors();

    $certificado = Certificado::sole();
    $this->assertDatabaseCount('ingresos', 0);
    if ($rol === 'secretario') {
        $this->post(route('ingresos.certificados.registrar', $certificado))->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'tesorero']));
    }
    $this->get(route('ingresos.index'))->assertOk()->assertViewHas('certificados', fn ($lista) => $lista->contains('id', $certificado->id));
    $this->post(route('ingresos.certificados.registrar', $certificado))->assertSessionHasNoErrors();
    $this->post(route('ingresos.certificados.registrar', $certificado))->assertSessionHasNoErrors();
    $this->assertDatabaseCount('ingresos', 1);
    $ingreso = Ingreso::where('medio_pago', 'efectivo')->where('talonario', 'T-9')->first();
    expect($ingreso)->not->toBeNull()
        ->and($ingreso->monto)->toBe(1000)
        ->and($ingreso->concepto)->toBe('certificados');
})->with(['presidente', 'secretario']);

test('el registro de retiro ya no está disponible ni cambia la vigencia', function () {
    $presidente = User::factory()->create(['role' => 'presidente']);
    $this->actingAs($presidente)->post('/admin/certificados', datosVentanillaResidencia())->assertSessionHasNoErrors();

    $certificado = Certificado::latest('id')->first();
    $vigenciaAntes = $certificado->fecha_vencimiento->toDateString();

    $this->actingAs($presidente)
        ->post("/admin/certificados/{$certificado->id}/retirar", ['fecha_retiro' => today()->toDateString()])
        ->assertNotFound();

    $certificado->refresh();
    expect($certificado->fecha_retiro)->toBeNull()
        ->and($certificado->fecha_vencimiento->toDateString())->toBe($vigenciaAntes);
});

test('el detalle conserva las descargas compactas sin ofrecer registrar retiro', function () {
    $presidente = User::factory()->create(['role' => 'presidente']);
    $this->actingAs($presidente)->post('/admin/certificados', datosVentanillaResidencia())->assertSessionHasNoErrors();

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
        ->assertNotFound();
    $this->get(route('admin.certificados.show', $certificado))->assertOk()
        ->assertSee('Descargas: 1')->assertSee($vecino->name)->assertSee('127.0.0.1')
        ->assertDontSee('Registrar retiro')->assertDontSee('Retiro en la sede');
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
test('el pago positivo de un certificado exige número de transacción en ambos medios', function (string $medio) {
    $secretario = User::factory()->create(['role' => 'secretario']);
    $datos = datosVentanillaResidencia(['medio_pago' => $medio, 'numero_transaccion' => 'TRX-123']);
    unset($datos['numero_transaccion']);
    $this->actingAs($secretario)->post(route('admin.certificados.store'), $datos)->assertSessionHasErrors('numero_transaccion');
    $this->assertDatabaseCount('certificados', 0);
    $this->assertDatabaseCount('ingresos', 0);
    $this->post(route('admin.certificados.store'), $datos + ['numero_transaccion' => 'TRX-123'])->assertSessionHasNoErrors();
    expect(Certificado::sole()->numero_transaccion)->toBe('TRX-123')->and(Certificado::sole()->comprobante_path)->toBeNull();
})->with(['efectivo', 'transferencia']);
