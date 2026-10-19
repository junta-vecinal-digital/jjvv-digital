<?php

use App\Models\Acta;
use App\Models\Socio;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->withoutVite();
    Storage::fake('local');
    Storage::fake('public');
    $this->presidenteActas = User::factory()->create(['role' => 'presidente']);
    $this->asambleaActas = Acta::create([
        'fecha' => '2026-01-10', 'tipo' => 'asamblea', 'acuerdos' => 'Acuerdo público de asamblea',
        'registrado_por' => $this->presidenteActas->id,
    ]);
    $this->directivaActas = Acta::create([
        'fecha' => '2026-01-11', 'tipo' => 'directiva', 'acuerdos' => 'Acuerdo reservado de directiva',
        'registrado_por' => $this->presidenteActas->id,
    ]);
});

function socioParaActas(string $estado = 'activo', string $rol = 'vecino'): Socio
{
    $usuario = User::factory()->create(['role' => $rol]);

    return Socio::create([
        'user_id' => $usuario->id, 'nombre_completo' => 'Persona de prueba', 'rut' => '12345678-5',
        'direccion' => 'Pasaje 123', 'telefono' => '912345678', 'residente_desde' => '2020-01-01',
        'estado' => $estado,
    ]);
}

test('presidente y secretario registran ambos tipos con autor automático y escaneo opcional privado', function (string $rol, bool $adjunto) {
    $usuario = User::factory()->create(['role' => $rol]);
    $this->actingAs($usuario)->get(route('actas.create'))->assertOk();
    $datos = [
        'fecha' => '2026-02-01', 'tipo' => $adjunto ? 'directiva' : 'asamblea',
        'acuerdos' => "Primer acuerdo\nSegundo acuerdo", 'registrado_por' => $this->presidenteActas->id,
        'escaneo_path' => 'ruta/manipulada.pdf',
    ];
    if ($adjunto) $datos['escaneo'] = UploadedFile::fake()->create('escaneo.pdf', 100, 'application/pdf');

    $this->post(route('actas.store'), $datos)->assertSessionHasNoErrors();
    $acta = Acta::latest('id')->first();
    expect($acta->registrado_por)->toBe($usuario->id)->and($acta->acuerdos)->toBe("Primer acuerdo\nSegundo acuerdo")
        ->and($acta->tipo)->toBe($datos['tipo']);
    $this->get(route('actas.show', $acta))->assertOk()->assertSee('Primer acuerdo')->assertSee('01/02/2026');
    if ($adjunto) {
        Storage::disk('local')->assertExists($acta->escaneo_path);
        Storage::disk('public')->assertMissing($acta->escaneo_path);
        $this->get(route('actas.escaneo', $acta))->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
    } else {
        expect($acta->escaneo_path)->toBeNull();
        $this->get(route('actas.escaneo', $acta))->assertNotFound();
    }
})->with([['presidente', false], ['presidente', true], ['secretario', false], ['secretario', true]]);

test('los dirigentes consultan ambos tipos y pueden filtrar el listado', function (string $rol) {
    $this->actingAs(User::factory()->create(['role' => $rol]));
    $this->get(route('actas.index'))->assertOk()->assertViewHas('actas', fn ($lista) => $lista->total() === 2);
    foreach ([$this->asambleaActas, $this->directivaActas] as $acta) {
        $this->get(route('actas.show', $acta))->assertOk()->assertSee($acta->acuerdos);
    }
    $this->get(route('actas.index', ['tipo' => 'directiva']))->assertOk()
        ->assertViewHas('actas', fn ($lista) => $lista->total() === 1 && $lista->first()->id === $this->directivaActas->id);
})->with(['presidente', 'secretario', 'tesorero']);

test('el socio activo solo consulta asamblea y no puede forzar la consulta de directiva', function () {
    $socio = socioParaActas();
    $this->actingAs($socio->user)->get(route('actas.index'))->assertOk()
        ->assertViewHas('actas', fn ($lista) => $lista->total() === 1 && $lista->first()->id === $this->asambleaActas->id)
        ->assertDontSee(route('actas.show', $this->directivaActas));
    $this->get(route('actas.show', $this->asambleaActas))->assertOk();
    $this->get(route('actas.show', $this->directivaActas))->assertForbidden();
    $this->get(route('actas.escaneo', $this->directivaActas))->assertForbidden();
    $this->getJson(route('actas.index', ['tipo' => 'directiva']))->assertUnprocessable();
    $this->get(route('dashboard'))->assertOk()->assertSee(route('actas.index'));
});

test('las fichas no activas no acceden a actas ni al menú', function (string $estado) {
    $socio = socioParaActas($estado);
    $this->actingAs($socio->user)->get(route('actas.index'))->assertForbidden();
    $this->get(route('actas.show', $this->asambleaActas))->assertForbidden();
    $this->get(route('actas.escaneo', $this->asambleaActas))->assertForbidden();
    $this->get(route('dashboard'))->assertOk()->assertDontSee(route('actas.index'));
})->with(['pendiente', 'por_firmar', 'rechazado', 'inactivo']);

