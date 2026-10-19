<?php

use App\Models\Grupo;
use App\Models\HorarioAtencion;
use App\Models\Ingreso;
use App\Models\ItemInventario;
use App\Models\Prestamo;
use App\Models\Socio;
use App\Models\SolicitudInsumo;
use App\Models\TipoInsumo;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    $this->withoutVite();
    $this->travelTo(Carbon::parse('2026-10-17 10:00:00'));
    $this->directivaInsumos = User::factory()->create(['role' => 'presidente']);
    $this->vecinoInsumos = User::factory()->create(['role' => 'vecino']);
    $this->fichaInsumos = Socio::create([
        'user_id' => $this->vecinoInsumos->id, 'nombre_completo' => 'Solicitante real', 'rut' => '12345678-5',
        'direccion' => 'Calle 123', 'telefono' => '912345678', 'residente_desde' => '2020-01-01', 'estado' => 'activo',
    ]);
    $this->sillasInsumos = ItemInventario::create(['nombre' => 'Sillas', 'cantidad' => 5, 'tipo_insumo_id' => TipoInsumo::where('nombre', 'Sillas')->value('id')]);
    $this->mesasInsumos = ItemInventario::create(['nombre' => 'Mesas', 'cantidad' => 2, 'tipo_insumo_id' => TipoInsumo::where('nombre', 'Mesas')->value('id')]);
    $this->actingAs($this->vecinoInsumos);
});

afterEach(function () { $this->travelBack(); });

function datosSolicitudInsumos(ItemInventario $sillas, ItemInventario $mesas, array $cambios = []): array
{
    return array_replace([
        'telefono_contacto' => '912345678', 'fecha_comprometida' => '2026-10-20',
        'lineas' => [['item_inventario_id' => $sillas->id, 'cantidad' => 3], ['item_inventario_id' => $mesas->id, 'cantidad' => 2]],
    ], $cambios);
}

function aprobarSolicitudInsumos($caso, SolicitudInsumo $solicitud, User $dirigente): void
{
    $caso->actingAs($dirigente)->post(route('insumos.aprobar', $solicitud), [
        'responsable_id' => $dirigente->id, 'monto' => 7000, 'garantia' => 12000, 'fecha_comprometida' => '2026-10-20',
    ])->assertSessionHasNoErrors();
}

function entregarSolicitudInsumos($caso, SolicitudInsumo $solicitud): void
{
    $caso->post(route('insumos.entregar', $solicitud), [
        'fecha_entrega' => now()->toDateString(), 'condiciones' => $solicitud->lineas->mapWithKeys(fn ($l) => [$l->id => 'bueno'])->all(),
    ])->assertSessionHasNoErrors();
}

test('socio solicita varios insumos sin reservar stock ni suplantar autor importes o identidad', function () {
    $this->post(route('insumos.store'), datosSolicitudInsumos($this->sillasInsumos, $this->mesasInsumos, [
        'solicitante' => 'usuario:'.$this->directivaInsumos->id, 'solicitante_nombre' => 'Falso',
        'estado' => 'aprobado', 'monto' => 1, 'garantia' => 1, 'registrado_por' => $this->directivaInsumos->id,
    ]))->assertSessionHasNoErrors();
    $solicitud = SolicitudInsumo::sole();
    expect($solicitud->estado)->toBe('pendiente')->and($solicitud->solicitante_id)->toBe($this->vecinoInsumos->id)
        ->and($solicitud->solicitante_nombre)->toBe('Solicitante real')->and($solicitud->registrado_por)->toBe($this->vecinoInsumos->id)
        ->and($solicitud->monto)->toBeNull()->and($solicitud->garantia)->toBeNull()->and($solicitud->lineas)->toHaveCount(2)
        ->and($this->sillasInsumos->fresh()->disponibles())->toBe(5)->and($this->mesasInsumos->fresh()->disponibles())->toBe(2);
    $this->get(route('insumos.show', $solicitud))->assertOk();
    $this->assertDatabaseCount('prestamos', 0);
});

