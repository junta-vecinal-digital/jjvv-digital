<?php

use App\Models\Ingreso;
use App\Models\Grupo;
use App\Models\Parametro;
use App\Models\Reserva;
use App\Models\Socio;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->travelTo(now()->startOfDay()->addHours(8));
    $this->directivaAporte = User::factory()->create(['role' => 'presidente']);
    $usuario = User::factory()->create(['role' => 'vecino']);
    $this->socioAporte = Socio::create([
        'user_id' => $usuario->id, 'nombre_completo' => 'Solicitante de sede', 'rut' => '12345678-5',
        'direccion' => 'Calle Vecinal 123', 'telefono' => '912345678',
        'residente_desde' => '2020-01-01', 'estado' => 'activo',
    ]);
    $this->actingAs($this->directivaAporte);
});

afterEach(function () {
    $this->travelBack();
});

function solicitudSedeAporte(array $cambios = []): array
{
    return array_replace([
        'fecha' => now()->addDays(5)->toDateString(), 'hora_inicio' => '10:00', 'hora_fin' => '12:00',
    ], $cambios);
}

function pendienteSedeAporte(Socio $socio, array $cambios = []): Reserva
{
    return Reserva::create(array_replace([
        'tipo' => 'arriendo', 'estado' => 'pendiente', 'motivo' => 'Reserva de sede', 'tipo_nombre' => 'Reserva de sede',
        'inicio' => now()->addDays(5)->setTime(10, 0), 'fin' => now()->addDays(5)->setTime(12, 0),
        'socio_id' => $socio->id, 'creado_por' => $socio->user_id,
        'aporte_variable' => true, 'valor_arriendo' => null, 'valor_garantia' => null, 'vence_en' => now()->addHours(48),
    ], $cambios));
}

test('solicitar en línea no permite fijar importe garantía autor ni comprobante anticipado', function () {
    $this->actingAs($this->socioAporte->user)->post(route('arriendos.store'), solicitudSedeAporte([
        'valor_arriendo' => 1, 'valor_garantia' => 90000, 'aporte_variable' => false,
        'aporte_definido_por' => $this->directivaAporte->id,
        'comprobante' => UploadedFile::fake()->create('anticipado.pdf', 10, 'application/pdf'),
    ]))->assertSessionHasNoErrors();
    $reserva = Reserva::sole();
    expect($reserva->aporte_variable)->toBeTrue()->and($reserva->valor_arriendo)->toBeNull()
        ->and($reserva->valor_garantia)->toBeNull()->and($reserva->aporte_definido_por)->toBeNull()
        ->and($reserva->comprobante_arriendo_path)->toBeNull();
    expect(Storage::disk('local')->allFiles())->toBe([]);
    $this->get(route('arriendos.show', $reserva))->assertOk()->assertSee('Pendiente de definir');
});

test('directiva define aporte y autor una vez sin extender el vencimiento', function (string $rol, int $monto) {
    $usuario = User::factory()->create(['role' => $rol]);
    $reserva = pendienteSedeAporte($this->socioAporte);
    $vence = $reserva->vence_en->copy();
    $this->travel(2)->hours();
    $this->actingAs($usuario)->post(route('arriendos.aporte', $reserva), ['valor_arriendo' => $monto])->assertSessionHasNoErrors();
    $reserva->refresh();
    expect($reserva->valor_arriendo)->toBe($monto)->and($reserva->aporte_definido_por)->toBe($usuario->id)
        ->and($reserva->aporte_definido_en)->not->toBeNull()->and($reserva->vence_en->equalTo($vence))->toBeTrue();
    $this->post(route('arriendos.aporte', $reserva), ['valor_arriendo' => 123])->assertSessionHasErrors('arriendo');
    expect($reserva->fresh()->valor_arriendo)->toBe($monto);
})->with(['presidente', 'secretario'])->with([0, 17500]);

