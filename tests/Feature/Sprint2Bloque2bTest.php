<?php

use App\Models\Auditoria;
use App\Models\Devolucion;
use App\Models\Egreso;
use App\Models\Ingreso;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->actingAs(User::factory()->create(['role' => 'presidente']));
});

function datosEgresoBloque2b(array $cambios = []): array
{
    return array_replace([
        'concepto' => 'servicios_basicos',
        'fecha_pago' => now()->toDateString(),
        'monto' => 2000,
        'numero_transaccion' => 'TRX-2B-001',
        'pagado_por' => 'Responsable del pago',
        'observaciones' => null,
        'comprobante' => UploadedFile::fake()->create('comprobante.pdf', 100, 'application/pdf'),
    ], $cambios);
}

function ingresoBloque2b(array $cambios = []): Ingreso
{
    return Ingreso::create(array_replace([
        'concepto' => 'cuota_socios',
        'tipo_cuota' => 'ordinaria',
        'fecha_pago' => now()->toDateString(),
        'monto' => 5000,
        'medio_pago' => 'efectivo',
        'talonario' => 'T-2B-001',
        'registrado_por' => auth()->id(),
    ], $cambios));
}

function egresoBloque2b(): Egreso
{
    $datos = datosEgresoBloque2b();
    unset($datos['comprobante']);

    return Egreso::create($datos + [
        'comprobante_path' => 'comprobantes/egresos/prueba.pdf',
        'registrado_por' => auth()->id(),
    ]);
}

test('presidente y tesorero registran egresos con comprobante privado y descuentan el saldo', function (string $rol, string $concepto) {
    $usuario = User::factory()->create(['role' => $rol]);
    $this->actingAs($usuario);
    ingresoBloque2b(['monto' => 10000]);

    $this->post(route('egresos.store'), datosEgresoBloque2b([
        'concepto' => $concepto,
        'registrado_por' => 999999, // El autor debe salir de la sesión.
    ]))->assertSessionHasNoErrors()->assertRedirect(route('egresos.index'));

    $egreso = Egreso::sole();
    $this->assertDatabaseHas('egresos', [
        'id' => $egreso->id,
        'concepto' => $concepto,
        'monto' => 2000,
        'numero_transaccion' => 'TRX-2B-001',
        'pagado_por' => 'Responsable del pago',
        'registrado_por' => $usuario->id,
        'observaciones' => null,
    ]);
    expect($egreso->fecha_pago->toDateString())->toBe(now()->toDateString())
        ->and(Ingreso::saldoCaja())->toBe(8000);
    Storage::disk('local')->assertExists($egreso->comprobante_path);
    $this->get(route('egresos.comprobante', $egreso))->assertOk();
})->with(['presidente', 'tesorero'])->with(['servicios_basicos', 'mantencion']);

test('un egreso exige todos sus datos obligatorios sin guardar archivos ni movimientos', function (string $campo) {
    $datos = datosEgresoBloque2b();
    unset($datos[$campo]);

    $this->post(route('egresos.store'), $datos)->assertSessionHasErrors($campo);

    $this->assertDatabaseCount('egresos', 0);
    expect(Storage::disk('local')->allFiles())->toBe([]);
})->with(['concepto', 'fecha_pago', 'monto', 'numero_transaccion', 'pagado_por', 'comprobante']);

test('un egreso rechaza concepto fecha y monto inválidos', function (string $campo, mixed $valor) {
    $this->post(route('egresos.store'), datosEgresoBloque2b([$campo => $valor]))
        ->assertSessionHasErrors($campo);

    $this->assertDatabaseCount('egresos', 0);
})->with([
    'concepto no permitido' => ['concepto', 'arriendo'],
    'fecha futura' => ['fecha_pago', '2999-01-01'],
    'monto cero' => ['monto', 0],
    'monto negativo' => ['monto', -100],
    'monto decimal' => ['monto', 10.5],
]);

test('un egreso rechaza comprobantes de tipo o tamaño no permitidos', function (string $nombre, int $tamano, string $mime) {
    $this->post(route('egresos.store'), datosEgresoBloque2b([
        'comprobante' => UploadedFile::fake()->create($nombre, $tamano, $mime),
    ]))->assertSessionHasErrors('comprobante');

    $this->assertDatabaseCount('egresos', 0);
    expect(Storage::disk('local')->allFiles())->toBe([]);
})->with([
    'archivo de texto' => ['comprobante.txt', 100, 'text/plain'],
    'PDF mayor a 5 MB' => ['comprobante.pdf', 5121, 'application/pdf'],
]);

