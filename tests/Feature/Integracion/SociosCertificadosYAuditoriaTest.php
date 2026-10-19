<?php

use App\Models\Auditoria;
use App\Models\Certificado;
use App\Models\Socio;
use App\Models\User;

function usuarioConRol(string $rol): User
{
    $usuario = User::factory()->create();
    $usuario->forceFill(['role' => $rol])->save();

    return $usuario->fresh();
}

function certificadoPendiente(array $extra = []): Certificado
{
    return Certificado::create($extra + [
        'tipo' => 'residencia',
        'nombre_solicitante' => 'Juan Pérez',
        'rut_solicitante' => '12345678-5',
        'direccion' => 'Pasaje 1 #123',
        'dirigido_a' => 'Banco Estado',
    ]);
}

// ---------- Certificados ----------

it('asigna folios correlativos por año al emitir', function () {
    $secretario = usuarioConRol('secretario');
    $a = certificadoPendiente();
    $b = certificadoPendiente();

    $a->emitir($secretario);
    $b->emitir($secretario);

    expect($a->folio)->toBe(now()->year.'-0001')
        ->and($b->folio)->toBe(now()->year.'-0002')
        ->and($a->estado)->toBe('emitido')
        ->and($a->otorgado_por)->toBe($secretario->id);
});

it('no consume folio al rechazar una solicitud', function () {
    $presidente = usuarioConRol('presidente');
    $rechazada = certificadoPendiente();
    $rechazada->forceFill(['estado' => 'rechazado'])->save();

    $emitida = certificadoPendiente();
    $emitida->emitir($presidente);

    expect($emitida->folio)->toBe(now()->year.'-0001');
});

it('verifica públicamente un certificado vigente', function () {
    $c = certificadoPendiente();
    $c->emitir(usuarioConRol('presidente'));

    $this->get(route('verificar.mostrar', $c->codigo_verificacion))
        ->assertOk()
        ->assertSee('Certificado válido');
});

it('no revela solicitudes que aún no fueron emitidas', function () {
    $c = certificadoPendiente();

    $this->get(route('verificar.mostrar', $c->codigo_verificacion))
        ->assertOk()
        ->assertSee('No encontramos');
});

it('informa certificados vencidos y anulados', function () {
    $presidente = usuarioConRol('presidente');

    $vencido = certificadoPendiente();
    $vencido->emitir($presidente);
    $vencido->forceFill(['fecha_vencimiento' => now()->subDay()->toDateString()])->save();

    $anulado = certificadoPendiente();
    $anulado->emitir($presidente);
    $anulado->forceFill(['estado' => 'anulado'])->save();

    $this->get(route('verificar.mostrar', $vencido->codigo_verificacion))->assertSee('Certificado vencido');
    $this->get(route('verificar.mostrar', $anulado->codigo_verificacion))->assertSee('Certificado anulado');
});

it('impide que un vecino entre a la gestión de certificados', function () {
    $this->actingAs(usuarioConRol('vecino'))
        ->get(route('admin.certificados.index'))
        ->assertForbidden();
});

it('exige verificar carnet y boleta para emitir un certificado de residencia', function () {
    $secretario = usuarioConRol('secretario');

    $datos = [
        'tipo' => 'residencia',
        'nombre_solicitante' => 'Juan Pérez',
        'rut_solicitante' => '12.345.678-5',
        'direccion' => 'Pasaje 1 #123',
        'dirigido_a' => 'Municipalidad',
        'medio_pago' => 'efectivo',
        'talonario' => 'T-1',
        'pago_verificado' => '1',
        'residencia_6_meses' => '1',
    ];

    $this->actingAs($secretario)
        ->post(route('admin.certificados.store'), $datos)
        ->assertSessionHasErrors('documentos_verificados');

    $this->actingAs($secretario)
        ->post(route('admin.certificados.store'), $datos + ['documentos_verificados' => '1'])
        ->assertRedirect();

    $this->assertDatabaseHas('certificados', ['rut_solicitante' => '12345678-5', 'estado' => 'emitido']);
});

// ---------- Socios (dirigentes) ----------

it('permite a un dirigente registrar un socio en ventanilla', function () {
    $this->actingAs(usuarioConRol('secretario'))
        ->post(route('socios.guardar'), [
            'nombre_completo' => 'María González',
            'rut' => '12.345.678-5',
            'direccion' => 'Calle Falsa 123',
            'telefono' => '+56912345678',
            'residente_desde' => now()->subYear()->toDateString(),
            'estado' => 'activo',
            'fecha_ingreso' => now()->toDateString(),
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('socios', ['rut' => '12345678-5', 'estado' => 'activo']);
});

it('rechaza un RUT con dígito verificador incorrecto', function () {
    $this->actingAs(usuarioConRol('secretario'))
        ->post(route('socios.guardar'), [
            'nombre_completo' => 'María González',
            'rut' => '12345678-9',
            'direccion' => 'Calle Falsa 123',
            'telefono' => '+56912345678',
            'residente_desde' => now()->subYear()->toDateString(),
            'estado' => 'por_firmar',
        ])
        ->assertSessionHasErrors('rut');
});

it('no deja que un vecino registre socios por la vía de ventanilla', function () {
    $this->actingAs(usuarioConRol('vecino'))
        ->get(route('socios.crear'))
        ->assertForbidden();
});

// ---------- Auditoría (RFS-04) ----------

it('registra autor, fecha y cambios al modificar un socio', function () {
    $secretario = usuarioConRol('secretario');

    $socio = Socio::create([
        'nombre_completo' => 'María González',
        'rut' => '12345678-5',
        'direccion' => 'Calle Falsa 123',
        'telefono' => '111',
        'residente_desde' => now()->subYear()->toDateString(),
    ]);

    $this->actingAs($secretario);
    $socio->update(['telefono' => '999']);

    $registro = Auditoria::where('auditable_type', Socio::class)->where('evento', 'modificado')->latest('id')->first();

    expect($registro->user_id)->toBe($secretario->id)
        ->and($registro->created_at)->not->toBeNull()
        ->and($registro->cambios['telefono'])->toBe(['antes' => '111', 'despues' => '999']);
});

it('oculta contraseñas en la auditoría de usuarios', function () {
    $admin = usuarioConRol('admin');
    $this->actingAs($admin);

    $admin->forceFill(['password' => 'nueva-clave-segura-123'])->save();

    $registro = Auditoria::where('auditable_type', User::class)->where('auditable_id', $admin->id)->latest('id')->first();

    expect(json_encode($registro->cambios))->not->toContain('nueva-clave-segura-123');
});
