<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prestamo extends Model
{
    use Auditable;

    protected $fillable = [
        'item_inventario_id', 'cantidad', 'grupo_id', 'destinatario_nombre',
        'responsable_nombre', 'responsable_telefono', 'fecha_solicitud', 'fecha_prestamo',
        'estado_salida', 'fecha_devolucion', 'estado_regreso', 'cantidad_danada',
        'registrado_por', 'devuelto_por',
        'solicitud_insumo_id',
    ];

    protected $casts = [
        'cantidad' => 'integer', 'cantidad_danada' => 'integer', 'fecha_solicitud' => 'date',
        'fecha_prestamo' => 'date', 'fecha_devolucion' => 'date',
    ];

    public const CONDICIONES = ['bueno' => 'Buen estado', 'desgaste' => 'Con desgaste', 'danado' => 'Con daños'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(ItemInventario::class, 'item_inventario_id');
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function receptor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'devuelto_por');
    }
}
