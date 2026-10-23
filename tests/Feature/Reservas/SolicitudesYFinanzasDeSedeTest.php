<?php

use App\Models\Garantia;
use App\Models\Grupo;
use App\Models\Ingreso;
use App\Models\Parametro;
use App\Models\Reserva;
use App\Models\Socio;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->withoutVite();
    Storage::fake('local');
    $this->travelTo(now()->startOfDay()->addHours(8));
    $this->presidente3b = User::factory()->create(['role' => 'presidente']);
    $this->socio3b = socioBloque3b();
    $this->actingAs($this->presidente3b);
});

afterEach(function () {
    $this->travelBack();
});

function socioBloque3b(string $estado = 'activo'): Socio
{
    $usuario = User::factory()->create(['role' => 'vecino']);

    return Socio::create([
        'user_id' => $usuario->id,
        'nombre_completo' => $usuario->name,
        'rut' => str_pad((string) $usuario->id, 8, '0', STR_PAD_LEFT).'-K',
        'direccion' => 'Calle de prueba 123',
        'telefono' => '912345678',
        'residente_desde' => '2020-01-01',
        'estado' => $estado,
    ]);
}

function datosArriendoBloque3b(array $cambios = []): array
{
    return array_replace([
        'fecha' => now()->addDays(5)->toDateString(),
        'hora_inicio' => '10:00',
        'hora_fin' => '12:00',
        'valor_arriendo' => 40000,
        'valor_garantia' => 40000,
        'numero_transaccion' => 'TRX-SEDE-40000',
    ], $cambios);
}

function reservaBloque3b(Socio $socio, array $cambios = []): Reserva
{
    Storage::disk('local')->put('comprobantes/arriendos/total.pdf', 'Comprobante de prueba');

    return Reserva::create(array_replace([
        'tipo' => 'arriendo',
        'estado' => 'pendiente',
        'inicio' => now()->addDays(5)->setTime(10, 0),
        'fin' => now()->addDays(5)->setTime(12, 0),
        'motivo' => 'Arriendo de sede',
        'aporte_variable' => false, // Casos históricos: conservan el pago y la garantía originales.
        'socio_id' => $socio->id,
        'creado_por' => $socio->user_id,
        'valor_arriendo' => 40000,
        'valor_garantia' => 40000,
        'vence_en' => now()->addHours(48),
        'comprobante_arriendo_path' => 'comprobantes/arriendos/total.pdf',
    ], $cambios));
}

function confirmarBloque3b($caso, Reserva $reserva): void
{
    $caso->post(route('arriendos.confirmar', $reserva), [
        'pago_verificado' => 1,
        'garantia_verificada' => 1,
    ])->assertSessionHasNoErrors()->assertRedirect();
}

function registrarBloque3b($caso, Reserva $reserva): void
{
    $caso->post(route('finanzas.arriendos.registrar', $reserva), [
        'fecha_pago' => now()->toDateString(),
        'medio_pago' => 'transferencia',
    ])->assertSessionHasNoErrors()->assertRedirect();
}

test('el socio solicita sin suplantar al responsable ni definir el aporte o la garantía', function () {
    $this->actingAs($this->socio3b->user);
    $this->post(route('arriendos.store'), datosArriendoBloque3b([
        'socio_id' => socioBloque3b()->id,
        'creado_por' => $this->presidente3b->id,
    ]))->assertSessionHasNoErrors()->assertRedirect();

    $reserva = Reserva::sole();
    expect($reserva->estado)->toBe('pendiente')
        ->and($reserva->socio_id)->toBe($this->socio3b->id)
        ->and($reserva->creado_por)->toBe($this->socio3b->user_id)
        ->and($reserva->aporte_variable)->toBeTrue()
        ->and($reserva->valor_arriendo)->toBeNull()
        ->and($reserva->valor_garantia)->toBeNull()
        ->and($reserva->vence_en->equalTo(now()->addHours(48)))->toBeTrue();
    expect($reserva->comprobante_arriendo_path)->toBeNull();
    expect(Storage::disk('local')->allFiles())->toBe([]);
    $this->assertDatabaseCount('ingresos', 0);
    $this->assertDatabaseCount('garantias', 0);
    $this->get(route('arriendos.index'))->assertOk();
    $this->get(route('arriendos.show', $reserva))->assertOk();
});

