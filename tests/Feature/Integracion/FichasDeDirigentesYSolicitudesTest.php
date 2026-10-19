<?php

use App\Models\Certificado;
use App\Models\Grupo;
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
    $this->travelTo(now()->startOfDay()->addHours(8));
});

afterEach(function () { $this->travelBack(); });

function fichaCierre(User $usuario, string $estado = 'activo'): Socio
{
    return Socio::create([
        'user_id' => $usuario->id, 'nombre_completo' => $usuario->name, 'rut' => '12345678-5',
        'direccion' => 'Calle Vecinal 123', 'telefono' => '912345678',
        'residente_desde' => '2020-01-01', 'estado' => $estado,
    ]);
}

function horarioCierre(array $cambios = []): array
{
    return array_replace([
        'fecha' => now()->addDay()->toDateString(), 'hora_inicio' => '10:00', 'hora_fin' => '11:00',
    ], $cambios);
}

test('dirigentes completan la inscripción y otro dirigente valida sin cambiar su cargo', function (string $rol) {
    $usuario = User::factory()->create(['role' => $rol]);
    $this->actingAs($usuario)->get(route('dashboard'))->assertOk()->assertSee('Completa y valida tu ficha');
    $this->get(route('socios.create'))->assertOk();
    $this->post(route('socios.store'), [
        'nombre_completo' => $usuario->name, 'rut' => '12.345.678-5', 'direccion' => 'Calle Vecinal 123',
        'telefono' => '912345678', 'residente_desde' => '2020-01-01',
        'carnet' => UploadedFile::fake()->create('carnet.pdf', 10, 'application/pdf'),
        'boleta' => UploadedFile::fake()->create('boleta.pdf', 10, 'application/pdf'),
    ])->assertSessionHasNoErrors();
    $ficha = Socio::sole();
    expect($ficha->estado)->toBe('pendiente')->and($ficha->user_id)->toBe($usuario->id);
    $this->patch(route('socios.update', $ficha), ['estado' => 'por_firmar'])->assertForbidden();
    $this->get(route('certificados.index'))->assertForbidden();
    $this->post(route('arriendos.store'), horarioCierre())->assertStatus($rol === 'tesorero' ? 403 : 302);
    $this->assertDatabaseCount('reservas', 0);
    if ($rol !== 'tesorero') $this->get(route('socios.index'))->assertOk();

    $revisor = User::factory()->create(['role' => $rol === 'secretario' ? 'presidente' : 'secretario']);
    $this->actingAs($revisor)->patch(route('socios.update', $ficha), ['estado' => 'por_firmar'])->assertSessionHasNoErrors();
    $this->actingAs($usuario)->get(route('citas.index'))->assertOk();
    $this->patch(route('socios.update', $ficha), ['estado' => 'activo'])->assertForbidden();
    $this->actingAs($revisor)->patch(route('socios.update', $ficha), ['estado' => 'activo'])->assertSessionHasNoErrors();
    $this->actingAs($usuario->fresh())->get(route('certificados.index'))->assertOk();
    $this->get(route('arriendos.index'))->assertOk();
    expect($usuario->fresh()->role)->toBe($rol)->and(Socio::count())->toBe(1);
})->with(['presidente', 'secretario', 'tesorero']);

test('dirigentes con ficha activa solicitan certificado de residencia con el flujo de pago habitual', function (string $rol) {
    $usuario = User::factory()->create(['role' => $rol]);
    $ficha = fichaCierre($usuario);
    $this->actingAs($usuario)->post(route('certificados.store'), [
        'tipo' => 'residencia', 'nombre_solicitante' => $ficha->nombre_completo, 'rut_solicitante' => $ficha->rut,
        'direccion' => $ficha->direccion, 'dirigido_a' => 'Municipalidad', 'numero_transaccion' => 'TRX-RESIDENCIA',
        'comprobante' => UploadedFile::fake()->create('aporte.pdf', 10, 'application/pdf'),
    ])->assertSessionHasNoErrors();
    expect(Certificado::sole()->estado)->toBe('solicitado')->and(Certificado::sole()->user_id)->toBe($usuario->id);
})->with(['presidente', 'secretario', 'tesorero']);

test('tesorero solicita sede para sí sin gestionar importe ni reservas ajenas', function () {
    $tesorero = User::factory()->create(['role' => 'tesorero']);
    $ficha = fichaCierre($tesorero);
    $this->actingAs($tesorero)->get(route('arriendos.index'))->assertOk()->assertSee('Solicitar reserva');
    $this->post(route('arriendos.store'), horarioCierre([
        'modalidad_solicitante' => 'manual', 'responsable_nombre' => 'Otra persona', 'valor_arriendo' => 99999,
    ]))->assertSessionHasNoErrors();
    $reserva = Reserva::sole();
    expect($reserva->socio_id)->toBe($ficha->id)->and($reserva->responsable_rut)->toBe($ficha->rut)
        ->and($reserva->valor_arriendo)->toBeNull()->and($reserva->canal)->toBe('en_linea');
    $this->post(route('arriendos.aporte', $reserva), ['valor_arriendo' => 0])->assertForbidden();
    $this->post(route('arriendos.confirmar', $reserva))->assertForbidden();
    $presidente = User::factory()->create(['role' => 'presidente']);
    $this->actingAs($presidente)->post(route('arriendos.aporte', $reserva), ['valor_arriendo' => 5000])->assertSessionHasNoErrors();
    $this->get(route('arriendos.show', $reserva))->assertOk()->assertDontSee('Adjuntar comprobante del aporte');
    $this->post(route('arriendos.comprobante.store', $reserva), [
        'comprobante' => UploadedFile::fake()->create('ajeno.pdf', 10, 'application/pdf'),
    ])->assertForbidden();
    $this->actingAs($tesorero)->get(route('arriendos.show', $reserva))->assertOk()->assertSee('Adjuntar comprobante del aporte');
    $this->post(route('arriendos.comprobante.store', $reserva), [
        'comprobante' => UploadedFile::fake()->create('propio.pdf', 10, 'application/pdf'),
    ])->assertSessionHasNoErrors();
});

