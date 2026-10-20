<?php

use App\Models\Certificado;
use App\Models\Grupo;
use App\Models\Ingreso;
use App\Models\Reserva;
use App\Models\Socio;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->presidente4 = User::factory()->create(['role' => 'presidente']);
    $usuario = User::factory()->create(['role' => 'vecino']);
    $this->socio4 = Socio::create([
        'user_id' => $usuario->id, 'nombre_completo' => 'Socio Activo', 'rut' => '12345678-5',
        'direccion' => 'Calle Vecinal 123', 'telefono' => '912345678',
        'residente_desde' => '2020-01-01', 'fecha_ingreso' => '2021-01-01', 'estado' => 'activo',
    ]);
    $this->actingAs($this->presidente4);
});

function arriendoManualBloque4(array $cambios = []): array
{
    return array_replace([
        'modalidad_solicitante' => 'manual', 'responsable_nombre' => 'Persona Ventanilla',
        'responsable_rut' => '11.111.111-1', 'responsable_direccion' => 'Pasaje Vecinal 456',
        'responsable_telefono' => '987654321', 'fecha' => now()->addDay()->toDateString(),
        'hora_inicio' => '10:00', 'hora_fin' => '11:00', 'valor_arriendo' => 40000, 'valor_garantia' => 40000,
        'comprobante' => UploadedFile::fake()->create('total.pdf', 100, 'application/pdf'),
    ], $cambios);
}

test('el socio emite inmediatamente con datos de su ficha y el presidente como otorgante sin pagar', function () {
    $this->actingAs($this->socio4->user)->post(route('certificados.store'), [
        'tipo' => 'socio', 'dirigido_a' => 'Municipalidad', 'nombre_solicitante' => 'Nombre falso',
        'socio_id' => 99999, 'otorgado_por' => $this->socio4->user_id, 'solo_impresion' => true,
    ])->assertSessionHasNoErrors()->assertRedirect(route('certificados.index'));
    $certificado = Certificado::sole();
    expect($certificado->estado)->toBe('emitido')->and($certificado->nombre_solicitante)->toBe('Socio Activo')
        ->and($certificado->socio_id)->toBe($this->socio4->id)
        ->and($certificado->otorgado_por)->toBe($this->presidente4->id)
        ->and($certificado->solo_impresion)->toBeFalse()->and($certificado->folio)->not->toBeNull();
    $this->assertDatabaseCount('ingresos', 0);
    $this->get(route('certificados.index'))->assertOk();
    $this->get(route('certificados.descargar', $certificado))->assertOk();
});

test('la emisión propia no se bloquea por tres solicitudes de residencia pendientes', function () {
    for ($indice = 0; $indice < 3; $indice++) {
        Certificado::create([
            'tipo' => 'residencia', 'user_id' => $this->socio4->user_id, 'socio_id' => $this->socio4->id,
            'nombre_solicitante' => 'Socio Activo', 'rut_solicitante' => $this->socio4->rut,
            'direccion' => $this->socio4->direccion, 'dirigido_a' => 'Banco',
        ]);
    }
    $this->actingAs($this->socio4->user)->post(route('certificados.store'), ['tipo' => 'socio', 'dirigido_a' => 'Municipalidad'])
        ->assertSessionHasNoErrors();
    expect(Certificado::where('tipo', 'socio')->sole()->estado)->toBe('emitido');
});

test('la emisión autónoma requiere un presidente inequívoco sin dejar solicitudes incompletas', function (bool $duplicado) {
    if ($duplicado) User::factory()->create(['role' => 'presidente']);
    else $this->presidente4->forceFill(['role' => 'secretario'])->save();
    $this->actingAs($this->socio4->user)->post(route('certificados.store'), ['tipo' => 'socio', 'dirigido_a' => 'Banco'])
        ->assertSessionHasErrors('otorgante', null, 'certificadoSocio');
    $this->assertDatabaseCount('certificados', 0);
})->with([false, true]);

test('el certificado de socio de ventanilla solo lo puede imprimir la directiva', function (string $rol) {
    $this->actingAs(User::factory()->create(['role' => $rol]))->post(route('admin.certificados.store'), [
        'tipo' => 'socio', 'socio_id' => $this->socio4->id, 'nombre_solicitante' => 'Socio Activo',
        'rut_solicitante' => $this->socio4->rut, 'direccion' => $this->socio4->direccion, 'dirigido_a' => 'Banco',
    ])->assertSessionHasNoErrors();
    $certificado = Certificado::sole();
    expect($certificado->estado)->toBe('emitido')->and($certificado->solo_impresion)->toBeTrue()
        ->and($certificado->user_id)->toBeNull()->and($certificado->puedeDescargarse())->toBeFalse();
    $this->get(route('admin.certificados.pdf', $certificado))->assertOk();
    $this->actingAs($this->socio4->user)->get(route('certificados.index'))
        ->assertViewHas('certificados', fn ($lista) => $lista->isEmpty());
    $this->get(route('certificados.descargar', $certificado))->assertNotFound();
    $this->assertDatabaseCount('ingresos', 0);
})->with(['presidente', 'secretario']);