test('sin ficha activa no se puede solicitar un arriendo', function (string $estado) {
    $socio = socioBloque3b($estado);
    $this->actingAs($socio->user);
    $this->get(route('arriendos.index'))->assertForbidden();
    $this->post(route('arriendos.store'), datosArriendoBloque3b())->assertForbidden();
    $this->assertDatabaseCount('reservas', 0);
})->with(['pendiente', 'por_firmar', 'rechazado', 'inactivo']);

test('un vecino sin ficha no puede solicitar arriendos', function () {
    $this->actingAs(User::factory()->create(['role' => 'vecino']));
    $this->post(route('arriendos.store'), datosArriendoBloque3b())->assertForbidden();
    $this->assertDatabaseCount('reservas', 0);
});

test('presidente y secretario solicitan por un socio o su grupo vigente', function (string $rol) {
    $this->actingAs(User::factory()->create(['role' => $rol]));
    $grupo = Grupo::create(['nombre' => 'Grupo de prueba', 'representante_id' => $this->socio3b->id]);
    $this->post(route('arriendos.store'), datosArriendoBloque3b([
        'socio_id' => $this->socio3b->id, 'grupo_id' => $grupo->id,
    ]))->assertSessionHasNoErrors()->assertRedirect();
    $this->assertDatabaseHas('reservas', ['grupo_id' => $grupo->id, 'socio_id' => $this->socio3b->id]);
})->with(['presidente', 'secretario']);

test('no se admite un grupo sin representante o con otro responsable', function (bool $sinRepresentante) {
    $grupo = Grupo::create(['nombre' => 'Grupo', 'representante_id' => $sinRepresentante ? null : socioBloque3b()->id]);
    $this->post(route('arriendos.store'), datosArriendoBloque3b([
        'socio_id' => $this->socio3b->id, 'grupo_id' => $grupo->id,
    ]))->assertSessionHasErrors('grupo_id');
    $this->assertDatabaseCount('reservas', 0);
})->with([true, false]);

test('la transacción posterior es obligatoria de texto y de hasta 50 caracteres', function (string $caso) {
    $transaccion = match ($caso) {
        'ausente' => null,
        'tipo inválido' => ['TRX-1'],
        'demasiado larga' => str_repeat('A', 51),
    };
    $reserva = reservaBloque3b($this->socio3b, ['aporte_variable' => true, 'canal' => 'en_linea', 'valor_garantia' => null, 'comprobante_arriendo_path' => null]);
    Storage::disk('local')->delete('comprobantes/arriendos/total.pdf');
    $this->actingAs($this->socio3b->user)->post(route('arriendos.comprobante.store', $reserva), ['numero_transaccion' => $transaccion])
        ->assertRedirect()->assertSessionHasErrors('numero_transaccion');
    expect($reserva->fresh()->numero_transaccion)->toBeNull()->and($reserva->comprobante_arriendo_path)->toBeNull();
    expect(Storage::disk('local')->allFiles())->toBe([]);
})->with(['ausente', 'tipo inválido', 'demasiado larga']);

test('el titular registra una transacción sin archivo y no puede reemplazarla', function () {
    $reserva = reservaBloque3b($this->socio3b, ['aporte_variable' => true, 'canal' => 'en_linea', 'comprobante_arriendo_path' => null]);
    Storage::disk('local')->delete('comprobantes/arriendos/total.pdf');
    $this->actingAs($this->socio3b->user)->post(route('arriendos.comprobante.store', $reserva), [
        'numero_transaccion' => str_repeat('A', 50),
    ])->assertSessionHasNoErrors();
    $this->post(route('arriendos.comprobante.store', $reserva), ['numero_transaccion' => 'OTRA'])->assertSessionHasErrors('arriendo');
    expect($reserva->fresh()->numero_transaccion)->toBe(str_repeat('A', 50))->and($reserva->comprobante_arriendo_path)->toBeNull();
    expect(Storage::disk('local')->allFiles())->toBe([]);
});

test('una solicitud pendiente bloquea la superposición pero permite el bloque contiguo', function () {
    reservaBloque3b($this->socio3b);
    $this->post(route('arriendos.store'), datosArriendoBloque3b([
        'socio_id' => $this->socio3b->id, 'hora_inicio' => '11:00',
    ]))->assertSessionHasErrors('arriendo');
    $this->assertDatabaseCount('reservas', 1);
    $this->post(route('arriendos.store'), datosArriendoBloque3b([
        'socio_id' => $this->socio3b->id, 'hora_inicio' => '12:00', 'hora_fin' => '13:00',
    ]))->assertSessionHasNoErrors()->assertRedirect();
    $this->assertDatabaseCount('reservas', 2);
});

