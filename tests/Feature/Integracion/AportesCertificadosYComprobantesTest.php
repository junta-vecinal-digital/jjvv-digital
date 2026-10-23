<?php

use App\Models\Auditoria;
use App\Models\Certificado;
use App\Models\DescargaCertificado;
use App\Models\Ingreso;
use App\Models\Parametro;
use App\Models\Socio;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->withoutVite();
    Storage::fake('local');
    $this->emisorAporte = User::factory()->create(['role' => 'presidente', 'name' => 'Otorgante original']);
    $this->vecinoAporte = User::factory()->create(['role' => 'vecino']);
    $this->fichaAporte = Socio::create([
        'user_id' => $this->vecinoAporte->id, 'nombre_completo' => 'Vecino de prueba', 'rut' => '12345678-5',
        'direccion' => 'Calle 123', 'telefono' => '912345678', 'residente_desde' => '2020-01-01', 'estado' => 'activo',
    ]);
});

function datosCertificadoAporte(array $cambios = []): array
{
    return array_replace([
        'tipo' => 'residencia', 'nombre_solicitante' => 'Vecino de prueba', 'rut_solicitante' => '12345678-5',
        'direccion' => 'Calle 123', 'dirigido_a' => 'Municipalidad', 'aporte' => 0,
    ], $cambios);
}

test('residencia sin aporte se solicita y emite sin respaldo ni ingreso', function () {
    Parametro::where('clave', 'certificado_residencia_valor')->update(['valor' => '0']);
    $this->actingAs($this->vecinoAporte)->post(route('certificados.store'), datosCertificadoAporte())->assertSessionHasNoErrors();
    $certificado = Certificado::sole();
    expect((int) $certificado->aporte)->toBe(0)->and($certificado->comprobante_path)->toBeNull()->and($certificado->medio_pago)->toBeNull();
    $this->actingAs($this->emisorAporte)->post(route('admin.certificados.emitir', $certificado), datosCertificadoAporte(['documentos_verificados' => 1]))->assertSessionHasNoErrors();
    expect($certificado->fresh()->estado)->toBe('emitido');
    $this->assertDatabaseCount('ingresos', 0);
    expect(Storage::disk('local')->allFiles())->toBe([]);
});

test('ventanilla permite residencia con aporte cero conservando verificación de documentos', function () {
    $datos = datosCertificadoAporte(['residencia_6_meses' => 1]);
    $this->actingAs($this->emisorAporte)->post(route('admin.certificados.store'), $datos)->assertSessionHasErrors('documentos_verificados');
    $this->post(route('admin.certificados.store'), $datos + ['documentos_verificados' => 1])->assertSessionHasNoErrors();
    expect(Certificado::sole()->estado)->toBe('emitido')->and((int) Certificado::sole()->aporte)->toBe(0);
    $this->assertDatabaseCount('ingresos', 0);
});

test('el aporte configurado se fija al solicitar y exige transacción y verificación antes del registro financiero', function () {
    Parametro::where('clave', 'certificado_residencia_valor')->update(['valor' => '2500']);
    $datos = datosCertificadoAporte(['aporte' => 99999]);
    $this->actingAs($this->vecinoAporte)->post(route('certificados.store'), $datos)->assertSessionHasErrors('numero_transaccion');
    $this->post(route('certificados.store'), $datos + ['numero_transaccion' => 'TRX-2500'])->assertSessionHasNoErrors();
    $certificado = Certificado::sole();
    expect((int) $certificado->aporte)->toBe(2500)->and($certificado->comprobante_path)->toBeNull();
    Parametro::where('clave', 'certificado_residencia_valor')->update(['valor' => '7000']);
    $this->actingAs($this->emisorAporte)->post(route('admin.certificados.emitir', $certificado), datosCertificadoAporte(['documentos_verificados' => 1]))->assertSessionHasErrors('pago_verificado');
    $this->post(route('admin.certificados.emitir', $certificado), datosCertificadoAporte(['documentos_verificados' => 1, 'pago_verificado' => 1, 'aporte' => 99999]))->assertSessionHasNoErrors();
    $this->assertDatabaseCount('ingresos', 0);
    $this->post(route('ingresos.certificados.registrar', $certificado))->assertSessionHasNoErrors();
    expect(Ingreso::sole()->monto)->toBe(2500)->and((int) $certificado->fresh()->aporte)->toBe(2500);
});