test('dirigentes solicitan para sí con ficha activa y conservan sus permisos de cargo', function (string $rol) {
    $usuario = User::factory()->create(['role' => $rol]);
    $ficha = Socio::create([
        'user_id' => $usuario->id, 'nombre_completo' => $usuario->name, 'rut' => '11111111-1',
        'direccion' => 'Calle 456', 'telefono' => '912345678', 'residente_desde' => '2020-01-01', 'estado' => 'activo',
    ]);
    $this->actingAs($usuario)->get(route('insumos.index'))->assertOk();
    $this->post(route('insumos.store'), datosSolicitudInsumos($this->sillasInsumos, $this->mesasInsumos, [
        'solicitante' => 'socio:'.$ficha->id,
    ]))->assertSessionHasNoErrors();
    expect(SolicitudInsumo::sole()->solicitante_id)->toBe($usuario->id)->and(SolicitudInsumo::sole()->canal)->toBe('en_linea');
    $this->assertDatabaseCount('socios', 2);
})->with(['presidente', 'secretario', 'tesorero']);

test('vecino sin ficha activa no solicita y no obtiene acceso por representar un grupo', function (string $estado) {
    $this->fichaInsumos->update(['estado' => $estado]);
    Grupo::create(['nombre' => 'Grupo', 'representante_id' => $this->fichaInsumos->id]);
    $this->get(route('insumos.index'))->assertForbidden();
    $this->post(route('insumos.store'), datosSolicitudInsumos($this->sillasInsumos, $this->mesasInsumos))->assertForbidden();
})->with(['inactivo', 'pendiente', 'por_firmar']);

test('ventanilla exige representante vigente y conserva sus datos sin crear ficha ni cuenta', function () {
    $grupo = Grupo::create(['nombre' => 'Grupo externo', 'representante_nombre' => 'Representante externo', 'representante_rut' => '11111111-1']);
    $usuarios = User::count();
    $this->actingAs($this->directivaInsumos)->post(route('insumos.store'), datosSolicitudInsumos($this->sillasInsumos, $this->mesasInsumos, [
        'solicitante' => 'grupo:'.$grupo->id,
    ]))->assertSessionHasNoErrors();
    $solicitud = SolicitudInsumo::sole();
    expect($solicitud->solicitante_nombre)->toBe('Representante externo')->and($solicitud->grupo_id)->toBe($grupo->id)
        ->and($solicitud->solicitante_id)->toBeNull()->and($solicitud->socio_id)->toBeNull()->and(User::count())->toBe($usuarios);
    $grupo->update(['representante_nombre' => null]);
    $this->post(route('insumos.aprobar', $solicitud), [
        'responsable_id' => $this->directivaInsumos->id, 'monto' => 0, 'garantia' => 0, 'fecha_comprometida' => '2026-10-20',
    ])->assertSessionHasErrors('operacion');
    expect($solicitud->fresh()->estado)->toBe('pendiente');
});

test('representante con varias agrupaciones debe elegir una propia y no puede ocultarla', function () {
    $grupos = collect(['Grupo uno', 'Grupo dos'])->map(fn ($nombre) => Grupo::create(['nombre' => $nombre, 'representante_id' => $this->fichaInsumos->id]));
    $datos = datosSolicitudInsumos($this->sillasInsumos, $this->mesasInsumos);
    $this->post(route('insumos.store'), $datos)->assertSessionHasErrors('grupo_id');
    $this->post(route('insumos.store'), $datos + ['grupo_id' => 999999])->assertSessionHasErrors('grupo_id');
    $this->post(route('insumos.store'), $datos + ['grupo_id' => $grupos->first()->id])->assertSessionHasNoErrors();
    expect(SolicitudInsumo::sole()->grupo_nombre)->toBe('Grupo uno');
});

test('validación impide cantidades inválidas ítems repetidos y existencias fuera del catálogo', function (string $caso) {
    $datos = datosSolicitudInsumos($this->sillasInsumos, $this->mesasInsumos);
    if ($caso === 'cantidad') $datos['lineas'][0]['cantidad'] = 0;
    if ($caso === 'repetido') $datos['lineas'][1]['item_inventario_id'] = $this->sillasInsumos->id;
    if ($caso === 'histórico') $datos['lineas'][0]['item_inventario_id'] = ItemInventario::create(['nombre' => 'Histórico sin tipo', 'cantidad' => 5])->id;
    $this->post(route('insumos.store'), $datos)->assertSessionHasErrors();
    $this->assertDatabaseCount('solicitudes_insumos', 0);
    expect($this->sillasInsumos->fresh()->disponibles())->toBe(5);
})->with(['cantidad', 'repetido', 'histórico']);

