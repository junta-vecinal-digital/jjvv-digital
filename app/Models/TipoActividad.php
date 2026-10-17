<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class TipoActividad extends Model
{
    use Auditable;

    protected $table = 'tipos_actividad';

    protected $fillable = ['clave', 'nombre', 'activo'];

    protected $casts = ['activo' => 'boolean'];
}
