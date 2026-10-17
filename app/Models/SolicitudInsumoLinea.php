<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudInsumoLinea extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = ['cantidad' => 'integer'];

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudInsumo::class, 'solicitud_insumo_id');
    }

    public function prestamo(): BelongsTo
    {
        return $this->belongsTo(Prestamo::class);
    }
}