test('otros roles no definen aportes ni confirman reservas', function (string $rol) {
    $reserva = pendienteSedeAporte($this->socioAporte);
    $usuario = $rol === 'vecino' ? $this->socioAporte->user : User::factory()->create(['role' => $rol]);
    $this->actingAs($usuario)->post(route('arriendos.aporte', $reserva), ['valor_arriendo' => 0])->assertForbidden();
    $this->post(route('arriendos.confirmar', $reserva))->assertForbidden();
    expect($reserva->fresh()->valor_arriendo)->toBeNull()->and($reserva->estado)->toBe('pendiente');
})->with(['vecino', 'admin', 'tesorero']);

test('aportes inválidos no se guardan en ventanilla ni al definirlos', function (mixed $monto) {
    $reserva = pendienteSedeAporte($this->socioAporte);
    $this->post(route('arriendos.aporte', $reserva), ['valor_arriendo' => $monto])->assertSessionHasErrors('valor_arriendo');
    $this->post(route('arriendos.store'), solicitudSedeAporte([
        'socio_id' => $this->socioAporte->id, 'valor_arriendo' => $monto,
    ]))->assertSessionHasErrors('valor_arriendo');
    expect($reserva->fresh()->valor_arriendo)->toBeNull();
    $this->assertDatabaseCount('reservas', 1);
})->with([-1, 1.5, 'texto', null, 4294967296]);

test('reserva gratuita de ventanilla confirma sin pago ni garantía y nunca genera ingreso', function (string $rol) {
    $this->actingAs(User::factory()->create(['role' => $rol]))->post(route('arriendos.store'), solicitudSedeAporte([
        'modalidad_solicitante' => 'manual', 'responsable_nombre' => 'Vecino sin ficha',
        'responsable_rut' => '11111111-1', 'responsable_direccion' => 'Pasaje 123', 'responsable_telefono' => '987654321',
        'valor_arriendo' => 0, 'valor_garantia' => 99999,
    ]))->assertSessionHasNoErrors();
    $reserva = Reserva::sole();
    $this->post(route('arriendos.confirmar', $reserva))->assertSessionHasNoErrors();
    expect($reserva->fresh()->estado)->toBe('confirmada')->and($reserva->valor_garantia)->toBeNull()
        ->and($reserva->socio_id)->toBeNull();
    $this->get(route('arriendos.show', $reserva))->assertOk()->assertDontSee('name="garantia_verificada"', false);
    $this->assertDatabaseCount('garantias', 0);
    $this->actingAs($this->directivaAporte)->post(route('finanzas.arriendos.registrar', $reserva), [
        'fecha_pago' => now()->toDateString(), 'medio_pago' => 'transferencia',
    ])->assertSessionHasErrors('finanzas');
    $this->get(route('finanzas.arriendos.index'))->assertOk()->assertSee('Gratuita');
    $this->assertDatabaseCount('ingresos', 0);
})->with(['presidente', 'secretario']);