test('el aporte manual y el plazo se conservan sin depender de la antigua tarifa fija', function () {
    Parametro::where('clave', 'arriendo_plazo_confirmacion_horas')->update(['valor' => '24']);
    $this->post(route('arriendos.store'), datosArriendoBloque3b(['socio_id' => $this->socio3b->id]))
        ->assertSessionHasNoErrors();
    $reserva = Reserva::sole();
    Parametro::where('clave', 'arriendo_valor')->update(['valor' => '50000']);
    expect($reserva->fresh()->valor_arriendo)->toBe(40000)
        ->and($reserva->vence_en->equalTo(now()->addHours(24)))->toBeTrue();
    $this->post(route('arriendos.store'), datosArriendoBloque3b([
        'socio_id' => $this->socio3b->id, 'hora_inicio' => '12:00', 'hora_fin' => '13:00',
    ]))->assertSessionHasNoErrors();
    $this->assertDatabaseCount('reservas', 2);
});

test('solo presidente o secretario confirma ambos pagos sin crear ingreso financiero', function (string $rol) {
    $reserva = reservaBloque3b($this->socio3b);
    $usuario = User::factory()->create(['role' => $rol]);
    $this->actingAs($usuario);
    $this->post(route('arriendos.confirmar', $reserva), ['pago_verificado' => 1])
        ->assertSessionHasErrors('garantia_verificada');
    expect($reserva->fresh()->estado)->toBe('pendiente');
    confirmarBloque3b($this, $reserva);
    $garantia = Garantia::sole();
    expect($reserva->fresh()->estado)->toBe('confirmada')
        ->and($reserva->fresh()->validado_por)->toBe($usuario->id)
        ->and($garantia->monto)->toBe(40000)->and($garantia->estado)->toBe('recibida');
    $this->assertDatabaseCount('ingresos', 0);
    $this->post(route('arriendos.confirmar', $reserva), ['pago_verificado' => 1, 'garantia_verificada' => 1])
        ->assertSessionHasErrors('arriendo');
    $this->assertDatabaseCount('garantias', 1);
})->with(['presidente', 'secretario']);

test('al cumplirse el plazo el comando libera pendientes y conserva confirmadas', function () {
    $pendiente = reservaBloque3b($this->socio3b);
    $confirmada = reservaBloque3b($this->socio3b, ['inicio' => now()->addDays(6)->setTime(10, 0), 'fin' => now()->addDays(6)->setTime(12, 0)]);
    confirmarBloque3b($this, $confirmada);
    $this->travel(48)->hours();
    expect(Reserva::ocupan()->whereKey($pendiente->id)->exists())->toBeFalse();
    $this->artisan('arriendos:liberar-vencidos')->assertExitCode(0);
    expect($pendiente->fresh()->estado)->toBe('vencida')
        ->and($confirmada->fresh()->estado)->toBe('confirmada');
    $this->post(route('arriendos.confirmar', $pendiente), ['pago_verificado' => 1, 'garantia_verificada' => 1])
        ->assertSessionHasErrors('arriendo');
    $this->assertDatabaseCount('garantias', 1);
    $this->post(route('arriendos.store'), datosArriendoBloque3b([
        'socio_id' => $this->socio3b->id, 'fecha' => $pendiente->inicio->toDateString(),
    ]))->assertSessionHasNoErrors();
});

test('cancelar libera el horario y deja la garantía recibida por devolver', function (bool $confirmar) {
    $reserva = reservaBloque3b($this->socio3b);
    if ($confirmar) confirmarBloque3b($this, $reserva);
    $this->post(route('reservas.cancelar', $reserva))->assertSessionHasNoErrors();
    expect($reserva->fresh()->estado)->toBe('cancelada')
        ->and($reserva->fresh()->cancelada_por)->toBe($this->presidente3b->id)
        ->and(Reserva::ocupan()->whereKey($reserva->id)->exists())->toBeFalse();
    if ($confirmar) expect($reserva->fresh()->garantia->estado)->toBe('por_devolver');
    $this->post(route('reservas.cancelar', $reserva))->assertSessionHasErrors('reserva');
    $this->post(route('arriendos.store'), datosArriendoBloque3b(['socio_id' => $this->socio3b->id]))
        ->assertSessionHasNoErrors();
})->with([false, true]);

