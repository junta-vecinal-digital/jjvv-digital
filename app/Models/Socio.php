<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Socio extends Model
{
    protected $fillable = [
        'user_id', 'nombre_completo', 'rut', 'email', 'direccion', 'telefono',
        'residente_desde', 'carnet_path', 'boleta_path', 'estado',
        'observaciones', 'fecha_ingreso', 'revisado_por',
    ];

    protected $casts = [
        'residente_desde' => 'date',
        'fecha_ingreso' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }

    public function cita(): HasOne
    {
        return $this->hasOne(Cita::class);
    }
}