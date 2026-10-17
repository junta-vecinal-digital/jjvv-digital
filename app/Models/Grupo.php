<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grupo extends Model
{
    use Auditable;

    protected $fillable = [
        'nombre', 'horarios_uso', 'representante_id',
        'contacto_nombre', 'contacto_correo', 'contacto_telefono', 'contacto_direccion',
        'representante_usuario_id', 'representante_nombre', 'representante_correo',
        'representante_telefono', 'representante_direccion',
    ];

    public function representante(): BelongsTo
    {
        return $this->belongsTo(Socio::class, 'representante_id');
    }

    public function horariosFijos(): HasMany
    {
        return $this->hasMany(HorarioFijo::class);
    }

    public function datosRepresentante(): array
    {
        if (filled($this->representante_nombre)) {
            return [
                'nombre' => $this->representante_nombre, 'correo' => $this->representante_correo,
                'telefono' => $this->representante_telefono, 'direccion' => $this->representante_direccion,
            ];
        }

        return [
            'nombre' => $this->representante?->nombre_completo, 'correo' => $this->representante?->email,
            'telefono' => $this->representante?->telefono, 'direccion' => $this->representante?->direccion,
        ];
    }

    /** La designación no depende de una ficha o cuenta vigente (RFS-39/41). */
    public function tieneRepresentanteVigente(): bool
    {
        return filled($this->datosRepresentante()['nombre']);
    }
}
