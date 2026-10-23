<?php

use App\Models\Auditoria;
use App\Models\Devolucion;
use App\Models\Egreso;
use App\Models\Ingreso;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

beforeEach(function () {
    Storage::fake('local');
    $this->withoutVite();
    $this->travelTo(Carbon::parse('2026-10-17 10:00:00'));
    $this->autorCierre = User::factory()->create(['role' => 'presidente']);
    $this->actingAs($this->autorCierre);
});

afterEach(function () {
    $this->travelBack();
});

function ingresoCierreFinanciero(array $cambios = []): Ingreso
{
    return Ingreso::create(array_replace([
        'concepto' => 'cuota_socios', 'tipo_cuota' => 'ordinaria', 'monto' => 5000,
        'fecha_pago' => now()->toDateString(), 'medio_pago' => 'efectivo',
        'talonario' => 'T-CIERRE', 'registrado_por' => auth()->id(),
    ], $cambios));
}

test('devolver y anular en ambos órdenes afecta una sola vez al saldo sin cambiar la operación ganadora', function (
    string $rol, string $concepto, string $tipoCuota, string $primera
) {
    ingresoCierreFinanciero(['concepto' => 'otros', 'tipo_cuota' => null, 'monto' => 10000]);
    $cuota = ingresoCierreFinanciero(['concepto' => $concepto, 'tipo_cuota' => $tipoCuota]);
    Egreso::create([
        'concepto' => 'mantencion', 'fecha_pago' => now()->toDateString(), 'monto' => 2000,
        'numero_transaccion' => 'TRX-CIERRE', 'pagado_por' => 'Responsable original',
        'comprobante_path' => 'comprobantes/egresos/historico.pdf', 'registrado_por' => $this->autorCierre->id,
    ]);
    expect(Ingreso::saldoCaja())->toBe(13000);
    $operador = User::factory()->create(['role' => $rol]);
    $otroOperador = User::factory()->create(['role' => $rol === 'presidente' ? 'tesorero' : 'presidente']);
    $datos = [
        'motivo' => 'Devolución original', 'motivo_anulacion' => 'Anulación original',
        'monto' => 1, 'registrado_por' => $otroOperador->id, 'anulado_por' => $otroOperador->id,
    ];
    $auditoriasAntes = Auditoria::count();
    $this->actingAs($operador)->post(route('ingresos.'.$primera, $cuota), $datos)->assertSessionHasNoErrors();
    expect(Ingreso::saldoCaja())->toBe(8000)->and(Auditoria::count())->toBe($auditoriasAntes + 1);
    $cuota->refresh();
    expect($cuota->monto)->toBe(5000)->and($cuota->registrado_por)->toBe($this->autorCierre->id);
    if ($primera === 'devolver') {
        $devolucion = Devolucion::sole();
        expect($cuota->estaAnulado())->toBeFalse()->and($devolucion->monto)->toBe(5000)
            ->and($devolucion->registrado_por)->toBe($operador->id)->and($devolucion->motivo)->toBe('Devolución original');
    } else {
        expect($cuota->estaAnulado())->toBeTrue()->and($cuota->anulado_por)->toBe($operador->id)
            ->and($cuota->motivo_anulacion)->toBe('Anulación original');
        $this->assertDatabaseCount('devoluciones', 0);
    }
    $estadoCuota = $cuota->getAttributes();
    $estadoDevoluciones = Devolucion::get()->map->getAttributes()->all();
    $estadoAuditorias = Auditoria::orderBy('id')->get()->map->getAttributes()->all();
    $this->travel(1)->hours();
    $this->actingAs($otroOperador);
    foreach (['devolver' => 'devolucion', 'anular' => 'ingreso'] as $accion => $campo) {
        $this->post(route('ingresos.'.$accion, $cuota), [
            'motivo' => 'Intento posterior', 'motivo_anulacion' => 'Intento posterior',
        ])->assertSessionHasErrors($campo);
    }
    expect($cuota->fresh()->getAttributes())->toBe($estadoCuota)
        ->and(Devolucion::get()->map->getAttributes()->all())->toBe($estadoDevoluciones)
        ->and(Auditoria::orderBy('id')->get()->map->getAttributes()->all())->toBe($estadoAuditorias)
        ->and(Ingreso::saldoCaja())->toBe(8000);
    $this->assertDatabaseCount('ingresos', 2);
    $this->assertDatabaseCount('egresos', 1);
})->with(['presidente', 'tesorero'])->with(['cuota_socios', 'aporte_grupos'])
    ->with(['ordinaria', 'extraordinaria'])->with(['devolver', 'anular']);

