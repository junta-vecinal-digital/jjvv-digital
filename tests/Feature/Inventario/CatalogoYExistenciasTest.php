<?php

use App\Models\ItemInventario;
use App\Models\Prestamo;
use App\Models\TipoInsumo;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
});

test('el catálogo inicial contiene las cuatro categorías y los insumos acordados', function () {
    expect(TipoInsumo::count())->toBe(15);
    foreach (['Sillas', 'Mesas', 'Toldos', 'Carpas estructurales', 'Equipos de amplificación',
        'Herramientas de jardinería', 'Herramientas eléctricas', 'Escalas o escaleras extensibles',
        'Vajilla', 'Mantelería', 'Catres clínicos', 'Sillas de ruedas', 'Burritos', 'Muletas', 'Bastones'] as $nombre) {
        $this->assertDatabaseHas('tipos_insumo', ['nombre' => $nombre]);
    }
    expect(TipoInsumo::distinct()->pluck('categoria')->sort()->values()->all())
        ->toBe(collect(array_keys(TipoInsumo::CATEGORIAS))->sort()->values()->all());
});

test('solo el administrador amplía el catálogo desde parámetros y el insumo aparece en inventario', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('parametros.index'))->assertOk()
        ->assertSee('Catálogo de insumos');
    $this->post(route('parametros.insumos.store'), ['nombre_insumo' => 'Proyector', 'categoria_insumo' => 'equipamiento'])
        ->assertSessionHasNoErrors()->assertRedirect(route('parametros.index'));
    $tipo = TipoInsumo::where('nombre', 'Proyector')->sole();
    $this->actingAs(User::factory()->create(['role' => 'presidente']))->get(route('inventario.index'))->assertOk()
        ->assertViewHas('tiposInsumo', fn ($lista) => $lista->contains('id', $tipo->id));
    $this->post(route('inventario.store'), ['tipo_insumo_id' => $tipo->id, 'cantidad' => 2, 'nombre' => 'Nombre falso'])
        ->assertSessionHasNoErrors();
    expect(ItemInventario::sole()->nombre)->toBe('Proyector')->and(ItemInventario::sole()->tipo_insumo_id)->toBe($tipo->id);
});

test('los otros roles no pueden crear insumos del catálogo', function (string $rol) {
    $this->actingAs(User::factory()->create(['role' => $rol]))->post(route('parametros.insumos.store'), [
        'nombre_insumo' => 'Proyector', 'categoria_insumo' => 'equipamiento',
    ])->assertForbidden();
    $this->assertDatabaseMissing('tipos_insumo', ['nombre' => 'Proyector']);
})->with(['presidente', 'secretario', 'tesorero', 'vecino']);

test('el catálogo rechaza nombres vacíos duplicados y categorías desconocidas', function (array $datos, string $campo) {
    $this->actingAs(User::factory()->create(['role' => 'admin']))->post(route('parametros.insumos.store'), $datos)
        ->assertSessionHasErrors($campo);
    $this->assertDatabaseCount('tipos_insumo', 15);
})->with([
    [['nombre_insumo' => ' ', 'categoria_insumo' => 'mobiliario'], 'nombre_insumo'],
    [['nombre_insumo' => 'Sillas', 'categoria_insumo' => 'mobiliario'], 'nombre_insumo'],
    [['nombre_insumo' => 'Proyector', 'categoria_insumo' => 'inventada'], 'categoria_insumo'],
]);

test('un tipo tiene una sola fila de existencias y no admite nombres libres para nuevas altas', function () {
    $this->actingAs(User::factory()->create(['role' => 'presidente']));
    $tipo = TipoInsumo::where('nombre', 'Sillas')->sole();
    $this->post(route('inventario.store'), ['tipo_insumo_id' => $tipo->id, 'cantidad' => 5])->assertSessionHasNoErrors();
    $this->post(route('inventario.store'), ['tipo_insumo_id' => $tipo->id, 'cantidad' => 8])->assertSessionHasErrors('tipo_insumo_id');
    $this->post(route('inventario.store'), ['nombre' => 'Nombre libre', 'cantidad' => 4])->assertSessionHasErrors('tipo_insumo_id');
    expect(ItemInventario::sole()->cantidad)->toBe(5);
    $this->get(route('inventario.index'))->assertOk()
        ->assertViewHas('tiposInsumo', fn ($lista) => ! $lista->contains('id', $tipo->id));
});

