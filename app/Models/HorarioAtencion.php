<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class HorarioAtencion extends Model
{
    use Auditable;

    protected $table = 'horarios_atencion';

    protected $fillable = ['dia_semana', 'hora_inicio', 'hora_fin'];

    public const DIAS = [
        1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves',
        5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo',
    ];
}
