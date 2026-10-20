<?php

use App\Models\Garantia;
use App\Models\Ingreso;
use App\Models\ItemInventario;
use App\Models\Reserva;
use App\Models\Socio;
use App\Models\SolicitudInsumo;
use App\Models\TipoInsumo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->withoutVite();
    Storage::fake('local');
    $this->presidentePendientes = User::factory()->create(['role' => 'presidente']);
    $this->secretarioPendientes = User::factory()->create(['role' => 'secretario']);
    $this->tesoreroPendientes = User::factory()->create(['role' => 'tesorero']);
    $this->vecinoPendientes = User::factory()->create(['role' => 'vecino']);
    $this->socioPendientes = Socio::create([
        'user_id' => $this->vecinoPendientes->id, 'nombre_completo' => 'Solicitante', 'rut' => '12345678-5',
        'direccion' => 'Calle 123', 'telefono' => '912345678', 'residente_desde' => '2020-01-01', 'estado' => 'activo',
    ]);
});

test('reserva con garantía exige comprobante y verificación y finanzas no duplica importes', function (int $aporte) {
    $this->actingAs($this->vecinoPendientes)->post(route('arriendos.store'), [
        'actividad_reserva' => 'particular',
        'fecha' => today()->addDays(5)->toDateString(), 'hora_inicio' => '10:00', 'hora_fin' => '11:00',
    ])->assertSessionHasNoErrors();
    $reserva = Reserva::sole();
    $this->actingAs($this->presidentePendientes)->post(route('arriendos.aporte', $reserva), ['valor_arriendo' => $aporte, 'garantia_reserva' => 12000])->assertSessionHasNoErrors();
    $this->post(route('arriendos.confirmar', $reserva), ['pago_verificado' => 1, 'garantia_verificada' => 1])->assertSessionHasErrors('arriendo');
    $this->actingAs($this->vecinoPendientes)->post(route('arriendos.comprobante.store', $reserva), [
        'comprobante' => UploadedFile::fake()->create('total.pdf', 10, 'application/pdf'),
    ])->assertSessionHasNoErrors();
    $this->actingAs($this->presidentePendientes)->post(route('arriendos.confirmar', $reserva), ['pago_verificado' => 1])->assertSessionHasErrors('garantia_verificada');
    $this->post(route('arriendos.confirmar', $reserva), ['pago_verificado' => 1, 'garantia_verificada' => 1])->assertSessionHasNoErrors();
    expect(Garantia::sole()->monto)->toBe(12000);
    $this->assertDatabaseCount('ingresos', 0);
    $this->actingAs($this->tesoreroPendientes)->get(route('ingresos.index'))->assertOk()
        ->assertViewHas('reservas', fn ($lista) => $lista->contains('id', $reserva->id));
    $datos = ['fecha_pago' => today()->toDateString(), 'medio_pago' => 'transferencia'];
    $this->from(route('ingresos.index'))->post(route('finanzas.arriendos.registrar', $reserva), $datos)->assertSessionHasNoErrors();
    expect(Ingreso::saldoCaja())->toBe($aporte)->and(Garantia::sole()->registrada_en)->not->toBeNull();
    $this->assertDatabaseCount('ingresos', $aporte > 0 ? 1 : 0);
    $this->post(route('finanzas.arriendos.registrar', $reserva), $datos)->assertSessionHasErrors('finanzas');
    $this->get(route('ingresos.index'))->assertOk()->assertViewHas('reservas', fn ($lista) => $lista->isEmpty());
    $this->assertDatabaseCount('garantias', 1);
    if ($aporte > 0) {
        $ingreso = Ingreso::sole();
        $ingreso->anulado_en = now();
        $ingreso->save();
        $this->get(route('ingresos.index'))->assertOk()->assertViewHas('reservas', fn ($lista) => $lista->contains('id', $reserva->id));
        $this->post(route('finanzas.arriendos.registrar', $reserva), $datos)->assertSessionHasNoErrors();
        expect(Ingreso::saldoCaja())->toBe($aporte);
        $this->assertDatabaseCount('garantias', 1);
    }
})->with([0, 7000]);

