<?php

use App\Models\Socio;
use App\Models\User;

function crearUsuarioPermisos(string $rol): User
{
    $usuario = User::factory()->create();
    $usuario->forceFill(['role' => $rol])->save();

    return $usuario->fresh();
}

function crearFichaPermisos(User $usuario, string $estado, string $rut): void
{
    (new Socio)->forceFill([
        'user_id' => $usuario->id,
        'nombre_completo' => $usuario->name,
        'rut' => $rut,
        'direccion' => 'Calle Falsa 123',
        'telefono' => '111',
        'residente_desde' => now()->subYear()->toDateString(),
        'estado' => $estado,
    ])->save();
}

$modulos = [
    'usuarios' => 'usuarios.index',
    'socios' => 'socios.index',
    'certificados (gestión)' => 'admin.certificados.index',
    'agenda' => 'agenda.index',
    'auditoría' => 'auditoria.index',
    'finanzas' => 'ingresos.index',
    'parámetros' => 'parametros.index',
    'actas' => 'actas.index',
    'proyectos' => 'proyectos.index',
    'inventario' => 'inventario.index',
];

it('limita cada rol a sus módulos', function (string $rol, array $permitidos) use ($modulos) {
    $this->withoutVite();
    $this->actingAs(crearUsuarioPermisos($rol));

    foreach ($modulos as $nombre => $ruta) {
        $respuesta = $this->get(route($ruta));

        in_array($nombre, $permitidos, true)
            ? $respuesta->assertOk()
            : $respuesta->assertForbidden();
    }
})->with([
    'administrador' => ['admin', ['usuarios', 'parámetros', 'proyectos']],
    'presidente' => ['presidente', ['socios', 'certificados (gestión)', 'agenda', 'auditoría', 'finanzas', 'actas', 'proyectos', 'inventario']],
    'secretario' => ['secretario', ['socios', 'certificados (gestión)', 'agenda', 'actas', 'proyectos', 'inventario']],
    'tesorero' => ['tesorero', ['finanzas', 'actas', 'proyectos']],
    'vecino' => ['vecino', ['proyectos']],
]);

it('solo un socio con ficha activa accede a los certificados en línea', function () {
    $this->withoutVite();

    $sinFicha = crearUsuarioPermisos('vecino');
    $this->actingAs($sinFicha)->get(route('certificados.index'))->assertForbidden();

    $pendiente = crearUsuarioPermisos('vecino');
    crearFichaPermisos($pendiente, 'pendiente', '12345678-5');
    $this->actingAs($pendiente)->get(route('certificados.index'))->assertForbidden();

    $activo = crearUsuarioPermisos('vecino');
    crearFichaPermisos($activo, 'activo', '11111111-1');
    $this->actingAs($activo)->get(route('certificados.index'))->assertOk();
});

it('autoriza las acciones de socio por la ficha y no por el rol', function () {
    $this->withoutVite();

    $secretario = crearUsuarioPermisos('secretario');
    crearFichaPermisos($secretario, 'activo', '12345678-5');

    $this->actingAs($secretario)->get(route('certificados.index'))->assertOk();
});
