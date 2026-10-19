<?php

use App\Models\Auditoria;
use App\Models\Certificado;
use App\Models\Devolucion;
use App\Models\Egreso;
use App\Models\Garantia;
use App\Models\Ingreso;
use App\Models\Socio;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    $this->withoutVite();
    $this->travelTo(Carbon::parse('2026-10-17 10:00:00'));
    $this->presidenteAuditoria = User::factory()->create(['role' => 'presidente']);
    $this->tesoreroAuditoria = User::factory()->create(['role' => 'tesorero']);
    $this->actingAs($this->presidenteAuditoria);
});

afterEach(function () {
    $this->travelBack();
});

function registroAuditoriaSprint2(string $modelo, ?int $autor, string $fecha = '2026-10-15 12:00:00'): Auditoria
{
    // El registro polimórfico de auditoría conserva su información sin consultar al modelo de origen.
    return Auditoria::create([
        'auditable_type' => $modelo, 'auditable_id' => 123, 'user_id' => $autor,
        'evento' => 'creado', 'cambios' => ['monto' => ['antes' => null, 'despues' => 5000]],
        'ip' => '192.0.2.10', 'created_at' => $fecha,
    ]);
}

test('cada categoría de auditoría devuelve únicamente sus tipos de registro', function (string $categoria, array $modelos) {
    $registros = collect([Socio::class, Certificado::class, User::class, Ingreso::class, Egreso::class, Devolucion::class, Garantia::class])
        ->map(fn ($modelo) => registroAuditoriaSprint2($modelo, $this->tesoreroAuditoria->id));
    $esperados = $registros->whereIn('auditable_type', $modelos)->pluck('id')->sortDesc()->values()->all();
    $this->get(route('auditoria.index', ['modelo' => $categoria, 'desde' => '2026-10-15', 'hasta' => '2026-10-15']))
        ->assertOk()->assertViewHas('auditorias', fn ($lista) => $lista->pluck('id')->all() === $esperados);
})->with([
    ['socio', [Socio::class]], ['certificado', [Certificado::class]], ['usuario', [User::class]],
    ['finanzas', [Ingreso::class, Egreso::class, Devolucion::class, Garantia::class]],
]);

test('combina categoría autor y fechas incluyendo ambos extremos del día', function () {
    $primero = registroAuditoriaSprint2(Ingreso::class, $this->tesoreroAuditoria->id, '2026-10-15 00:00:00');
    $ultimo = registroAuditoriaSprint2(Devolucion::class, $this->tesoreroAuditoria->id, '2026-10-15 23:59:59');
    registroAuditoriaSprint2(Egreso::class, $this->tesoreroAuditoria->id, '2026-10-14 23:59:59');
    registroAuditoriaSprint2(Garantia::class, $this->tesoreroAuditoria->id, '2026-10-16 00:00:00');
    registroAuditoriaSprint2(Ingreso::class, $this->presidenteAuditoria->id);
    registroAuditoriaSprint2(Socio::class, $this->tesoreroAuditoria->id);
    $this->get(route('auditoria.index', [
        'modelo' => 'finanzas', 'autor' => $this->tesoreroAuditoria->id,
        'desde' => '2026-10-15', 'hasta' => '2026-10-15',
    ]))->assertOk()->assertViewHas('auditorias', fn ($lista) => $lista->pluck('id')->all() === [$ultimo->id, $primero->id]);
});

test('permite filtrar solo desde o solo hasta sin exigir el otro límite', function (string $limite, string $esperada) {
    $anteriores = registroAuditoriaSprint2(Ingreso::class, $this->tesoreroAuditoria->id, '2026-10-14 23:59:59');
    $posteriores = registroAuditoriaSprint2(Ingreso::class, $this->tesoreroAuditoria->id, '2026-10-16 00:00:00');
    $id = $esperada === 'anteriores' ? $anteriores->id : $posteriores->id;
    $this->get(route('auditoria.index', ['modelo' => 'finanzas', $limite => '2026-10-15']))->assertOk()
        ->assertViewHas('auditorias', fn ($lista) => $lista->pluck('id')->all() === [$id]);
})->with([['desde', 'posteriores'], ['hasta', 'anteriores']]);

