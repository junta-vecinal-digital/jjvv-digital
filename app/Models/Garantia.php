<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Garantía recibida y validada: se conserva separada de los ingresos (RFS-62). */
class Garantia extends Model
{
    use Auditable;

    public const ESTADOS = [
        'recibida' => 'Recibida',
        'por_devolver' => 'Por devolver',
        'devuelta' => 'Devuelta',
        'retenida' => 'Retenida por daños',
    ];

    protected $fillable = [
        'reserva_id', 'monto', 'estado', 'recibida_en', 'validado_por',
        'solicitud_insumo_id',
        'registrada_en', 'registrada_por', 'fecha_pago', 'medio_pago', 'talonario',
        'fecha_resolucion', 'resuelta_en', 'resuelta_por', 'monto_devuelto', 'monto_retenido', 'motivo_retencion',
    ];

    protected static function booted(): void
    {
        static::deleting(fn () => throw new \LogicException('Las garantías no se eliminan; se conserva su trazabilidad.'));
    }

    protected function casts(): array
    {
        return [
            'recibida_en' => 'datetime', 'registrada_en' => 'datetime', 'fecha_pago' => 'date',
            'fecha_resolucion' => 'date', 'resuelta_en' => 'datetime',
        ];
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }

    public function ingresos(): HasMany
    {
        return $this->hasMany(Ingreso::class);
    }

    public function solicitudInsumo(): BelongsTo
    {
        return $this->belongsTo(SolicitudInsumo::class);
    }
}
