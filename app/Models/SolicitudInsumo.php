<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SolicitudInsumo extends Model
{
    use Auditable;

    protected $table = 'solicitudes_insumos';
    protected $guarded = ['id'];
    protected $casts = [
        'fecha_comprometida' => 'date', 'fecha_entrega' => 'date', 'fecha_devolucion' => 'date', 'aprobado_en' => 'datetime',
        'monto' => 'integer', 'garantia' => 'integer',
        'pago_registrado_en' => 'datetime', 'pago_validado_en' => 'datetime', 'fecha_pago' => 'date',
    ];

    public const ESTADOS = ['pendiente' => 'Pendiente', 'aprobado' => 'Aprobado · unidades reservadas', 'entregado' => 'Entregado', 'devuelto' => 'Devuelto'];

    public function lineas(): HasMany
    {
        return $this->hasMany(SolicitudInsumoLinea::class);
    }

    public function atrasado(): bool
    {
        return $this->estado === 'entregado' && $this->fecha_comprometida->lt(today());
    }

    public function requierePago(): bool
    {
        return $this->monto > 0 || $this->garantia > 0;
    }

    public function puedeEntregarse(): bool
    {
        return $this->estado === 'aprobado' && (! $this->requierePago() || $this->pago_validado_en !== null);
    }

    public function ingresos(): HasMany
    {
        return $this->hasMany(Ingreso::class);
    }

    public function garantiaRecibida(): HasOne
    {
        return $this->hasOne(Garantia::class);
    }
}