test('la paginación conserva filtros total y orden sin repetir registros', function () {
    $ids = [];
    for ($i = 0; $i < 31; $i++) $ids[] = registroAuditoriaSprint2(Ingreso::class, $this->tesoreroAuditoria->id)->id;
    registroAuditoriaSprint2(Socio::class, $this->tesoreroAuditoria->id);
    registroAuditoriaSprint2(Ingreso::class, $this->presidenteAuditoria->id);
    $filtros = ['modelo' => 'finanzas', 'autor' => $this->tesoreroAuditoria->id, 'desde' => '2026-10-15', 'hasta' => '2026-10-15'];
    $esperados = array_reverse($ids);
    $respuesta = $this->get(route('auditoria.index', $filtros))->assertOk()
        ->assertViewHas('auditorias', fn ($lista) => $lista->total() === 31 && $lista->pluck('id')->all() === array_slice($esperados, 0, 25));
    $pagina = $respuesta->viewData('auditorias');
    parse_str(parse_url($pagina->nextPageUrl(), PHP_URL_QUERY), $consulta);
    foreach ($filtros as $clave => $valor) expect((string) $consulta[$clave])->toBe((string) $valor);
    expect((int) $consulta['page'])->toBe(2);
    $this->get($pagina->nextPageUrl())->assertOk()
        ->assertViewHas('auditorias', fn ($lista) => $lista->total() === 31 && $lista->pluck('id')->all() === array_slice($esperados, 25));
});

test('rechaza filtros inválidos sin modificar el historial', function (array $filtros, string $campo) {
    $antes = Auditoria::count();
    $this->from(route('auditoria.index'))->get(route('auditoria.index', $filtros))->assertSessionHasErrors($campo);
    expect(Auditoria::count())->toBe($antes);
})->with([
    [['modelo' => 'inexistente'], 'modelo'], [['desde' => 'no-es-fecha'], 'desde'],
    [['hasta' => 'no-es-fecha'], 'hasta'], [['desde' => '2026-10-16', 'hasta' => '2026-10-15'], 'hasta'],
    [['autor' => 999999], 'autor'], [['autor' => 'texto'], 'autor'],
]);

test('solo presidente consulta auditoría aunque otro rol tenga acceso financiero', function (string $rol) {
    $usuario = User::factory()->create(['role' => $rol]);
    $this->actingAs($usuario)->get(route('auditoria.index', ['modelo' => 'finanzas']))->assertForbidden();
})->with(['secretario', 'tesorero', 'admin', 'vecino']);

test('sin sesión no se consulta auditoría', function () {
    auth()->logout();
    $this->get(route('auditoria.index'))->assertRedirect(route('login'));
});

test('un ingreso y su anulación guardan fecha IP autor real y cambios antes y después', function () {
    $this->actingAs($this->tesoreroAuditoria)->withServerVariables(['REMOTE_ADDR' => '192.0.2.25'])
        ->post(route('ingresos.store'), [
            'concepto' => 'cuota_socios', 'tipo_cuota' => 'ordinaria', 'monto' => 5000,
            'fecha_pago' => '2026-10-17', 'medio_pago' => 'efectivo', 'talonario' => 'T-AUDITORIA',
            'registrado_por' => $this->presidenteAuditoria->id,
        ])->assertSessionHasNoErrors();
    $ingreso = Ingreso::sole();
    $creacion = Auditoria::where('auditable_type', Ingreso::class)->where('auditable_id', $ingreso->id)->where('evento', 'creado')->sole();
    expect($creacion->user_id)->toBe($this->tesoreroAuditoria->id)->and($creacion->ip)->toBe('192.0.2.25')
        ->and($creacion->created_at->equalTo(now()))->toBeTrue()
        ->and($creacion->cambios['monto'])->toBe(['antes' => null, 'despues' => 5000]);
    $this->actingAs($this->presidenteAuditoria)->withServerVariables(['REMOTE_ADDR' => '192.0.2.26'])
        ->post(route('ingresos.anular', $ingreso), ['motivo_anulacion' => 'Pago duplicado'])->assertSessionHasNoErrors();
    $modificacion = Auditoria::where('auditable_type', Ingreso::class)->where('auditable_id', $ingreso->id)->where('evento', 'modificado')->sole();
    expect($modificacion->user_id)->toBe($this->presidenteAuditoria->id)->and($modificacion->ip)->toBe('192.0.2.26')
        ->and($modificacion->cambios['motivo_anulacion'])->toBe(['antes' => null, 'despues' => 'Pago duplicado'])
        ->and($modificacion->cambios['anulado_por'])->toBe(['antes' => null, 'despues' => $this->presidenteAuditoria->id]);
});

test('auditoría sin usuario conserva su información y puede consultarse', function () {
    $registro = registroAuditoriaSprint2(Ingreso::class, null);
    $this->get(route('auditoria.index', ['modelo' => 'finanzas']))->assertOk()->assertSee('Sistema / autoregistro')
        ->assertViewHas('auditorias', fn ($lista) => $lista->contains('id', $registro->id));
});

test('intentos de modificar o borrar auditoría mantienen intacto el registro persistido', function () {
    $registro = registroAuditoriaSprint2(Ingreso::class, $this->tesoreroAuditoria->id)->refresh();
    $antes = $registro->getAttributes();
    expect(fn () => $registro->update(['evento' => 'eliminado']))->toThrow(LogicException::class);
    expect($registro->refresh()->getAttributes())->toBe($antes);
    expect(fn () => $registro->delete())->toThrow(LogicException::class);
    expect($registro->fresh()->getAttributes())->toBe($antes);
});
