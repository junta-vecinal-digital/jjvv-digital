<?php

use App\Models\Auditoria;
use App\Models\Garantia;
use App\Models\Ingreso;
use App\Models\ItemInventario;
use App\Models\SolicitudInsumo;
use App\Models\TipoInsumo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->withoutVite();
    Storage::fake('local');
    $this->travelTo(Carbon::parse('2026-10-17 10:00:00'));
    $this->presidenteFinInsumos = User::factory()->create(['role' => 'presidente']);
    $this->tesoreroFinInsumos = User::factory()->create(['role' => 'tesorero']);
    $this->actingAs($this->presidenteFinInsumos);
});

afterEach(function () { $this->travelBack(); });

function solicitudFinInsumos(User $responsable, array $cambios = []): SolicitudInsumo
{
    $solicitud = SolicitudInsumo::create(array_replace([
        'estado' => 'aprobado', 'solicitante_id' => $responsable->id, 'solicitante_nombre' => $responsable->name,
        'telefono_contacto' => '912345678', 'canal' => 'en_linea', 'fecha_comprometida' => '2026-10-20',
        'responsable_id' => $responsable->id, 'responsable_nombre' => $responsable->name,
        'monto' => 7000, 'garantia' => 12000, 'registrado_por' => $responsable->id,
        'aprobado_por' => $responsable->id, 'aprobado_en' => now(),
    ], $cambios));
    $item = ItemInventario::firstOrCreate(['nombre' => 'Sillas financieras'], [
        'cantidad' => 10, 'tipo_insumo_id' => TipoInsumo::where('nombre', 'Sillas')->value('id'),
    ]);
    $solicitud->lineas()->create(['item_inventario_id' => $item->id, 'nombre_insumo' => $item->nombre, 'cantidad' => 2]);
    return $solicitud;
}

function pagoFinInsumos(array $cambios = []): array
{
    return array_replace(['fecha_pago' => '2026-10-17', 'medio_pago' => 'transferencia', 'numero_transaccion' => 'TRX-INSUMOS'], $cambios);
}

function devolverFinInsumos($caso, SolicitudInsumo $solicitud, User $presidente): void
{
    $caso->actingAs($presidente)->post(route('insumos.entregar', $solicitud), [
        'fecha_entrega' => '2026-10-17', 'condiciones' => $solicitud->lineas->mapWithKeys(fn ($l) => [$l->id => 'bueno'])->all(),
    ])->assertSessionHasNoErrors();
    $caso->post(route('insumos.devolver', $solicitud), [
        'fecha_devolucion' => '2026-10-17',
        'devoluciones' => $solicitud->lineas->mapWithKeys(fn ($l) => [$l->id => ['estado_regreso' => 'bueno', 'cantidad_danada' => 0]])->all(),
    ])->assertSessionHasNoErrors();
}

test('registra cobro vinculado y garantía separada con monto y autor del servidor', function (string $rol) {
    $solicitud = solicitudFinInsumos($this->presidenteFinInsumos);
    $usuario = $rol === 'presidente' ? $this->presidenteFinInsumos : $this->tesoreroFinInsumos;
    $this->actingAs($usuario)->post(route('finanzas.insumos.registrar', $solicitud), pagoFinInsumos([
        'monto' => 1, 'garantia' => 1, 'registrado_por' => 999999,
        'comprobante' => UploadedFile::fake()->create('total.pdf', 10, 'application/pdf'),
    ]))->assertSessionHasNoErrors();
    $ingreso = Ingreso::sole();
    $garantia = Garantia::sole();
    expect($ingreso->concepto)->toBe('prestamo_insumos')->and($ingreso->monto)->toBe(7000)
        ->and($ingreso->solicitud_insumo_id)->toBe($solicitud->id)->and($ingreso->registrado_por)->toBe($usuario->id)
        ->and($ingreso->tipo_cuota)->toBeNull()->and($garantia->monto)->toBe(12000)
        ->and($garantia->reserva_id)->toBeNull()->and($garantia->solicitud_insumo_id)->toBe($solicitud->id)
        ->and($garantia->registrada_por)->toBe($usuario->id)->and(Ingreso::saldoCaja())->toBe(7000);
    Storage::disk('local')->assertExists($solicitud->fresh()->comprobante_path);
    $this->get(route('finanzas.insumos.index'))->assertOk();
    $this->get(route('finanzas.insumos.show', $solicitud))->assertOk();
    $this->get(route('finanzas.insumos.comprobante', $solicitud))->assertOk();
    if ($rol === 'tesorero') {
        $this->get(route('insumos.show', $solicitud))->assertForbidden();
        $this->get(route('inventario.index'))->assertForbidden();
    }
    $this->get(route('ingresos.index'))->assertOk()->assertSee(route('finanzas.insumos.show', $solicitud));
    $antes = Auditoria::count();
    $archivos = Storage::disk('local')->allFiles();
    $this->post(route('finanzas.insumos.registrar', $solicitud), pagoFinInsumos())->assertSessionHasErrors('finanzas');
    expect(Auditoria::count())->toBe($antes)->and(Storage::disk('local')->allFiles())->toBe($archivos);
    $this->assertDatabaseCount('ingresos', 1);
    $this->assertDatabaseCount('garantias', 1);
})->with(['presidente', 'tesorero']);