test('anular un egreso conserva fecha motivo y autor sin crear un movimiento contrario', function (string $rol) {
    $usuario = User::factory()->create(['role' => $rol]);
    $this->actingAs($usuario);
    $egreso = egresoBloque2b();
    $saldoAntes = Ingreso::saldoCaja();

    $this->post(route('egresos.anular', $egreso), ['motivo_anulacion' => 'Pago duplicado'])
        ->assertSessionHasNoErrors()->assertRedirect(route('egresos.index'));

    $egreso->refresh();
    expect($egreso->estaAnulado())->toBeTrue()
        ->and($egreso->anulado_en->toDateString())->toBe(now()->toDateString())
        ->and($egreso->motivo_anulacion)->toBe('Pago duplicado')
        ->and($egreso->anulado_por)->toBe($usuario->id)
        ->and($egreso->monto)->toBe(2000)
        ->and(Ingreso::saldoCaja())->toBe($saldoAntes + 2000);
    $this->assertDatabaseCount('egresos', 1);
    $this->assertDatabaseCount('ingresos', 0);
    $this->assertDatabaseCount('devoluciones', 0);
    $this->assertDatabaseHas('auditorias', [
        'auditable_type' => Egreso::class,
        'auditable_id' => $egreso->id,
        'evento' => 'modificado',
        'user_id' => $usuario->id,
    ]);
})->with(['presidente', 'tesorero']);

test('no se puede anular un egreso sin motivo ni anularlo dos veces', function () {
    $egreso = egresoBloque2b();

    $this->post(route('egresos.anular', $egreso), ['motivo_anulacion' => ''])
        ->assertSessionHasErrors('motivo_anulacion');
    expect($egreso->fresh()->estaAnulado())->toBeFalse()
        ->and(Ingreso::saldoCaja())->toBe(-2000);

    $this->post(route('egresos.anular', $egreso), ['motivo_anulacion' => 'Primera anulación'])
        ->assertRedirect(route('egresos.index'));
    $this->post(route('egresos.anular', $egreso), ['motivo_anulacion' => 'Segunda anulación'])
        ->assertSessionHasErrors('egreso');

    expect($egreso->fresh()->motivo_anulacion)->toBe('Primera anulación')
        ->and(Ingreso::saldoCaja())->toBe(0);
});

test('devolver una cuota crea un movimiento propio ligado al ingreso y descuenta la caja', function (string $rol, string $tipoCuota) {
    $usuario = User::factory()->create(['role' => $rol]);
    $this->actingAs($usuario);
    $cuota = ingresoBloque2b(['tipo_cuota' => $tipoCuota]);

    $this->post(route('ingresos.devolver', $cuota), [
        'motivo' => 'Cuota pagada por error',
        'monto' => -100, // El monto a devolver se obtiene de la cuota original.
    ])->assertSessionHasNoErrors()->assertRedirect(route('ingresos.index'));

    $devolucion = Devolucion::sole();
    expect($devolucion->ingreso_id)->toBe($cuota->id)
        ->and($devolucion->monto)->toBe(5000)
        ->and($devolucion->motivo)->toBe('Cuota pagada por error')
        ->and($devolucion->registrado_por)->toBe($usuario->id)
        ->and($devolucion->fecha_devolucion->toDateString())->toBe(now()->toDateString())
        ->and($cuota->fresh()->estaAnulado())->toBeFalse()
        ->and($cuota->fresh()->monto)->toBe(5000)
        ->and(Ingreso::saldoCaja())->toBe(0);
    $this->assertDatabaseCount('ingresos', 1);
    $this->assertDatabaseCount('egresos', 0);
    $this->assertDatabaseHas('auditorias', [
        'auditable_type' => Devolucion::class,
        'auditable_id' => $devolucion->id,
        'evento' => 'creado',
        'user_id' => $usuario->id,
    ]);
})->with(['presidente', 'tesorero'])->with(['ordinaria', 'extraordinaria']);

test('la devolución exige un motivo', function () {
    $cuota = ingresoBloque2b();

    $this->post(route('ingresos.devolver', $cuota), ['motivo' => ''])
        ->assertSessionHasErrors('motivo');

    $this->assertDatabaseCount('devoluciones', 0);
    expect(Ingreso::saldoCaja())->toBe(5000);
});