function prestamoParaCatalogo(ItemInventario $item, array $cambios = []): Prestamo
{
    return Prestamo::create(array_replace([
        'item_inventario_id' => $item->id, 'cantidad' => 3, 'destinatario_nombre' => 'Destinatario anterior',
        'responsable_nombre' => 'Responsable anterior', 'responsable_telefono' => '912345678',
        'fecha_solicitud' => now()->subDays(2)->toDateString(), 'fecha_prestamo' => now()->subDay()->toDateString(),
        'estado_salida' => 'bueno',
    ], $cambios));
}

test('presidente y secretario ajustan cantidades sin cambiar nombre ni registrador', function (string $rol) {
    $original = User::factory()->create();
    $item = ItemInventario::create(['nombre' => 'Ítem anterior sin catálogo', 'cantidad' => 5, 'registrado_por' => $original->id]);
    $usuario = User::factory()->create(['role' => $rol]);
    $this->actingAs($usuario)->put(route('inventario.update', $item), [
        'cantidad_total' => 8, 'nombre' => 'Nombre falso', 'registrado_por' => $usuario->id,
        'tipo_insumo_id' => TipoInsumo::where('nombre', 'Mesas')->value('id'),
    ])->assertSessionHasNoErrors();
    expect($item->fresh()->cantidad)->toBe(8)->and($item->fresh()->nombre)->toBe('Ítem anterior sin catálogo')
        ->and($item->fresh()->registrado_por)->toBe($original->id)->and($item->fresh()->tipo_insumo_id)->toBeNull();
    $this->assertDatabaseHas('auditorias', ['auditable_type' => ItemInventario::class, 'auditable_id' => $item->id,
        'evento' => 'modificado', 'user_id' => $usuario->id]);
    $this->get(route('inventario.index'))->assertOk()->assertSee('Ítem anterior sin catálogo');
})->with(['presidente', 'secretario']);

test('el total no baja de lo prestado más lo dañado y conserva los préstamos históricos', function () {
    $item = ItemInventario::create(['nombre' => 'Sillas anteriores', 'cantidad' => 5]);
    $activo = prestamoParaCatalogo($item);
    $devuelto = prestamoParaCatalogo($item, ['cantidad' => 2, 'fecha_devolucion' => now()->toDateString(),
        'estado_regreso' => 'danado', 'cantidad_danada' => 1]);
    $this->actingAs(User::factory()->create(['role' => 'presidente']));
    $this->put(route('inventario.update', $item), ['cantidad_total' => 3])->assertSessionHasErrors('cantidad_total');
    expect($item->fresh()->cantidad)->toBe(5)->and($item->fresh()->disponibles())->toBe(1);
    $this->put(route('inventario.update', $item), ['cantidad_total' => 4])->assertSessionHasNoErrors();
    expect($item->fresh()->cantidad)->toBe(4)->and($item->fresh()->disponibles())->toBe(0)
        ->and($activo->fresh()->cantidad)->toBe(3)->and($devuelto->fresh()->cantidad_danada)->toBe(1);
    $this->assertDatabaseCount('prestamos', 2);
});

test('la cantidad total puede ser cero si no hay unidades comprometidas', function () {
    $item = ItemInventario::create(['nombre' => 'Mesas', 'cantidad' => 5]);
    $this->actingAs(User::factory()->create(['role' => 'presidente']))->put(route('inventario.update', $item), ['cantidad_total' => 0])
        ->assertSessionHasNoErrors();
    expect($item->fresh()->disponibles())->toBe(0);
});

test('la edición rechaza cantidades inválidas', function (mixed $cantidad) {
    $item = ItemInventario::create(['nombre' => 'Mesas', 'cantidad' => 5]);
    $this->actingAs(User::factory()->create(['role' => 'presidente']))->put(route('inventario.update', $item), ['cantidad_total' => $cantidad])
        ->assertSessionHasErrors('cantidad_total');
    expect($item->fresh()->cantidad)->toBe(5);
})->with([[-1], ['2.5'], [4294967296], [null]]);

test('administrador tesorero y vecino no editan existencias', function (string $rol) {
    $item = ItemInventario::create(['nombre' => 'Mesas', 'cantidad' => 5]);
    $this->actingAs(User::factory()->create(['role' => $rol]))->put(route('inventario.update', $item), ['cantidad_total' => 0])
        ->assertForbidden();
    expect($item->fresh()->cantidad)->toBe(5);
})->with(['admin', 'tesorero', 'vecino']);
