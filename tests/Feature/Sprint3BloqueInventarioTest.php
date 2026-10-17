<?php

use App\Models\ItemInventario;
use App\Models\User;
use App\Models\Grupo;
use App\Models\Prestamo;
use App\Models\TipoInsumo;

beforeEach(function () {
    $this->withoutVite();
});

test('presidente y secretario registran ítems con cantidad y autor automático', function (string $rol) {
    $usuario = User::factory()->create(['role' => $rol]);
    $this->actingAs($usuario)->post(route('inventario.store'), [
        'tipo_insumo_id' => TipoInsumo::where('nombre', 'Sillas')->value('id'), 'nombre' => 'Nombre manipulado', 'cantidad' => 20, 'registrado_por' => 999999,
    ])->assertSessionHasNoErrors()->assertRedirect(route('inventario.index'));
    $item = ItemInventario::sole();
    expect($item->nombre)->toBe('Sillas')->and($item->cantidad)->toBe(20)
        ->and($item->registrado_por)->toBe($usuario->id);
    $this->get(route('inventario.index'))->assertOk()->assertSee('Sillas');
})->with(['presidente', 'secretario']);

test('el inventario admite registrar un ítem aún sin existencias', function () {
    $this->actingAs(User::factory()->create(['role' => 'presidente']))->post(route('inventario.store'), [
        'tipo_insumo_id' => TipoInsumo::where('nombre', 'Mesas')->value('id'), 'cantidad' => 0,
    ])->assertSessionHasNoErrors();
    expect(ItemInventario::sole()->cantidad)->toBe(0);
});

test('inventario rechaza insumos sin seleccionar y cantidades inválidas', function (array $cambios, string $campo) {
    $this->actingAs(User::factory()->create(['role' => 'presidente']))->post(route('inventario.store'), array_replace([
        'tipo_insumo_id' => TipoInsumo::where('nombre', 'Sillas')->value('id'), 'cantidad' => 20,
    ], $cambios))->assertSessionHasErrors($campo);
    $this->assertDatabaseCount('items_inventario', 0);
})->with([
    [['tipo_insumo_id' => null], 'tipo_insumo_id'],
    [['cantidad' => -1], 'cantidad'],
    [['cantidad' => '2.5'], 'cantidad'],
    [['cantidad' => 4294967296], 'cantidad'],
]);

test('los demás roles no acceden ni registran inventario', function (string $rol) {
    $this->actingAs(User::factory()->create(['role' => $rol]))->get(route('inventario.index'))->assertForbidden();
    $this->post(route('inventario.store'), ['nombre' => 'Sillas', 'cantidad' => 20])->assertForbidden();
    $this->assertDatabaseCount('items_inventario', 0);
})->with(['tesorero', 'admin', 'vecino']);

test('inventario exige iniciar sesión', function () {
    $this->get(route('inventario.index'))->assertRedirect(route('login'));
    $this->post(route('inventario.store'), [])->assertRedirect(route('login'));
});

function datosPrestamoInventario(int $itemId, array $cambios = []): array
{
    return array_replace([
        'item_inventario_id' => $itemId, 'cantidad' => 3, 'destino' => 'libre',
        'destinatario_nombre' => 'Comité del pasaje', 'responsable_nombre' => 'Responsable sin cuenta',
        'responsable_telefono' => '912345678', 'fecha_solicitud' => now()->subDays(2)->toDateString(),
        'fecha_prestamo' => now()->subDay()->toDateString(), 'estado_salida' => 'bueno',
    ], $cambios);
}

function prestamoHistoricoInventario(int $itemId, array $cambios = []): Prestamo
{
    $datos = datosPrestamoInventario($itemId, $cambios);
    unset($datos['destino']);
    return Prestamo::create($datos + ['registrado_por' => auth()->id()]);
}
test('las altas antiguas se redirigen a solicitudes y no permiten préstamos nuevos a nombre libre', function (string $rol) {
    $this->actingAs(User::factory()->create(['role' => $rol]));
    $item = ItemInventario::create(['nombre' => 'Sillas', 'cantidad' => 5]);
    $this->get(route('inventario.prestamos.create'))->assertRedirect(route('insumos.index'));
    $this->post(route('inventario.prestamos.store'), datosPrestamoInventario($item->id))
        ->assertRedirect(route('insumos.index'))->assertSessionHasErrors('operacion');
    $this->assertDatabaseCount('prestamos', 0);
    expect($item->fresh()->disponibles())->toBe(5);
})->with(['presidente', 'secretario']);
test('un préstamo histórico conserva el nombre anterior de un grupo sin representante', function () {
    $grupo = Grupo::create(['nombre' => 'Grupo original']);
    $item = ItemInventario::create(['nombre' => 'Mesas', 'cantidad' => 5]);
    $this->actingAs(User::factory()->create(['role' => 'presidente']));
    $prestamo = prestamoHistoricoInventario($item->id, ['grupo_id' => $grupo->id, 'destinatario_nombre' => 'Grupo original']);
    $grupo->update(['nombre' => 'Grupo renombrado']);
    expect($prestamo->fresh()->destinatario_nombre)->toBe('Grupo original');
    $this->get(route('inventario.prestamos.show', $prestamo))->assertOk()->assertSee('Grupo original');
});
test('devolver cierra el préstamo completo y las unidades dañadas permanecen fuera de servicio', function (int $danadas) {
    $usuario = User::factory()->create(['role' => 'secretario']);
    $item = ItemInventario::create(['nombre' => 'Sillas', 'cantidad' => 5]);
    $this->actingAs($usuario); prestamoHistoricoInventario($item->id);
    $prestamo = Prestamo::sole();
    $datos = ['fecha_devolucion' => now()->toDateString(), 'estado_regreso' => $danadas ? 'danado' : 'bueno',
        'cantidad_danada' => $danadas, 'devuelto_por' => 999999, 'cantidad' => 1];
    if ($danadas === 0) unset($datos['cantidad_danada']);
    $this->post(route('inventario.prestamos.devolver', $prestamo), $datos)->assertSessionHasNoErrors();
    $devuelto = $prestamo->fresh();
    expect($devuelto->fecha_devolucion)->not->toBeNull()->and($devuelto->cantidad)->toBe(3)
        ->and($devuelto->devuelto_por)->toBe($usuario->id)->and($devuelto->cantidad_danada)->toBe($danadas)
        ->and($item->fresh()->disponibles())->toBe(5 - $danadas);
    $this->get(route('inventario.prestamos.show', $prestamo))->assertOk()->assertSee('Devolución completa');
    $this->post(route('inventario.prestamos.devolver', $prestamo), $datos)->assertSessionHasErrors('devolucion');
    expect($item->fresh()->disponibles())->toBe(5 - $danadas);
    $this->post(route('inventario.prestamos.store'), datosPrestamoInventario($item->id, ['cantidad' => 6 - $danadas]))
        ->assertSessionHasErrors('operacion');
})->with([0, 1, 3]);

