<?php

use App\Models\Certificado;
use App\Models\DescargaCertificado;
use App\Models\Ingreso;
use App\Models\Parametro;
use App\Models\Socio;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Storage::fake('local'));

function socioActivo(): User
{
    $usuario = User::factory()->create(['role' => 'vecino']);

    Socio::create([
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

    return $usuario;
}

function solicitudResidencia(User $usuario): array
{
    return [
        'tipo' => 'residencia',
        'nombre_solicitante' => 'Socio de prueba',
        'rut_solicitante' => '12345678-5',
        'direccion' => 'Calle 1',
        'dirigido_a' => 'Banco',
        'numero_transaccion' => 'TRX-001',
        'comprobante' => UploadedFile::fake()->create('pago.pdf', 100, 'application/pdf'),
    ];
}

test('la solicitud de residencia exige comprobante', function () {
    $usuario = socioActivo();

    $datos = solicitudResidencia($usuario);
    unset($datos['comprobante']);

    $this->actingAs($usuario)->post('/certificados', $datos)->assertSessionHasErrors('comprobante');
});

test('emitir sin confirmar el pago no es posible', function () {
    $usuario = socioActivo();
    $this->actingAs($usuario)->post('/certificados', solicitudResidencia($usuario));

    $presidente = User::factory()->create(['role' => 'presidente']);
    $certificado = Certificado::latest('id')->first();

    $this->actingAs($presidente)
        ->post("/admin/certificados/{$certificado->id}/emitir", [
            'nombre_solicitante' => 'Socio de prueba',
            'rut_solicitante' => '12345678-5',
            'direccion' => 'Calle 1',
            'dirigido_a' => 'Banco',
            'documentos_verificados' => '1',
        ])->assertSessionHasErrors('pago_verificado');
});

test('emitir registra el ingreso automático de $1.000', function () {
    $usuario = socioActivo();
    $this->actingAs($usuario)->post('/certificados', solicitudResidencia($usuario));

    $presidente = User::factory()->create(['role' => 'presidente']);
    $certificado = Certificado::latest('id')->first();

    $this->actingAs($presidente)->post("/admin/certificados/{$certificado->id}/emitir", [
        'nombre_solicitante' => 'Socio de prueba',
        'rut_solicitante' => '12345678-5',
        'direccion' => 'Calle 1',
        'dirigido_a' => 'Banco',
        'documentos_verificados' => '1',
        'pago_verificado' => '1',
    ]);

    $ingreso = Ingreso::where('certificado_id', $certificado->id)->first();
    expect($ingreso)->not->toBeNull()
        ->and($ingreso->monto)->toBe(Parametro::entero('certificado_residencia_valor'))
        ->and($ingreso->concepto)->toBe('certificados');
});

test('descargar dentro del plazo registra la descarga', function () {
    $usuario = socioActivo();
    $this->actingAs($usuario)->post('/certificados', solicitudResidencia($usuario));

    $certificado = Certificado::latest('id')->first();
    $certificado->forceFill([
        'estado' => 'emitido',
        'folio' => '2026-0001',
        'anio' => 2026,
        'correlativo' => 1,
        'fecha_emision' => now()->toDateString(),
        'fecha_vencimiento' => now()->addDays(90)->toDateString(),
        'descarga_disponible_hasta' => now()->addDays(5),
    ])->save();

    $this->actingAs($usuario)->get("/certificados/{$certificado->id}/pdf")->assertOk();

    expect(DescargaCertificado::where('certificado_id', $certificado->id)->count())->toBe(1);
});

test('descargar fuera del plazo devuelve 410', function () {
    $usuario = socioActivo();
    $this->actingAs($usuario)->post('/certificados', solicitudResidencia($usuario));

    $certificado = Certificado::latest('id')->first();
    $certificado->forceFill([
        'estado' => 'emitido',
        'folio' => '2026-0002',
        'anio' => 2026,
        'correlativo' => 2,
        'fecha_emision' => now()->subDays(10)->toDateString(),
        'fecha_vencimiento' => now()->addDays(80)->toDateString(),
        'descarga_disponible_hasta' => now()->subDays(5),
    ])->save();

    $this->actingAs($usuario)->get("/certificados/{$certificado->id}/pdf")->assertStatus(410);
});