test('identidad del otorgante queda fija al renombrar cambiar rol o eliminar su cuenta', function () {
    $certificado = Certificado::create(datosCertificadoAporte());
    $certificado->emitir($this->emisorAporte);
    $this->emisorAporte->forceFill(['name' => 'Nombre posterior', 'role' => 'tesorero'])->save();
    $this->emisorAporte->delete();
    $certificado->refresh();
    expect($certificado->otorgado_por)->toBeNull()->and($certificado->otorgante_nombre)->toBe('Otorgante original')
        ->and($certificado->otorgante_cargo)->toBe('Presidente');
    $html = view('certificados.pdf', ['c' => $certificado, 'junta' => 'Junta de prueba'])->render();
    expect($html)->toContain('Otorgante original')->toContain('Presidente')->not->toContain('Nombre posterior');
    $this->get(route('verificar.mostrar', $certificado->codigo_verificacion))->assertOk()->assertSee('Otorgante original');
});

test('descargar certificado de socio no genera auditoría ni registro de descarga', function () {
    $this->actingAs($this->vecinoAporte)->post(route('certificados.store'), ['tipo' => 'socio', 'dirigido_a' => 'Municipalidad'])->assertSessionHasNoErrors();
    $certificado = Certificado::sole();
    $antes = Auditoria::count();
    $this->get(route('certificados.descargar', $certificado))->assertOk();
    expect(Auditoria::count())->toBe($antes)->and(DescargaCertificado::count())->toBe(0);
});

test('ingreso manual permite archivo en lugar de número y protege el respaldo tras anular', function (string $rol) {
    $usuario = User::factory()->create(['role' => $rol]);
    $datos = [
        'concepto' => 'otros', 'fecha_pago' => now()->toDateString(), 'monto' => 5000, 'medio_pago' => 'transferencia',
        'observaciones' => 'Aporte voluntario para la comunidad',
    ];
    $this->actingAs($usuario)->post(route('ingresos.store'), $datos)->assertSessionHasErrors(['numero_transaccion', 'comprobante']);
    $this->post(route('ingresos.store'), $datos + ['comprobante' => UploadedFile::fake()->create('aporte.pdf', 10, 'application/pdf')])->assertSessionHasNoErrors();
    $ingreso = Ingreso::sole();
    Storage::disk('local')->assertExists($ingreso->comprobante_path);
    $this->get(route('ingresos.comprobante', $ingreso))->assertOk();
    $this->post(route('ingresos.anular', $ingreso), ['motivo_anulacion' => 'Duplicado'])->assertSessionHasNoErrors();
    Storage::disk('local')->assertExists($ingreso->comprobante_path);
    foreach (['secretario', 'vecino', 'admin'] as $otroRol) {
        $this->actingAs(User::factory()->create(['role' => $otroRol]))->get(route('ingresos.comprobante', $ingreso))->assertForbidden();
    }
})->with(['presidente', 'tesorero']);

test('comprobante manual rechaza tipo o tamaño inválido sin crear archivos o ingresos', function (string $caso) {
    $archivo = $caso === 'tipo' ? UploadedFile::fake()->create('archivo.txt', 1, 'text/plain') : UploadedFile::fake()->create('grande.pdf', 5121, 'application/pdf');
    $this->actingAs($this->emisorAporte)->post(route('ingresos.store'), [
        'concepto' => 'otros', 'observaciones' => 'Prueba', 'fecha_pago' => now()->toDateString(), 'monto' => 1,
        'medio_pago' => 'transferencia', 'comprobante' => $archivo,
    ])->assertSessionHasErrors('comprobante');
    $this->assertDatabaseCount('ingresos', 0);
    expect(Storage::disk('local')->allFiles())->toBe([]);
})->with(['tipo', 'tamaño']);

test('el número de transacción o talonario no reemplaza al comprobante manual obligatorio', function (string $medio) {
    $this->actingAs($this->emisorAporte)->post(route('ingresos.store'), [
        'concepto' => 'otros', 'observaciones' => 'Aporte de prueba', 'fecha_pago' => today()->toDateString(),
        'monto' => 1000, 'medio_pago' => $medio, 'talonario' => 'T-123', 'numero_transaccion' => 'TRX-123',
    ])->assertSessionHasErrors('comprobante');
    $this->assertDatabaseCount('ingresos', 0);
})->with(['efectivo', 'transferencia']);