test('aporte positivo requiere comprobante y verificación y finanzas usa el monto guardado', function (string $rol) {
    $reserva = pendienteSedeAporte($this->socioAporte);
    $this->post(route('arriendos.confirmar', $reserva))->assertSessionHasErrors('arriendo');
    $this->post(route('arriendos.aporte', $reserva), ['valor_arriendo' => 17500])->assertSessionHasNoErrors();
    $this->post(route('arriendos.confirmar', $reserva), ['pago_verificado' => 1])->assertSessionHasErrors('arriendo');
    $this->actingAs($this->socioAporte->user)->post(route('arriendos.comprobante.store', $reserva), [
        'comprobante' => UploadedFile::fake()->create('aporte.pdf', 10, 'application/pdf'), 'valor_arriendo' => 1,
    ])->assertSessionHasNoErrors();
    Storage::disk('local')->assertExists($reserva->fresh()->comprobante_arriendo_path);
    $this->get(route('arriendos.comprobante', $reserva))->assertOk();
    $this->actingAs($this->directivaAporte)->post(route('arriendos.confirmar', $reserva))->assertSessionHasErrors('pago_verificado');
    $this->post(route('arriendos.confirmar', $reserva), ['pago_verificado' => 1])->assertSessionHasNoErrors();
    $this->assertDatabaseCount('garantias', 0);
    $this->assertDatabaseCount('ingresos', 0);
    $this->actingAs(User::factory()->create(['role' => $rol]));
    $pago = ['fecha_pago' => now()->toDateString(), 'medio_pago' => 'transferencia', 'monto' => 1];
    $this->post(route('finanzas.arriendos.registrar', $reserva), $pago)->assertSessionHasNoErrors();
    $ingreso = Ingreso::sole();
    expect($ingreso->concepto)->toBe('aporte_reserva')->and($ingreso->monto)->toBe(17500)
        ->and($ingreso->tipo_cuota)->toBeNull()->and(Ingreso::saldoCaja())->toBe(17500);
    $this->get(route('arriendos.show', ['reserva' => $reserva, 'desde' => 'finanzas']))->assertOk()->assertViewHas('volverFinanzas', true);
    $this->get(route('ingresos.index'))->assertOk()->assertSee('Aporte voluntario por reserva de sede');
    $this->post(route('finanzas.arriendos.registrar', $reserva), $pago)->assertSessionHasErrors('finanzas');
    $this->post(route('ingresos.anular', $ingreso), ['motivo_anulacion' => 'Error de registro'])->assertSessionHasNoErrors();
    $this->post(route('finanzas.arriendos.registrar', $reserva), $pago)->assertSessionHasNoErrors();
    expect($reserva->ingresos()->vigentes()->count())->toBe(1)->and(Ingreso::saldoCaja())->toBe(17500);
})->with(['presidente', 'tesorero']);

test('otro vecino no consulta ni adjunta comprobante a una reserva ajena', function () {
    $reserva = pendienteSedeAporte($this->socioAporte, ['valor_arriendo' => 5000]);
    $usuario = User::factory()->create(['role' => 'vecino']);
    $this->actingAs($usuario)->get(route('arriendos.show', $reserva))->assertForbidden();
    $this->get(route('arriendos.comprobante', $reserva))->assertForbidden();
    $this->post(route('arriendos.comprobante.store', $reserva), [
        'comprobante' => UploadedFile::fake()->create('ajeno.pdf', 10, 'application/pdf'),
    ])->assertForbidden();
    expect(Storage::disk('local')->allFiles())->toBe([]);
});

test('no admite comprobantes sin importe gratuitos vencidos cancelados confirmados o duplicados', function (string $caso) {
    $reserva = pendienteSedeAporte($this->socioAporte, ['valor_arriendo' => 5000]);
    $this->actingAs($this->socioAporte->user);
    if ($caso === 'sin definir') $reserva->update(['valor_arriendo' => null]);
    if ($caso === 'gratuita') $reserva->update(['valor_arriendo' => 0]);
    if ($caso === 'vencida') $this->travel(48)->hours();
    if (in_array($caso, ['cancelada', 'confirmada'])) $reserva->update(['estado' => $caso]);
    if ($caso === 'duplicado') {
        Storage::disk('local')->put('comprobantes/arriendos/original.pdf', 'original');
        $reserva->update(['comprobante_arriendo_path' => 'comprobantes/arriendos/original.pdf']);
    }
    $archivos = Storage::disk('local')->allFiles();
    $this->post(route('arriendos.comprobante.store', $reserva), [
        'comprobante' => UploadedFile::fake()->create('nuevo.pdf', 10, 'application/pdf'),
    ])->assertSessionHasErrors('arriendo');
    expect(Storage::disk('local')->allFiles())->toBe($archivos);
})->with(['sin definir', 'gratuita', 'vencida', 'cancelada', 'confirmada', 'duplicado']);

test('pendiente sin aporte bloquea horario y al vencer no acepta importe ni confirmación', function () {
    $reserva = pendienteSedeAporte($this->socioAporte);
    $datos = solicitudSedeAporte(['socio_id' => $this->socioAporte->id, 'valor_arriendo' => 0]);
    $this->post(route('arriendos.store'), $datos)->assertSessionHasErrors('arriendo');
    $this->travel(48)->hours();
    $this->post(route('arriendos.aporte', $reserva), ['valor_arriendo' => 1000])->assertSessionHasErrors('arriendo');
    $this->post(route('arriendos.confirmar', $reserva))->assertSessionHasErrors('arriendo');
    expect($reserva->fresh()->estado)->toBe('vencida')->and($reserva->valor_arriendo)->toBeNull();
    $this->post(route('arriendos.store'), $datos)->assertSessionHasNoErrors();
});