test('la bandeja y formulario comunes conservan actividades gratuitas y reservas de ventanilla', function () {
    $presidente = User::factory()->create(['role' => 'presidente']);
    $this->actingAs($presidente)->post(route('arriendos.store'), horarioCierre([
        'tipo_actividad' => 'actividad', 'valor_arriendo' => 0,
    ]))->assertSessionHasNoErrors();
    $actividad = Reserva::sole();
    $this->post(route('arriendos.store'), horarioCierre([
        'hora_inicio' => '12:00', 'hora_fin' => '13:00', 'modalidad_solicitante' => 'manual',
        'responsable_nombre' => 'Persona de ventanilla', 'responsable_rut' => '11111111-1',
        'responsable_direccion' => 'Calle 456', 'responsable_telefono' => '912345678', 'valor_arriendo' => 5000,
    ]))->assertSessionHasNoErrors();
    $reserva = Reserva::latest('id')->first();
    $this->get(route('reservas.index'))->assertOk()->assertViewHas('arriendos', fn ($lista) => $lista->contains('id', $actividad->id) && $lista->contains('id', $reserva->id));
    $this->get(route('arriendos.show', $reserva))->assertOk()->assertSee('Registrar comprobante recibido en ventanilla')
        ->assertDontSee('Adjuntar comprobante del aporte');
    $this->post(route('arriendos.comprobante.store', $reserva), [
        'comprobante' => UploadedFile::fake()->create('ventanilla.pdf', 10, 'application/pdf'),
    ])->assertSessionHasNoErrors();
    $this->post(route('arriendos.store'), horarioCierre(['tipo_actividad' => 'actividad', 'valor_arriendo' => 1]))->assertSessionHasErrors('valor_arriendo');
    $this->assertDatabaseCount('reservas', 2);
    $this->assertDatabaseCount('socios', 0);
});

test('representantes externos requieren RUT válido y el préstamo conserva su identidad', function () {
    $presidente = User::factory()->create(['role' => 'presidente']);
    $this->actingAs($presidente);
    $datos = ['nombre' => 'Taller cierre', 'origen_representante' => 'manual', 'representante_nombre' => 'Representante externo'];
    $this->post(route('grupos.store'), $datos)->assertSessionHasErrors('representante_rut');
    $this->post(route('grupos.store'), $datos + ['representante_rut' => '12345678-0'])->assertSessionHasErrors('representante_rut');
    $this->post(route('grupos.store'), $datos + ['representante_rut' => '12.345.678-5'])->assertSessionHasNoErrors();
    $grupo = Grupo::sole();
    $item = ItemInventario::create(['nombre' => 'Sillas', 'cantidad' => 5, 'tipo_insumo_id' => TipoInsumo::where('nombre', 'Sillas')->value('id')]);
    $solicitud = [
        'solicitante' => 'grupo:'.$grupo->id, 'telefono_contacto' => '912345678',
        'fecha_comprometida' => now()->addWeek()->toDateString(), 'lineas' => [['item_inventario_id' => $item->id, 'cantidad' => 1]],
    ];
    $this->post(route('insumos.store'), $solicitud)->assertSessionHasNoErrors();
    expect(SolicitudInsumo::sole()->solicitante_rut)->toBe('12345678-5');
    $grupo->update(['representante_rut' => null]);
    $this->post(route('insumos.store'), $solicitud)->assertSessionHasErrors('solicitante_rut');
    $this->post(route('reservas.store'), horarioCierre(['tipo' => 'grupo', 'grupo_id' => $grupo->id]))->assertSessionHasErrors('reserva');
    expect(SolicitudInsumo::sole()->solicitante_rut)->toBe('12345678-5');
    $this->assertDatabaseCount('socios', 0);
});

test('dirigentes sin ficha activa conservan gestión pero no pueden solicitar insumos propios', function (string $rol) {
    $usuario = User::factory()->create(['role' => $rol]);
    $item = ItemInventario::create(['nombre' => 'Sillas', 'cantidad' => 5, 'tipo_insumo_id' => TipoInsumo::where('nombre', 'Sillas')->value('id')]);
    $respuesta = $this->actingAs($usuario)->post(route('insumos.store'), [
        'solicitante' => 'usuario:'.$usuario->id, 'telefono_contacto' => '912345678',
        'fecha_comprometida' => now()->addWeek()->toDateString(),
        'lineas' => [['item_inventario_id' => $item->id, 'cantidad' => 1]],
    ]);
    if ($rol === 'tesorero') $respuesta->assertForbidden();
    else $respuesta->assertSessionHasErrors('solicitante');
    $this->assertDatabaseCount('solicitudes_insumos', 0);
    $this->get(route('insumos.index'))->assertStatus($rol === 'tesorero' ? 403 : 200);
})->with(['presidente', 'secretario', 'tesorero']);