test('el comprobante y detalle solo son visibles para titular dirigentes y tesorero tras validación', function () {
    $reserva = reservaBloque3b($this->socio3b);
    $this->actingAs(socioBloque3b()->user);
    $this->get(route('arriendos.show', $reserva))->assertForbidden();
    $this->get(route('arriendos.comprobante', $reserva))->assertForbidden();
    $this->get(route('arriendos.index'))->assertViewHas('arriendos', fn ($lista) => $lista->count() === 0);
    $this->actingAs($this->socio3b->user);
    $this->get(route('arriendos.comprobante', $reserva))->assertOk();
    $tesorero = User::factory()->create(['role' => 'tesorero']);
    $this->actingAs($tesorero);
    $this->get(route('arriendos.show', $reserva))->assertForbidden();
    $this->actingAs($this->presidente3b);
    confirmarBloque3b($this, $reserva);
    $this->actingAs($tesorero);
    $this->get(route('arriendos.show', $reserva))->assertOk();
    $this->get(route('arriendos.comprobante', $reserva))->assertOk();
});

test('finanzas registra solo el arriendo validado y separa la garantía sin duplicar ingresos', function (string $rol) {
    $reserva = reservaBloque3b($this->socio3b);
    $this->post(route('finanzas.arriendos.registrar', $reserva), ['fecha_pago' => now()->toDateString(), 'medio_pago' => 'transferencia'])
        ->assertStatus(422);
    confirmarBloque3b($this, $reserva);
    $usuario = User::factory()->create(['role' => $rol]);
    $this->actingAs($usuario);
    registrarBloque3b($this, $reserva);
    expect(Ingreso::sole()->concepto)->toBe('arriendo')->and(Ingreso::sole()->monto)->toBe(40000)
        ->and(Ingreso::sole()->registrado_por)->toBe($usuario->id)
        ->and(Ingreso::saldoCaja())->toBe(40000)
        ->and($reserva->fresh()->garantia->registrada_por)->toBe($usuario->id);
    $this->post(route('finanzas.arriendos.registrar', $reserva), ['fecha_pago' => now()->toDateString(), 'medio_pago' => 'transferencia'])
        ->assertSessionHasErrors('finanzas');
    $this->assertDatabaseCount('ingresos', 1);
    $this->get(route('finanzas.arriendos.index'))->assertOk();
    $this->get(route('ingresos.index'))->assertOk();
})->with(['presidente', 'tesorero']);

test('efectivo exige talonario y transferencias reutilizan el comprobante', function () {
    $reserva = reservaBloque3b($this->socio3b);
    confirmarBloque3b($this, $reserva);
    $datos = ['fecha_pago' => now()->toDateString(), 'medio_pago' => 'efectivo'];
    $this->post(route('finanzas.arriendos.registrar', $reserva), $datos)->assertSessionHasErrors('talonario');
    $this->post(route('finanzas.arriendos.registrar', $reserva), $datos + ['talonario' => 'T-3B'])
        ->assertSessionHasNoErrors();
    expect(Ingreso::sole()->talonario)->toBe('T-3B');
});

test('anular el pago permite registrarlo nuevamente sin duplicar la garantía', function () {
    $reserva = reservaBloque3b($this->socio3b);
    confirmarBloque3b($this, $reserva);
    registrarBloque3b($this, $reserva);
    $garantia = $reserva->fresh()->garantia;
    $this->post(route('ingresos.anular', Ingreso::sole()), ['motivo_anulacion' => 'Error de registro'])
        ->assertSessionHasNoErrors();
    expect(Ingreso::saldoCaja())->toBe(0);
    registrarBloque3b($this, $reserva);
    expect($reserva->ingresos()->vigentes()->count())->toBe(1)
        ->and($reserva->fresh()->garantia->id)->toBe($garantia->id)
        ->and(Ingreso::saldoCaja())->toBe(40000);
    $this->assertDatabaseCount('garantias', 1);
    $this->assertDatabaseCount('ingresos', 2);
});