test('operaciones de cero no crean ingresos y sin garantía no crean garantías', function (int $monto, int $garantia) {
    $solicitud = solicitudFinInsumos($this->presidenteFinInsumos, ['monto' => $monto, 'garantia' => $garantia]);
    $respuesta = $this->post(route('finanzas.insumos.registrar', $solicitud), pagoFinInsumos());
    if ($monto + $garantia === 0) $respuesta->assertSessionHasErrors('finanzas');
    else $respuesta->assertSessionHasNoErrors();
    $this->assertDatabaseCount('ingresos', $monto > 0 ? 1 : 0);
    $this->assertDatabaseCount('garantias', $garantia > 0 ? 1 : 0);
    expect(Ingreso::saldoCaja())->toBe($monto);
})->with([[0, 0], [0, 12000], [7000, 0]]);

test('la recepción valida talonario transferencia archivo y fecha sin dejar movimientos parciales', function (string $caso) {
    $solicitud = solicitudFinInsumos($this->presidenteFinInsumos);
    $datos = pagoFinInsumos();
    if ($caso === 'efectivo') $datos = ['fecha_pago' => '2026-10-17', 'medio_pago' => 'efectivo'];
    if ($caso === 'sin respaldo') $datos['numero_transaccion'] = null;
    if ($caso === 'archivo') $datos['comprobante'] = UploadedFile::fake()->create('archivo.txt', 10, 'text/plain');
    if ($caso === 'tamaño') $datos['comprobante'] = UploadedFile::fake()->create('archivo.pdf', 5121, 'application/pdf');
    if ($caso === 'futura') $datos['fecha_pago'] = '2026-10-18';
    if ($caso === 'anterior') $datos['fecha_pago'] = '2026-10-16';
    $this->post(route('finanzas.insumos.registrar', $solicitud), $datos)->assertSessionHasErrors();
    expect($solicitud->fresh()->pago_registrado_en)->toBeNull()->and(Storage::disk('local')->allFiles())->toBe([]);
    $this->assertDatabaseCount('ingresos', 0);
    $this->assertDatabaseCount('garantias', 0);
})->with(['efectivo', 'sin respaldo', 'archivo', 'tamaño', 'futura', 'anterior']);

test('transferencia admite comprobante sin número y efectivo se respalda con talonario', function (string $medio) {
    $solicitud = solicitudFinInsumos($this->presidenteFinInsumos);
    $datos = ['fecha_pago' => '2026-10-17', 'medio_pago' => $medio];
    if ($medio === 'transferencia') $datos['comprobante'] = UploadedFile::fake()->create('pago.pdf', 10, 'application/pdf');
    else $datos['talonario'] = 'T-INSUMOS';
    $this->post(route('finanzas.insumos.registrar', $solicitud), $datos)->assertSessionHasNoErrors();
    expect(Ingreso::sole()->medio_pago)->toBe($medio)->and(Garantia::sole()->medio_pago)->toBe($medio);
})->with(['efectivo', 'transferencia']);