test('aprobar reserva todos los insumos y rechaza una segunda solicitud sin reservar parcialmente', function (string $rol) {
    $datos = datosSolicitudInsumos($this->sillasInsumos, $this->mesasInsumos);
    $this->post(route('insumos.store'), $datos)->assertSessionHasNoErrors();
    $primera = SolicitudInsumo::sole();
    $datos['lineas'][0]['cantidad'] = 1; // Esta línea sí cabe; la falta de mesas debe rechazar el conjunto.
    $this->post(route('insumos.store'), $datos)->assertSessionHasNoErrors();
    $segunda = SolicitudInsumo::latest('id')->first();
    $dirigente = User::factory()->create(['role' => $rol]);
    aprobarSolicitudInsumos($this, $primera, $dirigente);
    expect($primera->fresh()->monto)->toBe(7000)->and($primera->fresh()->garantia)->toBe(12000)
        ->and($this->sillasInsumos->fresh()->disponibles())->toBe(2)->and($this->mesasInsumos->fresh()->disponibles())->toBe(0);
    $this->post(route('insumos.aprobar', $segunda), [
        'responsable_id' => $dirigente->id, 'monto' => 0, 'garantia' => 0, 'fecha_comprometida' => '2026-10-20',
    ])->assertSessionHasErrors('operacion');
    expect($segunda->fresh()->estado)->toBe('pendiente')->and($this->sillasInsumos->fresh()->disponibles())->toBe(2);
    $this->put(route('inventario.update', $this->sillasInsumos), ['cantidad_total' => 2])->assertSessionHasErrors('cantidad_total');
    $this->assertDatabaseCount('prestamos', 0);
    $this->assertDatabaseCount('ingresos', 0);
})->with(['presidente', 'secretario']);

test('solo directiva gestiona y solo el titular consulta sus solicitudes', function () {
    $this->post(route('insumos.store'), datosSolicitudInsumos($this->sillasInsumos, $this->mesasInsumos))->assertSessionHasNoErrors();
    $solicitud = SolicitudInsumo::sole();
    foreach (['aprobar', 'entregar', 'devolver'] as $accion) $this->post(route('insumos.'.$accion, $solicitud))->assertForbidden();
    $tesorero = User::factory()->create(['role' => 'tesorero']);
    $this->actingAs($tesorero)->get(route('insumos.show', $solicitud))->assertForbidden();
    $this->get(route('inventario.index'))->assertForbidden();
    $this->get(route('insumos.index'))->assertForbidden();
    $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('insumos.show', $solicitud))->assertForbidden();
});

test('entrega no descuenta dos veces y devolución completa libera solo unidades utilizables', function () {
    $this->post(route('insumos.store'), datosSolicitudInsumos($this->sillasInsumos, $this->mesasInsumos))->assertSessionHasNoErrors();
    $solicitud = SolicitudInsumo::sole();
    aprobarSolicitudInsumos($this, $solicitud, $this->directivaInsumos);
    entregarSolicitudInsumos($this, $solicitud);
    expect($this->sillasInsumos->fresh()->disponibles())->toBe(2)->and($this->mesasInsumos->fresh()->disponibles())->toBe(0);
    $this->assertDatabaseCount('prestamos', 2);
    $this->get(route('insumos.index'))->assertViewHas('solicitudes', fn ($lista) => $lista->count() === 1);
    $this->get(route('inventario.prestamos.index'))->assertOk()->assertViewHas('solicitudes', fn ($lista) => $lista->count() === 1);
    $this->post(route('inventario.prestamos.devolver', Prestamo::first()), [])->assertStatus(422);
    $lineas = $solicitud->lineas->mapWithKeys(fn ($l) => [$l->id => ['estado_regreso' => 'bueno', 'cantidad_danada' => 0]])->all();
    $this->post(route('insumos.devolver', $solicitud), ['fecha_devolucion' => '2026-10-17', 'devoluciones' => []])->assertSessionHasErrors();
    expect($this->sillasInsumos->fresh()->disponibles())->toBe(2);
    $sillas = $solicitud->lineas->firstWhere('item_inventario_id', $this->sillasInsumos->id);
    $lineas[$sillas->id] = ['estado_regreso' => 'danado', 'cantidad_danada' => 1];
    $datos = ['fecha_devolucion' => '2026-10-17', 'devoluciones' => $lineas];
    $this->post(route('insumos.devolver', $solicitud), $datos)->assertSessionHasNoErrors();
    expect($solicitud->fresh()->estado)->toBe('devuelto')->and($this->sillasInsumos->fresh()->disponibles())->toBe(4)
        ->and($this->mesasInsumos->fresh()->disponibles())->toBe(2)->and(Ingreso::saldoCaja())->toBe(0);
    $this->post(route('insumos.devolver', $solicitud), $datos)->assertSessionHasErrors('operacion');
    $this->get(route('insumos.show', $solicitud))->assertOk();
});