test('administrador y vecino sin ficha no tienen acceso mientras que el administrador socio activo solo ve asamblea', function () {
    foreach (['admin', 'vecino'] as $rol) {
        $this->actingAs(User::factory()->create(['role' => $rol]))->get(route('actas.index'))->assertForbidden();
        $this->get(route('actas.show', $this->asambleaActas))->assertForbidden();
        $this->get(route('actas.show', $this->directivaActas))->assertForbidden();
    }
    $socio = socioParaActas('activo', 'admin');
    $this->actingAs($socio->user)->get(route('actas.show', $this->asambleaActas))->assertOk();
    $this->get(route('actas.show', $this->directivaActas))->assertForbidden();
});

test('la baja retira también el acceso directo a un escaneo de asamblea', function () {
    Storage::disk('local')->put('actas/asamblea.pdf', 'contenido privado');
    $this->asambleaActas->update(['escaneo_path' => 'actas/asamblea.pdf']);
    $socio = socioParaActas();
    $this->actingAs($socio->user)->get(route('actas.escaneo', $this->asambleaActas))->assertOk();
    $this->actingAs($this->presidenteActas)->patch(route('socios.update', $socio), [
        'estado' => 'inactivo', 'motivo_baja' => 'Solicitud personal', 'fecha_baja' => now()->toDateString(),
    ])->assertSessionHasNoErrors();
    $this->actingAs($socio->user->fresh())->get(route('actas.index'))->assertForbidden();
    $this->get(route('actas.show', $this->asambleaActas))->assertForbidden();
    $this->get(route('actas.escaneo', $this->asambleaActas))->assertForbidden();
});

test('tesorero y socio activo pueden leer pero no registrar actas', function (string $rol) {
    $usuario = $rol === 'vecino' ? socioParaActas()->user : User::factory()->create(['role' => $rol]);
    $this->actingAs($usuario)->get(route('actas.create'))->assertForbidden();
    $this->post(route('actas.store'), [
        'fecha' => '2026-02-01', 'tipo' => 'asamblea', 'acuerdos' => 'Registro no autorizado',
        'escaneo' => UploadedFile::fake()->create('escaneo.pdf', 100, 'application/pdf'),
    ])->assertForbidden();
    $this->assertDatabaseCount('actas', 2);
    expect(Storage::disk('local')->allFiles('actas'))->toBeEmpty();
})->with(['tesorero', 'vecino', 'admin']);

test('actas valida campos obligatorios y formatos antes de guardar', function (array $cambios, string $campo) {
    $this->actingAs($this->presidenteActas)->post(route('actas.store'), array_replace([
        'fecha' => '2026-02-01', 'tipo' => 'asamblea', 'acuerdos' => 'Acuerdo válido',
    ], $cambios))->assertSessionHasErrors($campo);
    $this->assertDatabaseCount('actas', 2);
    expect(Storage::disk('local')->allFiles('actas'))->toBeEmpty();
})->with([
    [['fecha' => '31/12/2026'], 'fecha'],
    [['tipo' => 'otro'], 'tipo'],
    [['acuerdos' => '   '], 'acuerdos'],
    [['acuerdos' => ['texto']], 'acuerdos'],
]);

test('el escaneo rechaza otros formatos y archivos mayores a cinco megabytes', function (bool $grande) {
    $archivo = $grande ? UploadedFile::fake()->create('grande.pdf', 5121, 'application/pdf')
        : UploadedFile::fake()->create('archivo.html', 10, 'text/html');
    $this->actingAs($this->presidenteActas)->post(route('actas.store'), [
        'fecha' => '2026-02-01', 'tipo' => 'asamblea', 'acuerdos' => 'Acuerdo válido', 'escaneo' => $archivo,
    ])->assertSessionHasErrors('escaneo');
    $this->assertDatabaseCount('actas', 2);
    expect(Storage::disk('local')->allFiles('actas'))->toBeEmpty();
})->with([false, true]);

test('los acuerdos se muestran como texto y el escaneo ausente responde con no encontrado', function () {
    $this->asambleaActas->update(['acuerdos' => '<script>alert(1)</script>', 'escaneo_path' => 'actas/inexistente.pdf']);
    $this->actingAs($this->presidenteActas)->get(route('actas.show', $this->asambleaActas))->assertOk()
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    $this->get(route('actas.escaneo', $this->asambleaActas))->assertNotFound();
});

test('las actas y sus archivos requieren iniciar sesión', function () {
    foreach (['actas.index', 'actas.create'] as $ruta) {
        $this->get(route($ruta))->assertRedirect(route('login'));
    }
    $this->get(route('actas.show', $this->asambleaActas))->assertRedirect(route('login'));
    $this->get(route('actas.escaneo', $this->asambleaActas))->assertRedirect(route('login'));
    $this->post(route('actas.store'), [])->assertRedirect(route('login'));
});
