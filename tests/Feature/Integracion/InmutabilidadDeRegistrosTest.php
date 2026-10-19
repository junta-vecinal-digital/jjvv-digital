<?php

use App\Models\Auditoria;
use App\Models\Ingreso;
use App\Models\User;

test('la auditoría no se puede modificar ni eliminar', function () {
    $registro = Auditoria::create([
        'evento' => 'creado',
        'auditable_type' => User::class,
        'auditable_id' => 1,
        'cambios' => [],
    ]);

    expect(fn () => $registro->update(['evento' => 'modificado']))->toThrow(LogicException::class);
    expect(fn () => $registro->delete())->toThrow(LogicException::class);
});

test('un ingreso no se puede eliminar', function () {
    $ingreso = Ingreso::create([
        'concepto' => 'otros',
        'fecha_pago' => now()->toDateString(),
        'monto' => 1000,
        'medio_pago' => 'efectivo',
        'observaciones' => 'Prueba',
        'registrado_por' => User::factory()->create()->id,
    ]);

    expect(fn () => $ingreso->delete())->toThrow(LogicException::class);
});