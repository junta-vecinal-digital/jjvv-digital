<?php

use App\Models\Garantia;
use App\Models\Grupo;
use App\Models\Ingreso;
use App\Models\ItemInventario;
use App\Models\Socio;
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
});

afterEach(function () { $this->travelBack(); });

test('ciclo completo de préstamo mantiene consistentes existencias garantía ingresos y reporte del grupo', function () {
    $presidente = User::factory()->create(['role' => 'presidente']);
    $secretario = User::factory()->create(['role' => 'secretario']);
    $tesorero = User::factory()->create(['role' => 'tesorero']);
    $vecino = User::factory()->create(['role' => 'vecino']);
    $socio = Socio::create([
        'user_id' => $vecino->id, 'nombre_completo' => 'Solicitante de cierre', 'rut' => '12345678-5',
        'direccion' => 'Pasaje de prueba 123', 'telefono' => '912345678', 'residente_desde' => '2020-01-01', 'estado' => 'activo',
    ]);
    $grupo = Grupo::create(['nombre' => 'Taller de cierre', 'representante_id' => $socio->id]);
    $item = ItemInventario::create([
        'nombre' => 'Sillas', 'cantidad' => 5, 'tipo_insumo_id' => TipoInsumo::where('nombre', 'Sillas')->value('id'),
    ]);

    $this->actingAs($vecino)->post(route('insumos.store'), [
        'grupo_id' => $grupo->id, 'telefono_contacto' => $socio->telefono, 'fecha_comprometida' => '2026-10-20',
        'lineas' => [['item_inventario_id' => $item->id, 'cantidad' => 3]],
    ])->assertSessionHasNoErrors();
    $solicitud = SolicitudInsumo::sole();
    expect($item->fresh()->disponibles())->toBe(5)->and($solicitud->grupo_id)->toBe($grupo->id);
    $this->get(route('finanzas.insumos.show', $solicitud))->assertForbidden();

    $this->actingAs($secretario)->post(route('insumos.aprobar', $solicitud), [
        'responsable_id' => $tesorero->id, 'monto' => 6000, 'garantia' => 10000, 'fecha_comprometida' => '2026-10-20',
    ])->assertSessionHasNoErrors();
    expect($item->fresh()->disponibles())->toBe(2);
    $this->get(route('finanzas.insumos.show', $solicitud))->assertForbidden();

    $this->actingAs($vecino)->post(route('insumos.pago', $solicitud), [
        'fecha_pago' => '2026-10-17', 'medio_pago' => 'transferencia', 'numero_transaccion' => 'TRX-CIERRE-INTEGRACION',
        'comprobante' => UploadedFile::fake()->create('pago.pdf', 10, 'application/pdf'),
    ])->assertSessionHasNoErrors();
    $this->actingAs($secretario)->post(route('insumos.validar-pago', $solicitud), ['pago_verificado' => 1])->assertSessionHasNoErrors();
    $this->assertDatabaseCount('ingresos', 0);
    $this->post(route('finanzas.insumos.registrar', $solicitud))->assertForbidden();

    $linea = $solicitud->lineas()->sole();
    $this->actingAs($secretario)->post(route('insumos.entregar', $solicitud), [
        'fecha_entrega' => '2026-10-17', 'condiciones' => [$linea->id => 'bueno'],
    ])->assertSessionHasNoErrors();
    expect($item->fresh()->disponibles())->toBe(2)->and(Ingreso::saldoCaja())->toBe(0);
    // La entrega no espera el paso contable; el tesorero registra después el ingreso.
    $this->actingAs($tesorero)->post(route('finanzas.insumos.registrar', $solicitud))->assertSessionHasNoErrors();
    $garantia = Garantia::sole();
    expect(Ingreso::saldoCaja())->toBe(6000)->and($garantia->monto)->toBe(10000);
    $this->get(route('insumos.show', $solicitud))->assertForbidden();
    $this->actingAs($secretario)->post(route('insumos.devolver', $solicitud), [
        'fecha_devolucion' => '2026-10-17',
        'devoluciones' => [$linea->id => ['estado_regreso' => 'danado', 'cantidad_danada' => 1]],
    ])->assertSessionHasNoErrors();
    expect($item->fresh()->disponibles())->toBe(4)->and($garantia->fresh()->estado)->toBe('por_devolver');

    $this->actingAs($tesorero)->post(route('finanzas.garantias.retener', $garantia), [
        'fecha_resolucion' => '2026-10-17', 'monto_retenido' => 2500, 'motivo_retencion' => 'Daño en una silla',
    ])->assertSessionHasNoErrors();
    $garantia->refresh();
    expect($garantia->monto_devuelto)->toBe(7500)->and($garantia->monto_retenido)->toBe(2500)->and(Ingreso::saldoCaja())->toBe(8500);
    $resolucion = $garantia->getAttributes();
    $filtros = ['desde' => '2026-10-17', 'hasta' => '2026-10-17', 'grupo_id' => $grupo->id];
    $this->get(route('reportes.financieros', $filtros))->assertOk()
        ->assertViewHas('totales', ['ingresos' => 8500, 'egresos' => 0, 'devoluciones' => 0, 'neto' => 8500])
        ->assertViewHas('movimientos', fn ($filas) => $filas->count() === 2 && $filas->every(fn ($f) => $f['grupo_id'] === $grupo->id));

    $cobro = $solicitud->ingresos()->sole();
    $this->actingAs($presidente)->post(route('ingresos.anular', $cobro), ['motivo_anulacion' => 'Corregir registro del cobro'])->assertSessionHasNoErrors();
    $this->get(route('reportes.financieros', $filtros))->assertOk()
        ->assertViewHas('totales', ['ingresos' => 2500, 'egresos' => 0, 'devoluciones' => 0, 'neto' => 2500]);
    $this->actingAs($tesorero)->post(route('finanzas.insumos.reingresar', $solicitud))->assertSessionHasNoErrors();
    $this->get(route('reportes.financieros', $filtros))->assertOk()
        ->assertViewHas('totales', ['ingresos' => 8500, 'egresos' => 0, 'devoluciones' => 0, 'neto' => 8500]);
    expect($garantia->fresh()->getAttributes())->toBe($resolucion)->and($item->fresh()->disponibles())->toBe(4)
        ->and($solicitud->ingresos()->vigentes()->count())->toBe(1)->and($solicitud->fresh()->estado)->toBe('devuelto');
    $this->assertDatabaseCount('garantias', 1);
    $this->assertDatabaseCount('ingresos', 3);
    $this->assertDatabaseCount('egresos', 0);
    $this->assertDatabaseCount('devoluciones', 0);
});