test('una cuota devuelta no se puede devolver nuevamente ni anular', function () {
    $cuota = ingresoBloque2b();

    $this->post(route('ingresos.devolver', $cuota), ['motivo' => 'Primera devolución'])
        ->assertRedirect(route('ingresos.index'));
    $this->post(route('ingresos.devolver', $cuota), ['motivo' => 'Segunda devolución'])
        ->assertSessionHasErrors('devolucion');
    $this->post(route('ingresos.anular', $cuota), ['motivo_anulacion' => 'Intento de anulación'])
        ->assertSessionHasErrors('ingreso');

    $this->assertDatabaseCount('devoluciones', 1);
    expect(Devolucion::sole()->motivo)->toBe('Primera devolución')
        ->and($cuota->fresh()->estaAnulado())->toBeFalse()
        ->and(Ingreso::saldoCaja())->toBe(0);
});

test('no se pueden devolver ingresos de otros conceptos ni cuotas anuladas', function (string $concepto, bool $anulado) {
    $ingreso = ingresoBloque2b(['concepto' => $concepto]);
    if ($anulado) {
        $ingreso->anular('Pago registrado por error', auth()->user());
    }
    $saldoAntes = Ingreso::saldoCaja();

    $this->post(route('ingresos.devolver', $ingreso), ['motivo' => 'Solicito devolución'])
        ->assertSessionHasErrors('devolucion');

    $this->assertDatabaseCount('devoluciones', 0);
    expect(Ingreso::saldoCaja())->toBe($saldoAntes);
})->with([
    'otros ingresos' => ['otros', false],
    'certificados' => ['certificados', false],
    'cuota anulada' => ['cuota_socios', true],
]);

test('el saldo combina ingresos egresos devoluciones y anulaciones', function () {
    $ingreso = ingresoBloque2b(['concepto' => 'otros', 'tipo_cuota' => null, 'monto' => 10000]);
    $cuota = ingresoBloque2b();
    $egreso = egresoBloque2b();
    expect(Ingreso::saldoCaja())->toBe(13000);

    $this->post(route('ingresos.devolver', $cuota), ['motivo' => 'Devolución solicitada'])
        ->assertRedirect(route('ingresos.index'));
    expect(Ingreso::saldoCaja())->toBe(8000);

    $this->post(route('egresos.anular', $egreso), ['motivo_anulacion' => 'Egreso duplicado'])
        ->assertRedirect(route('egresos.index'));
    expect(Ingreso::saldoCaja())->toBe(10000);

    $this->post(route('ingresos.anular', $ingreso), ['motivo_anulacion' => 'Ingreso duplicado'])
        ->assertRedirect(route('ingresos.index'));
    expect(Ingreso::saldoCaja())->toBe(0);
    $this->assertDatabaseCount('ingresos', 2);
    $this->assertDatabaseCount('egresos', 1);
    $this->assertDatabaseCount('devoluciones', 1);
});

test('egresos y devoluciones no se pueden eliminar', function () {
    $egreso = egresoBloque2b();
    $cuota = ingresoBloque2b();
    $this->post(route('ingresos.devolver', $cuota), ['motivo' => 'Cuota duplicada'])
        ->assertRedirect(route('ingresos.index'));
    $devolucion = Devolucion::sole();

    expect(fn () => $egreso->delete())->toThrow(LogicException::class);
    expect(fn () => $devolucion->delete())->toThrow(LogicException::class);
    $this->assertDatabaseHas('egresos', ['id' => $egreso->id]);
    $this->assertDatabaseHas('devoluciones', ['id' => $devolucion->id]);
});

test('secretario administrador y vecino no pueden registrar egresos anularlos ni devolver cuotas', function (string $rol) {
    $egreso = egresoBloque2b();
    $cuota = ingresoBloque2b();
    $this->actingAs(User::factory()->create(['role' => $rol]));

    $this->post(route('egresos.store'), datosEgresoBloque2b())->assertForbidden();
    $this->post(route('egresos.anular', $egreso), ['motivo_anulacion' => 'Sin permiso'])->assertForbidden();
    $this->post(route('ingresos.devolver', $cuota), ['motivo' => 'Sin permiso'])->assertForbidden();
    $this->get(route('egresos.comprobante', $egreso))->assertForbidden();

    $this->assertDatabaseCount('egresos', 1);
    $this->assertDatabaseCount('devoluciones', 0);
    expect($egreso->fresh()->estaAnulado())->toBeFalse()
        ->and(Ingreso::saldoCaja())->toBe(3000);
    expect(Storage::disk('local')->allFiles())->toBe([]);
})->with(['secretario', 'admin', 'vecino']);