test('parámetros oculta las tarifas antiguas y no permite cambiarlas', function () {
    $antes = Parametro::whereIn('clave', ['arriendo_valor', 'arriendo_garantia'])->pluck('valor', 'clave')->all();
    $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('parametros.index'))
        ->assertOk()->assertDontSee('name="valores[arriendo_valor]"', false)->assertDontSee('name="valores[arriendo_garantia]"', false);
    $valores = Parametro::whereNotIn('clave', ['arriendo_valor', 'arriendo_garantia'])->pluck('valor', 'clave')->all();
    $this->put(route('parametros.update'), ['valores' => $valores + ['arriendo_valor' => 1, 'arriendo_garantia' => 2]])
        ->assertSessionHasNoErrors();
    expect(Parametro::whereIn('clave', array_keys($antes))->pluck('valor', 'clave')->all())->toBe($antes);
});

test('cancelar una reserva gratuita libera el horario sin generar garantía', function () {
    $reserva = pendienteSedeAporte($this->socioAporte, ['valor_arriendo' => 0]);
    $this->post(route('arriendos.confirmar', $reserva))->assertSessionHasNoErrors();
    $this->post(route('reservas.cancelar', $reserva))->assertSessionHasNoErrors();
    expect($reserva->fresh()->estado)->toBe('cancelada')->and(Reserva::ocupan()->whereKey($reserva->id)->exists())->toBeFalse();
    $this->post(route('arriendos.store'), solicitudSedeAporte([
        'socio_id' => $this->socioAporte->id, 'valor_arriendo' => 5000,
    ]))->assertSessionHasNoErrors();
    $this->assertDatabaseCount('garantias', 0);
    $this->assertDatabaseCount('ingresos', 0);
});

test('no permite redefinir importes ni reemplazar comprobantes históricos', function () {
    $reserva = pendienteSedeAporte($this->socioAporte, [
        'aporte_variable' => false, 'valor_arriendo' => 40000, 'valor_garantia' => 40000,
    ]);
    $this->post(route('arriendos.aporte', $reserva), ['valor_arriendo' => 0])->assertSessionHasErrors('arriendo');
    $this->actingAs($this->socioAporte->user);
    $this->post(route('arriendos.comprobante.store', $reserva), [
        'comprobante' => UploadedFile::fake()->create('nuevo.pdf', 10, 'application/pdf'),
    ])->assertSessionHasErrors('arriendo');
    expect($reserva->fresh()->valor_arriendo)->toBe(40000)->and($reserva->valor_garantia)->toBe(40000);
    expect(Storage::disk('local')->allFiles())->toBe([]);
});

test('ventanilla vincula al socio por el RUT del representante manual sin aceptar otro RUT', function (string $rut, bool $valido) {
    $grupo = Grupo::create([
        'nombre' => 'Agrupación del barrio', 'representante_nombre' => 'Representante manual',
        'representante_rut' => $rut,
    ]);
    $respuesta = $this->post(route('arriendos.store'), solicitudSedeAporte([
        'grupo_id' => $grupo->id, 'socio_id' => $this->socioAporte->id,
        'modalidad_solicitante' => 'socio', 'valor_arriendo' => 0,
    ]));
    if (! $valido) {
        $respuesta->assertSessionHasErrors('grupo_id');
        $this->assertDatabaseCount('reservas', 0);
        return;
    }
    $respuesta->assertSessionHasNoErrors();
    expect(Reserva::sole()->socio_id)->toBe($this->socioAporte->id)
        ->and(Reserva::sole()->grupo_id)->toBe($grupo->id);
})->with([['12.345.678-5', true], ['11.111.111-1', false]]);