test('devolver la garantía completa no descuenta caja ni crea otro movimiento', function () {
    $reserva = reservaBloque3b($this->socio3b);
    confirmarBloque3b($this, $reserva);
    registrarBloque3b($this, $reserva);
    $garantia = $reserva->fresh()->garantia;
    $this->post(route('finanzas.garantias.por-devolver', $garantia))->assertSessionHasNoErrors();
    $this->post(route('finanzas.garantias.devolver', $garantia), ['fecha_resolucion' => now()->toDateString()])
        ->assertSessionHasNoErrors();
    expect($garantia->fresh()->estado)->toBe('devuelta')
        ->and($garantia->fresh()->monto_devuelto)->toBe(40000)
        ->and($garantia->fresh()->resuelta_por)->toBe($this->presidente3b->id)
        ->and(Ingreso::saldoCaja())->toBe(40000);
    $this->assertDatabaseCount('ingresos', 1);
    $this->assertDatabaseCount('devoluciones', 0);
    $this->assertDatabaseCount('egresos', 0);
    $this->post(route('finanzas.garantias.devolver', $garantia), ['fecha_resolucion' => now()->toDateString()])
        ->assertSessionHasErrors('finanzas');
    expect(Ingreso::saldoCaja())->toBe(40000);
});

test('retener parte o toda la garantía crea ingreso solo por lo retenido y audita ambos registros', function (int $retenido, int $devuelto, string $rol) {
    $reserva = reservaBloque3b($this->socio3b);
    confirmarBloque3b($this, $reserva);
    $usuario = User::factory()->create(['role' => $rol]);
    $this->actingAs($usuario);
    registrarBloque3b($this, $reserva);
    $garantia = $reserva->fresh()->garantia;
    $datos = ['fecha_resolucion' => now()->toDateString(), 'monto_retenido' => $retenido, 'motivo_retencion' => 'Reparación de puerta'];
    $this->post(route('finanzas.garantias.retener', $garantia), $datos)->assertSessionHasNoErrors();
    expect($garantia->fresh()->estado)->toBe('retenida')
        ->and($garantia->fresh()->monto_retenido)->toBe($retenido)
        ->and($garantia->fresh()->monto_devuelto)->toBe($devuelto)
        ->and($garantia->fresh()->resuelta_por)->toBe($usuario->id)
        ->and($garantia->ingresos()->sole()->monto)->toBe($retenido)
        ->and(Ingreso::saldoCaja())->toBe(40000 + $retenido);
    foreach ([Garantia::class => $garantia->id, Ingreso::class => $garantia->ingresos()->sole()->id] as $modelo => $id) {
        $this->assertDatabaseHas('auditorias', ['auditable_type' => $modelo, 'auditable_id' => $id, 'user_id' => $usuario->id]);
    }
    $this->post(route('finanzas.garantias.retener', $garantia), $datos)->assertSessionHasErrors('finanzas');
    $this->post(route('finanzas.garantias.devolver', $garantia), ['fecha_resolucion' => now()->toDateString()])->assertSessionHasErrors('finanzas');
    $this->assertDatabaseCount('ingresos', 2);
})->with([[15000, 25000], [40000, 0]])->with(['presidente', 'tesorero']);

test('retención rechaza montos imposibles falta de motivo y fechas inválidas sin resolver la garantía', function (array $cambios, string $campo) {
    $reserva = reservaBloque3b($this->socio3b);
    confirmarBloque3b($this, $reserva);
    registrarBloque3b($this, $reserva);
    $garantia = $reserva->fresh()->garantia;
    $this->post(route('finanzas.garantias.retener', $garantia), array_replace([
        'fecha_resolucion' => now()->toDateString(), 'monto_retenido' => 15000, 'motivo_retencion' => 'Daños',
    ], $cambios))->assertSessionHasErrors($campo);
    expect($garantia->fresh()->estado)->toBe('recibida')->and(Ingreso::saldoCaja())->toBe(40000);
    $this->assertDatabaseCount('ingresos', 1);
})->with([
    'cero' => [['monto_retenido' => 0], 'monto_retenido'],
    'negativo' => [['monto_retenido' => -1], 'monto_retenido'],
    'decimal' => [['monto_retenido' => 1.5], 'monto_retenido'],
    'supera garantía' => [['monto_retenido' => 40001], 'monto_retenido'],
    'sin motivo' => [['motivo_retencion' => ''], 'motivo_retencion'],
    'futura' => [['fecha_resolucion' => '2999-01-01'], 'fecha_resolucion'],
    'anterior al pago' => [['fecha_resolucion' => '2000-01-01'], 'fecha_resolucion'],
]);

