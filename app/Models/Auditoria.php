<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Auditoria extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'auditorias';

    protected $guarded = [];


        protected static function booted(): void
    {
        // Trazabilidad: no se edita ni se elimina (RFS-86).
        static::updating(fn () => throw new \LogicException('La auditoría no se puede modificar.'));
        static::deleting(fn () => throw new \LogicException('La auditoría no se puede eliminar.'));
    }


    /** Filtros disponibles en la pantalla de auditoría (RFS-87). */
    public const MODELOS = [
        'socio' => Socio::class,
        'certificado' => Certificado::class,
        'usuario' => User::class,
        'finanzas' => [Ingreso::class, Egreso::class, Devolucion::class, Garantia::class],
    ];

    protected function casts(): array
    {
        return ['cambios' => 'array'];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function etiquetaRegistro(): string
    {
        return class_basename($this->auditable_type).' #'.$this->auditable_id;
    }
}