test('anular y registrar nuevamente conserva recepción comprobante y garantía originales', function () {
    $solicitud = solicitudFinInsumos($this->presidenteFinInsumos);
    $this->post(route('finanzas.insumos.registrar', $solicitud), pagoFinInsumos())->assertSessionHasNoErrors();
    $recepcion = $solicitud->refresh()->getAttributes();
    $garantia = Garantia::sole()->getAttributes();
    $this->post(route('ingresos.anular', Ingreso::sole()), ['motivo_anulacion' => 'Error de registro'])->assertSessionHasNoErrors();
    expect(Ingreso::saldoCaja())->toBe(0);
    $this->actingAs($this->tesoreroFinInsumos)->post(route('finanzas.insumos.reingresar', $solicitud), ['monto' => 1])->assertSessionHasNoErrors();
    expect($solicitud->fresh()->getAttributes())->toBe($recepcion)->and(Garantia::sole()->getAttributes())->toBe($garantia)
        ->and($solicitud->ingresos()->vigentes()->sole()->registrado_por)->toBe($this->tesoreroFinInsumos->id)
        ->and(Ingreso::saldoCaja())->toBe(7000);
    $this->post(route('finanzas.insumos.reingresar', $solicitud))->assertSessionHasErrors('finanzas');
    $this->assertDatabaseCount('garantias', 1);
});

test('garantía requiere devolución completa y devolverla no descuenta otra vez de caja', function () {
    $solicitud = solicitudFinInsumos($this->presidenteFinInsumos);
    $this->post(route('finanzas.insumos.registrar', $solicitud), pagoFinInsumos())->assertSessionHasNoErrors();
    $garantia = Garantia::sole();
    $this->post(route('finanzas.garantias.devolver', $garantia), ['fecha_resolucion' => '2026-10-17'])->assertSessionHasErrors('finanzas');
    devolverFinInsumos($this, $solicitud, $this->presidenteFinInsumos);
    expect($garantia->fresh()->estado)->toBe('por_devolver');
    $this->actingAs($this->tesoreroFinInsumos)->post(route('finanzas.garantias.devolver', $garantia), ['fecha_resolucion' => '2026-10-17'])->assertSessionHasNoErrors();
    expect($garantia->fresh()->monto_devuelto)->toBe(12000)->and(Ingreso::saldoCaja())->toBe(7000);
    $this->post(route('finanzas.garantias.retener', $garantia), ['fecha_resolucion' => '2026-10-17', 'monto_retenido' => 1, 'motivo_retencion' => 'Otro intento'])->assertSessionHasErrors('finanzas');
    $this->assertDatabaseCount('ingresos', 1);
});

test('retención parcial o total genera solo lo retenido y reingreso no repite devolución', function (int $retenido) {
    $solicitud = solicitudFinInsumos($this->presidenteFinInsumos);
    devolverFinInsumos($this, $solicitud, $this->presidenteFinInsumos);
    // El registro financiero posterior a la devolución deja la garantía por devolver.
    $this->post(route('finanzas.insumos.registrar', $solicitud), pagoFinInsumos())->assertSessionHasNoErrors();
    $garantia = Garantia::sole();
    $this->post(route('finanzas.garantias.retener', $garantia), [
        'fecha_resolucion' => '2026-10-17', 'monto_retenido' => $retenido, 'motivo_retencion' => 'Daños verificados',
    ])->assertSessionHasNoErrors();
    $garantia->refresh();
    expect($garantia->monto_devuelto)->toBe(12000 - $retenido)->and($garantia->monto_retenido)->toBe($retenido)
        ->and(Ingreso::saldoCaja())->toBe(7000 + $retenido);
    $resolucion = $garantia->getAttributes();
    $retencion = $garantia->ingresos()->sole();
    $this->post(route('ingresos.anular', $retencion), ['motivo_anulacion' => 'Reingresar correctamente'])->assertSessionHasNoErrors();
    $this->post(route('finanzas.garantias.registrar-retencion', $garantia))->assertSessionHasNoErrors();
    $this->post(route('finanzas.garantias.registrar-retencion', $garantia))->assertSessionHasErrors('finanzas');
    expect($garantia->fresh()->getAttributes())->toBe($resolucion)->and($garantia->ingresos()->vigentes()->count())->toBe(1)
        ->and(Ingreso::saldoCaja())->toBe(7000 + $retenido);
    $this->get(route('ingresos.index'))->assertOk()->assertSee('Préstamo y comprobante');
    $this->get(route('finanzas.insumos.show', $solicitud))->assertOk();
})->with([5000, 12000]);