test('una garantía sin registro financiero no puede devolverse ni retenerse', function () {
    $reserva = reservaBloque3b($this->socio3b);
    confirmarBloque3b($this, $reserva);
    $garantia = $reserva->fresh()->garantia;
    $this->post(route('finanzas.garantias.devolver', $garantia), ['fecha_resolucion' => now()->toDateString()])->assertSessionHasErrors('finanzas');
    $this->post(route('finanzas.garantias.retener', $garantia), ['fecha_resolucion' => now()->toDateString(), 'monto_retenido' => 100, 'motivo_retencion' => 'Daños'])->assertSessionHasErrors('finanzas');
    $this->assertDatabaseCount('ingresos', 0);
});

test('anular y registrar nuevamente una retención conserva la devolución y no duplica ingresos vigentes', function () {
    $reserva = reservaBloque3b($this->socio3b);
    confirmarBloque3b($this, $reserva);
    registrarBloque3b($this, $reserva);
    $garantia = $reserva->fresh()->garantia;
    $this->post(route('finanzas.garantias.retener', $garantia), ['fecha_resolucion' => now()->toDateString(), 'monto_retenido' => 15000, 'motivo_retencion' => 'Daños'])->assertSessionHasNoErrors();
    $ingreso = $garantia->ingresos()->sole();
    $this->post(route('ingresos.anular', $ingreso), ['motivo_anulacion' => 'Registro corregido'])->assertSessionHasNoErrors();
    expect(Ingreso::saldoCaja())->toBe(40000);
    $this->post(route('finanzas.garantias.registrar-retencion', $garantia))->assertSessionHasNoErrors();
    $this->post(route('finanzas.garantias.registrar-retencion', $garantia))->assertSessionHasErrors('finanzas');
    expect($garantia->fresh()->monto_devuelto)->toBe(25000)
        ->and($garantia->ingresos()->vigentes()->count())->toBe(1)
        ->and(Ingreso::saldoCaja())->toBe(55000);
    $this->assertDatabaseCount('ingresos', 3);
});

test('finanzas está restringido a presidente y tesorero', function (string $rol) {
    $reserva = reservaBloque3b($this->socio3b);
    confirmarBloque3b($this, $reserva);
    $garantia = $reserva->fresh()->garantia;
    $this->actingAs(User::factory()->create(['role' => $rol]));
    $this->get(route('finanzas.arriendos.index'))->assertForbidden();
    $this->post(route('finanzas.arriendos.registrar', $reserva), [])->assertForbidden();
    foreach (['por-devolver', 'devolver', 'retener', 'registrar-retencion'] as $accion) {
        $this->post(route('finanzas.garantias.'.$accion, $garantia), [])->assertForbidden();
    }
    $this->assertDatabaseCount('ingresos', 0);
})->with(['secretario', 'admin', 'vecino']);

test('tesorero administrador y vecino ajeno no pueden confirmar ni cancelar reservas', function (string $rol) {
    $reserva = reservaBloque3b($this->socio3b);
    $this->actingAs($rol === 'vecino' ? socioBloque3b()->user : User::factory()->create(['role' => $rol]));
    $this->post(route('arriendos.confirmar', $reserva), ['pago_verificado' => 1, 'garantia_verificada' => 1])->assertForbidden();
    $this->post(route('reservas.cancelar', $reserva))->assertForbidden();
    expect($reserva->fresh()->estado)->toBe('pendiente');
})->with(['tesorero', 'admin', 'vecino']);

test('el socio titular puede cancelar su reserva pero no confirmarla', function () {
    $reserva = reservaBloque3b($this->socio3b);
    $this->actingAs($this->socio3b->user);
    $this->post(route('arriendos.confirmar', $reserva), ['pago_verificado' => 1, 'garantia_verificada' => 1])->assertForbidden();
    expect($reserva->fresh()->estado)->toBe('pendiente');
    $this->post(route('reservas.cancelar', $reserva))->assertRedirect()->assertSessionHasNoErrors();
    $reserva->refresh();
    expect($reserva->estado)->toBe('cancelada')
        ->and($reserva->cancelada_por)->toBe($this->socio3b->user_id)
        ->and(Reserva::ocupan()->whereKey($reserva->id)->exists())->toBeFalse();
    $this->assertDatabaseCount('ingresos', 0);
});

test('la garantía no se elimina', function () {
    $reserva = reservaBloque3b($this->socio3b);
    confirmarBloque3b($this, $reserva);
    expect(fn () => $reserva->fresh()->garantia->delete())->toThrow(LogicException::class);
    $this->assertDatabaseCount('garantias', 1);
});
