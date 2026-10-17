<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Registro de proyectos postulados fuera del sistema (RFS-76 a RFS-78). */
class Proyecto extends Model
{
    use Auditable;

    protected $fillable = ['titulo', 'organismo', 'fecha_postulacion', 'estado', 'monto', 'postulado_por', 'postulador_id', 'registrado_por'];

    protected $casts = ['fecha_postulacion' => 'date', 'monto' => 'integer'];

    public const ESTADOS = ['postulado' => 'Postulado', 'adjudicado' => 'Adjudicado', 'rechazado' => 'Rechazado'];

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