test('solo presidente y tesorero acceden a finanzas y comprobantes sin ampliar gestión del préstamo', function (string $rol) {
    $solicitud = solicitudFinInsumos($this->presidenteFinInsumos);
    $this->post(route('finanzas.insumos.registrar', $solicitud), pagoFinInsumos())->assertSessionHasNoErrors();
    $garantia = Garantia::sole();
    $this->actingAs(User::factory()->create(['role' => $rol]));
    foreach (['index', 'show', 'comprobante'] as $accion) $this->get(route('finanzas.insumos.'.$accion, $accion === 'index' ? [] : $solicitud))->assertForbidden();
    $this->post(route('finanzas.insumos.registrar', $solicitud), pagoFinInsumos())->assertForbidden();
    $this->post(route('finanzas.insumos.reingresar', $solicitud))->assertForbidden();
    $this->post(route('finanzas.garantias.devolver', $garantia), ['fecha_resolucion' => '2026-10-17'])->assertForbidden();
})->with(['secretario', 'admin', 'vecino']);

test('solicitud pendiente y concepto genérico no permiten registrar cobros de insumos', function () {
    $solicitud = solicitudFinInsumos($this->presidenteFinInsumos, ['estado' => 'pendiente', 'monto' => null, 'garantia' => null]);
    $this->post(route('finanzas.insumos.registrar', $solicitud), pagoFinInsumos())->assertSessionHasErrors('finanzas');
    $this->get(route('finanzas.insumos.show', $solicitud))->assertNotFound();
    $this->post(route('ingresos.store'), [
        'concepto' => 'prestamo_insumos', 'monto' => 7000, 'fecha_pago' => '2026-10-17',
        'medio_pago' => 'transferencia', 'numero_transaccion' => 'TRX',
    ])->assertSessionHasErrors('concepto');
    $this->assertDatabaseCount('ingresos', 0);
    $this->assertDatabaseCount('garantias', 0);
});

test('resolución rechaza fechas anteriores a la devolución y retenciones inválidas sin alterar garantía ni saldo', function (array $cambios, string $campo) {
    $solicitud = solicitudFinInsumos($this->presidenteFinInsumos, [
        'estado' => 'devuelto', 'fecha_entrega' => '2026-10-17', 'fecha_devolucion' => '2026-10-18',
    ]);
    $this->travelTo(Carbon::parse('2026-10-18 10:00:00'));
    $this->post(route('finanzas.insumos.registrar', $solicitud), pagoFinInsumos())->assertSessionHasNoErrors();
    $garantia = Garantia::sole();
    $antes = $garantia->getAttributes();
    $auditorias = Auditoria::count();
    $this->post(route('finanzas.garantias.retener', $garantia), array_replace([
        'fecha_resolucion' => '2026-10-18', 'monto_retenido' => 5000, 'motivo_retencion' => 'Daños',
    ], $cambios))->assertSessionHasErrors($campo);
    expect($garantia->fresh()->getAttributes())->toBe($antes)->and(Auditoria::count())->toBe($auditorias)
        ->and(Ingreso::saldoCaja())->toBe(7000);
    $this->assertDatabaseCount('ingresos', 1);
})->with([
    [['fecha_resolucion' => '2026-10-17'], 'fecha_resolucion'],
    [['monto_retenido' => 12001], 'monto_retenido'], [['monto_retenido' => 0], 'monto_retenido'],
    [['monto_retenido' => -1], 'monto_retenido'], [['motivo_retencion' => ''], 'motivo_retencion'],
]);