test('horarios y puntos rojos muestran pendientes y atrasos solo a la directiva', function () {
    HorarioAtencion::create(['dia_semana' => 1, 'hora_inicio' => '10:00', 'hora_fin' => '12:00']);
    $this->get(route('insumos.index'))->assertOk()->assertSee('Lunes: 10:00 a 12:00');
    $this->post(route('insumos.store'), datosSolicitudInsumos($this->sillasInsumos, $this->mesasInsumos))->assertSessionHasNoErrors();
    $solicitud = SolicitudInsumo::sole();
    $this->actingAs($this->directivaInsumos)->get(route('dashboard'))->assertSee('data-pendiente="insumos"', false);
    aprobarSolicitudInsumos($this, $solicitud, $this->directivaInsumos);
    entregarSolicitudInsumos($this, $solicitud);
    $this->get(route('dashboard'))->assertDontSee('data-pendiente="prestamos"', false);
    $this->travelTo(Carbon::parse('2026-10-21 10:00:00'));
    expect($solicitud->fresh()->atrasado())->toBeTrue();
    $this->get(route('dashboard'))->assertSee('data-pendiente="prestamos"', false);
    $this->actingAs($this->vecinoInsumos)->get(route('insumos.show', $solicitud))->assertOk()->assertSee('Atrasado')
        ->assertDontSee('data-pendiente="prestamos"', false);
});

test('la aprobación no acepta montos negativos', function (array $cambios, string $campo) {
    $this->post(route('insumos.store'), datosSolicitudInsumos($this->sillasInsumos, $this->mesasInsumos))->assertSessionHasNoErrors();
    $solicitud = SolicitudInsumo::sole();
    $datos = array_replace([
        'responsable_id' => $this->vecinoInsumos->id, 'monto' => 0, 'garantia' => 0, 'fecha_comprometida' => '2026-10-20',
    ], $cambios);
    if ($campo !== 'responsable_id') $datos['responsable_id'] = $this->directivaInsumos->id;
    $this->actingAs($this->directivaInsumos)->post(route('insumos.aprobar', $solicitud), $datos)->assertSessionHasErrors($campo);
    expect($solicitud->fresh()->estado)->toBe('pendiente')->and($this->sillasInsumos->fresh()->disponibles())->toBe(5);
})->with([[['monto' => -1], 'monto'], [['garantia' => -1], 'garantia']]);

test('entregas duplicadas y devoluciones inválidas no modifican unidades ni cierran parcialmente', function (string $caso) {
    $this->post(route('insumos.store'), datosSolicitudInsumos($this->sillasInsumos, $this->mesasInsumos))->assertSessionHasNoErrors();
    $solicitud = SolicitudInsumo::sole();
    aprobarSolicitudInsumos($this, $solicitud, $this->directivaInsumos);
    entregarSolicitudInsumos($this, $solicitud);
    $this->post(route('insumos.entregar', $solicitud), [])->assertSessionHasErrors('operacion');
    $devoluciones = $solicitud->lineas->mapWithKeys(fn ($l) => [$l->id => ['estado_regreso' => 'bueno', 'cantidad_danada' => 0]])->all();
    $ultima = $solicitud->lineas->last();
    $datos = ['fecha_devolucion' => '2026-10-17', 'devoluciones' => $devoluciones];
    if ($caso === 'daños excesivos') $datos['devoluciones'][$ultima->id] = ['estado_regreso' => 'danado', 'cantidad_danada' => $ultima->cantidad + 1];
    if ($caso === 'daños sin estado') $datos['devoluciones'][$ultima->id]['cantidad_danada'] = 1;
    if ($caso === 'futura') $datos['fecha_devolucion'] = '2026-10-18';
    if ($caso === 'anterior') $datos['fecha_devolucion'] = '2026-10-16';
    $this->post(route('insumos.devolver', $solicitud), $datos)->assertSessionHasErrors();
    expect($solicitud->fresh()->estado)->toBe('entregado')->and(Prestamo::whereNotNull('fecha_devolucion')->count())->toBe(0)
        ->and(Prestamo::count())->toBe(2)->and($this->sillasInsumos->fresh()->disponibles())->toBe(2);
})->with(['daños excesivos', 'daños sin estado', 'futura', 'anterior']);
