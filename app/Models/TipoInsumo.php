<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoInsumo extends Model
{
    use Auditable;

    protected $table = 'tipos_insumo';

    protected $fillable = ['nombre', 'categoria'];

    public const CATEGORIAS = [
        'mobiliario' => 'Mobiliario y eventos', 'equipamiento' => 'Equipamiento',
        'herramientas' => 'Herramientas', 'ayudas_tecnicas' => 'Ayudas técnicas en comodato',
    ];

    public function existencias(): HasMany
    {
        return $this->hasMany(ItemInventario::class);
    }
}
