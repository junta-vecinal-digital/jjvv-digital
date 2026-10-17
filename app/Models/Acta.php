<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Actas de asamblea y directiva (RFS-73 a RFS-75). */
class Acta extends Model
{
    use Auditable;

    protected $fillable = ['fecha', 'tipo', 'acuerdos', 'escaneo_path', 'registrado_por'];

    protected $casts = ['fecha' => 'date'];

    public const TIPOS = ['asamblea' => 'Asamblea', 'directiva' => 'Directiva'];

    public function puedeConsultar(User $usuario): bool
    {
        return $usuario->esDirigente()
            || ($this->tipo === 'asamblea' && $usuario->esSocioActivo());
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
