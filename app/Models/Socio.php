<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Socio extends Model
{
    use Auditable;

    protected $fillable = [
        'user_id', 'nombre_completo', 'rut', 'email', 'direccion', 'telefono',
        'residente_desde', 'carnet_path', 'boleta_path', 'estado',
        'observaciones', 'fecha_ingreso', 'revisado_por',
        'motivo_baja', 'fecha_baja',
    ];

    protected $casts = [
        'residente_desde' => 'date',
        'fecha_ingreso' => 'date',
        'fecha_baja' => 'date',
    ];

    public const ETIQUETAS = [
        'pendiente' => 'Pendiente de revisión',
        'por_firmar' => 'Pendiente de firma',
        'activo' => 'Socio activo',
        'rechazado' => 'Rechazada',
        'inactivo' => 'Inactivo',
    ];

    /** Cambios de estado permitidos: estado actual => estados a los que puede pasar. */
    public const TRANSICIONES = [
        'pendiente' => ['por_firmar', 'rechazado'],
        'por_firmar' => ['activo', 'rechazado'],
        'rechazado' => ['pendiente'],
        'activo' => ['inactivo'],
        'inactivo' => ['activo'],
    ];

    public function puedePasarA(string $estado): bool
    {
        return in_array($estado, self::TRANSICIONES[$this->estado] ?? [], true);
    }

    /** Etiqueta informativa; no modifica rol, estado ni permisos de la ficha. */
    public function esRepresentante(): bool
    {
        return Grupo::where('representante_id', $this->id)
            ->when($this->user_id, fn ($q) => $q->orWhere('representante_usuario_id', $this->user_id))->exists();
    }

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