test('responsable automático conserva registrador de ventanilla y usa aprobador en línea sin aceptar tesorero', function (bool $ventanilla) {
    $item = ItemInventario::create(['nombre' => 'Sillas', 'cantidad' => 5, 'tipo_insumo_id' => TipoInsumo::where('nombre', 'Sillas')->value('id')]);
    $datos = [
        'solicitante' => 'socio:'.$this->socioPendientes->id, 'telefono_contacto' => '912345678',
        'fecha_comprometida' => today()->addWeek()->toDateString(),
        'lineas' => [['item_inventario_id' => $item->id, 'cantidad' => 2]],
        'responsable_id' => $this->tesoreroPendientes->id,
    ];
    $this->actingAs($ventanilla ? $this->presidentePendientes : $this->vecinoPendientes)->post(route('insumos.store'), $datos)->assertSessionHasNoErrors();
    $solicitud = SolicitudInsumo::sole();
    $this->actingAs($this->secretarioPendientes)->post(route('insumos.aprobar', $solicitud), [
        'responsable_id' => $this->tesoreroPendientes->id, 'monto' => 5000, 'garantia' => 10000,
        'fecha_comprometida' => today()->addWeek()->toDateString(),
    ])->assertSessionHasNoErrors();
    $solicitud->refresh();
    expect($solicitud->responsable_id)->toBe($ventanilla ? $this->presidentePendientes->id : $this->secretarioPendientes->id)
        ->and($solicitud->responsable_cargo)->toBe($ventanilla ? 'presidente' : 'secretario');
    $this->get(route('insumos.show', $solicitud))->assertOk()->assertDontSee('name="responsable_id"', false);
    $this->actingAs($this->tesoreroPendientes)->get(route('ingresos.index'))->assertOk()->assertViewHas('prestamos', fn ($lista) => $lista->contains('id', $solicitud->id));
    $pago = ['fecha_pago' => today()->toDateString(), 'medio_pago' => 'transferencia', 'numero_transaccion' => 'TRX-123'];
    $this->post(route('finanzas.insumos.registrar', $solicitud), $pago)->assertSessionHasNoErrors();
    $this->post(route('finanzas.insumos.registrar', $solicitud), $pago)->assertSessionHasErrors('finanzas');
    expect(Ingreso::saldoCaja())->toBe(5000);
    $this->assertDatabaseCount('garantias', 1);
    $this->get(route('ingresos.index'))->assertOk()->assertViewHas('prestamos', fn ($lista) => $lista->isEmpty());
    $ingreso = Ingreso::sole();
    $ingreso->anulado_en = now();
    $ingreso->save();
    $this->get(route('ingresos.index'))->assertOk()->assertViewHas('prestamos', fn ($lista) => $lista->contains('id', $solicitud->id));
    $this->post(route('finanzas.insumos.reingresar', $solicitud))->assertSessionHasNoErrors();
    $this->assertDatabaseCount('garantias', 1);
    expect(Ingreso::saldoCaja())->toBe(5000);
})->with([true, false]);

test('solo presidente y tesorero acceden a pendientes financieros', function (string $rol) {
    $usuario = User::factory()->create(['role' => $rol]);
    $this->actingAs($usuario)->get(route('ingresos.index'))->assertForbidden();
    $this->get(route('finanzas.pendientes.index'))->assertForbidden();
    $this->get(route('finanzas.insumos.index'))->assertForbidden();
})->with(['secretario', 'vecino', 'admin']);

test('las entradas financieras anteriores redirigen a las vistas agrupadas sin ampliar permisos', function (string $rol) {
    $this->actingAs(User::factory()->create(['role' => $rol]));
    $this->get(route('finanzas.pendientes.index'))->assertRedirect(route('ingresos.index'));
    $this->get(route('finanzas.insumos.index'))->assertRedirect(route('finanzas.arriendos.index'));
    $this->get(route('ingresos.index'))->assertOk()->assertSee('Ingresos por aprobar');
    $this->get(route('finanzas.arriendos.index'))->assertOk()->assertSee('Registro garantías');
})->with(['presidente', 'tesorero']);

test('actividades sin aporte admiten garantía y las operaciones de total cero no aparecen en pendientes', function () {
    $this->actingAs($this->presidentePendientes)->post(route('arriendos.store'), [
        'actividad_reserva' => 'comunitaria', 'valor_arriendo' => 0, 'garantia_reserva' => 8000,
        'fecha' => today()->addDays(5)->toDateString(), 'hora_inicio' => '10:00', 'hora_fin' => '11:00',
    ])->assertSessionHasNoErrors();
    expect(Reserva::sole()->estado)->toBe('pendiente')->and(Reserva::sole()->valor_garantia)->toBe(8000);
    $this->get(route('ingresos.index'))->assertOk()->assertViewHas('reservas', fn ($lista) => $lista->isEmpty());
    Reserva::create([
        'tipo' => 'arriendo', 'estado' => 'confirmada', 'aporte_variable' => true,
        'inicio' => today()->addDays(6)->setTime(10, 0), 'fin' => today()->addDays(6)->setTime(11, 0),
        'motivo' => 'Reserva', 'valor_arriendo' => 0, 'valor_garantia' => 0, 'validado_en' => now(),
    ]);
    SolicitudInsumo::create([
        'estado' => 'aprobado', 'solicitante_nombre' => 'Vecino', 'telefono_contacto' => '912345678',
        'canal' => 'ventanilla', 'fecha_comprometida' => today()->addWeek(), 'monto' => 0, 'garantia' => 0,
    ]);
    $this->get(route('ingresos.index'))->assertOk()
        ->assertViewHas('reservas', fn ($lista) => $lista->isEmpty())
        ->assertViewHas('prestamos', fn ($lista) => $lista->isEmpty());
});