test('devolver un préstamo conserva descontadas las unidades que siguen en otro préstamo', function () {
    $item = ItemInventario::create(['nombre' => 'Sillas', 'cantidad' => 10]);
    $this->actingAs(User::factory()->create(['role' => 'presidente']));
    prestamoHistoricoInventario($item->id, ['cantidad' => 5]);
    $primero = Prestamo::sole();
    prestamoHistoricoInventario($item->id);
    $this->post(route('inventario.prestamos.devolver', $primero), [
        'fecha_devolucion' => now()->toDateString(), 'estado_regreso' => 'danado', 'cantidad_danada' => 2,
    ])->assertSessionHasNoErrors();
    $item = ItemInventario::conExistencias()->findOrFail($item->id);
    expect((int) $item->prestadas)->toBe(3)->and((int) $item->fuera_servicio)->toBe(2)->and($item->disponibles())->toBe(5);
    $this->get(route('inventario.prestamos.index', ['estado' => 'prestado']))->assertOk()
        ->assertViewHas('prestamos', fn ($lista) => $lista->total() === 1);
    $this->get(route('inventario.prestamos.index', ['estado' => 'devuelto']))->assertOk()
        ->assertViewHas('prestamos', fn ($lista) => $lista->total() === 1);
});

test('devoluciones inválidas no liberan stock ni cierran el préstamo', function (array $cambios, string $campo) {
    $item = ItemInventario::create(['nombre' => 'Sillas', 'cantidad' => 5]);
    $this->actingAs(User::factory()->create(['role' => 'presidente'])); prestamoHistoricoInventario($item->id);
    $prestamo = Prestamo::sole();
    $this->post(route('inventario.prestamos.devolver', $prestamo), array_replace([
        'fecha_devolucion' => now()->toDateString(), 'estado_regreso' => 'danado', 'cantidad_danada' => 1,
    ], $cambios))->assertSessionHasErrors($campo);
    expect($prestamo->fresh()->fecha_devolucion)->toBeNull()->and($item->fresh()->disponibles())->toBe(2);
})->with([
    [['cantidad_danada' => -1], 'cantidad_danada'], [['cantidad_danada' => 4], 'cantidad_danada'],
    [['cantidad_danada' => 0], 'cantidad_danada'], [['cantidad_danada' => '1.5'], 'cantidad_danada'],
    [['estado_regreso' => 'bueno', 'cantidad_danada' => 1], 'cantidad_danada'],
    [['fecha_devolucion' => '1900-01-01'], 'fecha_devolucion'], [['fecha_devolucion' => '2999-01-01'], 'fecha_devolucion'],
]);

test('las rutas de préstamos exigen presidente o secretario incluso por URL directa', function (string $rol) {
    $item = ItemInventario::create(['nombre' => 'Sillas', 'cantidad' => 5]);
    $datos = datosPrestamoInventario($item->id);
    $prestamo = Prestamo::create(array_diff_key($datos, ['destino' => true]));
    $this->actingAs(User::factory()->create(['role' => $rol]));
    $this->get(route('inventario.prestamos.index'))->assertForbidden();
    $this->get(route('inventario.prestamos.create'))->assertForbidden();
    $this->get(route('inventario.prestamos.show', $prestamo))->assertForbidden();
    $this->post(route('inventario.prestamos.store'), $datos)->assertForbidden();
    $this->post(route('inventario.prestamos.devolver', $prestamo), [])->assertForbidden();
    expect($prestamo->fresh()->fecha_devolucion)->toBeNull();
})->with(['tesorero', 'admin', 'vecino']);