test('los conceptos que no son cuotas ni aportes de grupos no admiten devolución', function (string $concepto) {
    $ingreso = ingresoCierreFinanciero(['concepto' => $concepto, 'tipo_cuota' => null]);
    $antes = Auditoria::count();
    $this->post(route('ingresos.devolver', $ingreso), ['motivo' => 'Intento de devolución'])->assertSessionHasErrors('devolucion');
    expect(Ingreso::saldoCaja())->toBe(5000)->and(Auditoria::count())->toBe($antes);
    $this->assertDatabaseCount('devoluciones', 0);
})->with(['otros', 'certificados', 'arriendo', 'aporte_reserva']);

test('validación rechazada no deja modificaciones ni auditorías financieras', function (string $accion, string $campo) {
    $cuota = ingresoCierreFinanciero();
    $cuota->refresh();
    $estado = $cuota->getAttributes();
    $auditorias = Auditoria::count();
    $this->post(route('ingresos.'.$accion, $cuota), [$campo => ''])->assertSessionHasErrors($campo);
    expect($cuota->fresh()->getAttributes())->toBe($estado)->and(Auditoria::count())->toBe($auditorias)
        ->and(Ingreso::saldoCaja())->toBe(5000);
    $this->assertDatabaseCount('devoluciones', 0);
})->with([['devolver', 'motivo'], ['anular', 'motivo_anulacion']]);

test('cuotas y aportes se registran clasificados con autor real e ignoran vínculos financieros enviados', function (string $concepto, string $tipo) {
    $this->post(route('ingresos.store'), [
        'concepto' => $concepto, 'tipo_cuota' => $tipo, 'monto' => 5000,
        'fecha_pago' => now()->toDateString(), 'medio_pago' => 'transferencia', 'numero_transaccion' => 'TRX-CUOTA',
        'comprobante' => UploadedFile::fake()->create('pago.pdf', 10, 'application/pdf'),
        'registrado_por' => 999999, 'reserva_id' => 999999, 'garantia_id' => 999999, 'solicitud_insumo_id' => 999999,
    ])->assertSessionHasNoErrors();
    $ingreso = Ingreso::sole();
    expect($ingreso->concepto)->toBe($concepto)->and($ingreso->tipo_cuota)->toBe($tipo)
        ->and($ingreso->registrado_por)->toBe($this->autorCierre->id)
        ->and($ingreso->reserva_id)->toBeNull()->and($ingreso->garantia_id)->toBeNull()->and($ingreso->solicitud_insumo_id)->toBeNull();
    $this->assertDatabaseHas('auditorias', [
        'auditable_type' => Ingreso::class, 'auditable_id' => $ingreso->id,
        'evento' => 'creado', 'user_id' => $this->autorCierre->id,
    ]);
})->with(['cuota_socios', 'aporte_grupos'])->with(['ordinaria', 'extraordinaria']);

test('el concepto de aporte de sede requiere el flujo vinculado y no se crea desde ingresos genéricos', function () {
    $antes = Auditoria::count();
    $this->post(route('ingresos.store'), [
        'concepto' => 'aporte_reserva', 'monto' => 5000, 'fecha_pago' => now()->toDateString(),
        'medio_pago' => 'transferencia', 'numero_transaccion' => 'TRX-APORTE',
    ])->assertSessionHasErrors('concepto');
    $this->assertDatabaseCount('ingresos', 0);
    expect(Auditoria::count())->toBe($antes);
});