test('residencia conserva la revisión de pago y su disponibilidad para el titular', function () {
    $this->actingAs($this->socio4->user)->post(route('certificados.store'), [
        'tipo' => 'residencia', 'nombre_solicitante' => 'Socio Activo', 'rut_solicitante' => $this->socio4->rut,
        'direccion' => $this->socio4->direccion, 'dirigido_a' => 'Banco', 'numero_transaccion' => 'TRX-4',
        'comprobante' => UploadedFile::fake()->create('pago.pdf', 100, 'application/pdf'),
    ])->assertSessionHasNoErrors();
    $certificado = Certificado::sole();
    expect($certificado->estado)->toBe('solicitado');
    $this->actingAs($this->presidente4)->post(route('admin.certificados.emitir', $certificado), [
        'nombre_solicitante' => 'Socio Activo', 'rut_solicitante' => $this->socio4->rut, 'direccion' => $this->socio4->direccion,
        'dirigido_a' => 'Banco', 'documentos_verificados' => 1, 'pago_verificado' => 1,
    ])->assertSessionHasNoErrors();
    expect($certificado->fresh()->solo_impresion)->toBeFalse()->and(Ingreso::sole()->concepto)->toBe('certificados');
    $this->actingAs($this->socio4->user)->get(route('certificados.descargar', $certificado))->assertOk();
});

test('reserva de no socio en ventanilla registra el aporte sin garantía ni crear ficha o cuenta', function (string $rol) {
    $this->actingAs(User::factory()->create(['role' => $rol]));
    $usuariosAntes = User::count();
    $this->post(route('arriendos.store'), arriendoManualBloque4())->assertSessionHasNoErrors();
    $reserva = Reserva::sole();
    expect($reserva->socio_id)->toBeNull()->and($reserva->responsable_nombre)->toBe('Persona Ventanilla')
        ->and($reserva->responsable_rut)->toBe('11111111-1')->and($reserva->canal)->toBe('ventanilla')
        ->and($reserva->estado)->toBe('pendiente')->and(User::count())->toBe($usuariosAntes);
    $this->assertDatabaseCount('socios', 1);
    Storage::disk('local')->assertExists($reserva->comprobante_arriendo_path);
    $this->get(route('arriendos.show', $reserva))->assertOk()->assertViewHas('volverFinanzas', false);
    $this->post(route('arriendos.confirmar', $reserva), ['pago_verificado' => 1, 'garantia_verificada' => 1])->assertSessionHasNoErrors();
    $this->actingAs(User::factory()->create(['role' => 'tesorero']))->post(route('finanzas.arriendos.registrar', $reserva), [
        'fecha_pago' => now()->toDateString(), 'medio_pago' => 'transferencia',
    ])->assertSessionHasNoErrors();
    expect(Ingreso::saldoCaja())->toBe(40000)->and(Ingreso::sole()->concepto)->toBe('aporte_reserva');
    $this->assertDatabaseCount('garantias', 0);
    $this->get(route('ingresos.index'))->assertOk()
        ->assertSee(route('arriendos.show', ['reserva' => $reserva, 'desde' => 'finanzas']));
    $this->get(route('arriendos.show', $reserva))->assertOk()->assertSee('Persona Ventanilla');
    $this->get(route('arriendos.show', ['reserva' => $reserva, 'desde' => 'finanzas']))->assertViewHas('volverFinanzas', true);
    $this->actingAs($this->socio4->user)->get(route('arriendos.show', $reserva))->assertForbidden();
})->with(['presidente', 'secretario']);

test('ventanilla exige datos personales completos y un RUT válido sin guardar archivos inválidos', function (string $campo, mixed $valor) {
    $this->post(route('arriendos.store'), arriendoManualBloque4([$campo => $valor]))->assertSessionHasErrors($campo);
    $this->assertDatabaseCount('reservas', 0);
    expect(Storage::disk('local')->allFiles())->toBe([]);
})->with([
    'sin nombre' => ['responsable_nombre', ''], 'sin RUT' => ['responsable_rut', ''],
    'sin domicilio' => ['responsable_direccion', ''], 'sin teléfono' => ['responsable_telefono', ''],
    'dígito inválido' => ['responsable_rut', '11111111-2'], 'RUT no escalar' => ['responsable_rut', ['11111111-1']],
]);

test('un grupo con representante manual también puede arrendar en ventanilla', function () {
    $grupo = Grupo::create(['nombre' => 'Grupo manual', 'representante_nombre' => 'Persona Ventanilla']);
    $this->post(route('arriendos.store'), arriendoManualBloque4(['grupo_id' => $grupo->id]))->assertSessionHasNoErrors();
    expect(Reserva::sole()->grupo_id)->toBe($grupo->id)->and(Reserva::sole()->socio_id)->toBeNull();
});

test('un socio en línea no puede usar ventanilla para suplantar a otra persona', function () {
    $this->actingAs($this->socio4->user)->post(route('arriendos.store'), arriendoManualBloque4())->assertSessionHasNoErrors();
    expect(Reserva::sole()->responsable_nombre)->toBe('Socio Activo')
        ->and(Reserva::sole()->socio_id)->toBe($this->socio4->id)->and(Reserva::sole()->canal)->toBe('en_linea');
});

test('no se permite registrar ventanilla sin los permisos o la ficha exigidos', function (string $rol) {
    $this->actingAs(User::factory()->create(['role' => $rol]))->post(route('arriendos.store'), arriendoManualBloque4())->assertForbidden();
    $this->assertDatabaseCount('reservas', 0);
})->with(['vecino', 'admin', 'tesorero']);
