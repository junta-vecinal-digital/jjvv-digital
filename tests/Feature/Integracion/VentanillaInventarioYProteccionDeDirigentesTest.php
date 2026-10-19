<?php

use App\Models\Grupo;
use App\Models\ItemInventario;
use App\Models\Reserva;
use App\Models\Socio;
use App\Models\SolicitudInsumo;
use App\Models\TipoInsumo;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
    $this->presidenteCorrecciones = User::factory()->create(['role' => 'presidente']);
});

test('dirigentes y administradores no eliminan su propia cuenta', function (string $rol) {
    $usuario = User::factory()->create(['role' => $rol]);
    $this->actingAs($usuario)->delete(route('profile.destroy'), ['password' => 'password'])->assertForbidden();
    expect($usuario->fresh())->not->toBeNull();
})->with(['presidente', 'secretario', 'tesorero', 'admin']);

test('solo administrador da de baja la ficha de un dirigente y conserva su cargo', function (string $rol) {
    $dirigente = User::factory()->create(['role' => 'tesorero']);
    $ficha = Socio::create([
        'user_id' => $dirigente->id, 'nombre_completo' => 'Dirigente', 'rut' => '12345678-5',
        'direccion' => 'Calle 123', 'telefono' => '912345678', 'residente_desde' => '2020-01-01', 'estado' => 'activo',
    ]);
    $datos = ['estado' => 'inactivo', 'motivo_baja' => 'Cambio de residencia', 'fecha_baja' => today()->toDateString()];
    $this->actingAs(User::factory()->create(['role' => $rol]));
    if ($rol !== 'admin') {
        $this->patch(route('socios.update', $ficha), $datos)->assertForbidden();
        $this->post(route('usuarios.baja', $dirigente), $datos)->assertForbidden();
        $this->delete(route('usuarios.destroy', $dirigente), ['confirmar_eliminacion' => 1])->assertForbidden();
        expect($ficha->fresh()->estado)->toBe('activo');
        return;
    }
    $this->post(route('usuarios.baja', $dirigente), $datos)->assertSessionHasNoErrors();
    expect($ficha->fresh()->estado)->toBe('inactivo')->and($dirigente->fresh()->role)->toBe('tesorero');
})->with(['presidente', 'secretario', 'admin']);

test('administrador elimina otra cuenta conserva ficha y no se elimina a si mismo', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $dirigente = $this->presidenteCorrecciones;
    $ficha = Socio::create([
        'user_id' => $dirigente->id, 'nombre_completo' => 'Dirigente', 'rut' => '12345678-5',
        'direccion' => 'Calle 123', 'telefono' => '912345678', 'residente_desde' => '2020-01-01', 'estado' => 'activo',
    ]);
    $this->actingAs($admin)->delete(route('usuarios.destroy', $admin), ['confirmar_eliminacion' => 1])->assertForbidden();
    $this->delete(route('usuarios.destroy', $dirigente), ['confirmar_eliminacion' => 1])->assertSessionHasNoErrors();
    expect($dirigente->fresh())->toBeNull()->and($ficha->fresh()->user_id)->toBeNull();
});

test('ventanilla permite solicitar y aprobar insumos para vecino sin ficha ni agrupacion', function (string $rol) {
    $responsable = User::factory()->create(['role' => $rol]);
    $item = ItemInventario::create(['nombre' => 'Sillas', 'cantidad' => 5, 'tipo_insumo_id' => TipoInsumo::where('nombre', 'Sillas')->value('id')]);
    $this->actingAs($responsable)->post(route('insumos.store'), [
        'solicitante' => 'manual', 'solicitante_nombre' => 'Vecino de ventanilla', 'solicitante_rut' => '12345678-5',
        'direccion_contacto' => 'Calle 123', 'telefono_contacto' => '912345678',
        'fecha_comprometida' => today()->addWeek()->toDateString(),
        'lineas' => [['item_inventario_id' => $item->id, 'cantidad' => 2]],
    ])->assertSessionHasNoErrors();
    $solicitud = SolicitudInsumo::sole();
    expect($solicitud->socio_id)->toBeNull()->and($solicitud->grupo_id)->toBeNull()->and($solicitud->canal)->toBe('ventanilla');
    $this->post(route('insumos.aprobar', $solicitud), [
        'responsable_id' => $responsable->id, 'monto' => 0, 'garantia' => 0,
        'fecha_comprometida' => today()->addWeek()->toDateString(),
    ])->assertSessionHasNoErrors();
    expect($solicitud->fresh()->estado)->toBe('aprobado');
    $this->get(route('insumos.show', $solicitud))->assertOk()->assertSee('action="'.route('insumos.entregar', $solicitud).'"', false);
    $this->get(route('insumos.show', [$solicitud, 'consulta' => 'historial']))->assertOk()
        ->assertDontSee('action="'.route('insumos.entregar', $solicitud).'"', false)
        ->assertDontSee('Horarios de atención para retirar insumos');
    $this->assertDatabaseCount('socios', 0);
})->with(['presidente', 'secretario']);

test('actividad privada permite vecino sin ficha y rechaza agrupacion ajena a esa actividad', function () {
    $datos = [
        'actividad_reserva' => 'particular', 'modalidad_solicitante' => 'manual',
        'responsable_nombre' => 'Vecino de ventanilla', 'responsable_rut' => '12345678-5',
        'responsable_direccion' => 'Calle 123', 'responsable_telefono' => '912345678',
        'fecha' => today()->addDays(5)->toDateString(), 'hora_inicio' => '10:00', 'hora_fin' => '11:00', 'valor_arriendo' => 0,
    ];
    $grupo = Grupo::create(['nombre' => 'Agrupación', 'representante_nombre' => 'Representante']);
    $this->actingAs($this->presidenteCorrecciones)->post(route('arriendos.store'), $datos + ['grupo_id' => $grupo->id])->assertSessionHasErrors('grupo_id');
    $this->post(route('arriendos.store'), $datos)->assertSessionHasNoErrors();
    expect(Reserva::sole()->tipo_nombre)->toBe('Actividad privada')->and(Reserva::sole()->socio_id)->toBeNull();
});

test('socio activo conserva solicitud en linea para su agrupacion', function () {
    $vecino = User::factory()->create(['role' => 'vecino']);
    $socio = Socio::create([
        'user_id' => $vecino->id, 'nombre_completo' => 'Representante', 'rut' => '12345678-5',
        'direccion' => 'Calle 123', 'telefono' => '912345678', 'residente_desde' => '2020-01-01', 'estado' => 'activo',
    ]);
    $grupo = Grupo::create(['nombre' => 'Agrupación', 'representante_id' => $socio->id]);
    $this->actingAs($vecino)->post(route('arriendos.store'), [
        'actividad_reserva' => 'grupo', 'grupo_id' => $grupo->id,
        'fecha' => today()->addDays(5)->toDateString(), 'hora_inicio' => '10:00', 'hora_fin' => '11:00',
    ])->assertSessionHasNoErrors();
    expect(Reserva::sole()->grupo_id)->toBe($grupo->id)->and(Reserva::sole()->socio_id)->toBe($socio->id);
});
