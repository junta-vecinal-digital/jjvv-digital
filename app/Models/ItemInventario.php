<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemInventario extends Model
{
    use Auditable;

    protected $table = 'items_inventario';

    protected $fillable = ['nombre', 'cantidad', 'registrado_por', 'tipo_insumo_id'];

    protected $casts = ['cantidad' => 'integer'];

    public function tipoInsumo(): BelongsTo
    {
        return $this->belongsTo(TipoInsumo::class);
    }

    public function prestamos(): HasMany
    {
        return $this->hasMany(Prestamo::class);
    }

    public function scopeConExistencias(Builder $query): Builder
    {
        return $query->withSum(['prestamos as prestadas' => fn ($q) => $q->whereNull('fecha_devolucion')], 'cantidad')
            ->withSum(['prestamos as fuera_servicio' => fn ($q) => $q->whereNotNull('fecha_devolucion')], 'cantidad_danada')
            ->withSum(['solicitudes as reservadas' => fn ($q) => $q->whereHas('solicitud', fn ($s) => $s->where('estado', 'aprobado'))], 'cantidad');
    }

    public function solicitudes(): HasMany
    {
        return $this->hasMany(SolicitudInsumoLinea::class, 'item_inventario_id');
    }

    public function disponibles(): int
    {
        $prestadas = array_key_exists('prestadas', $this->getAttributes())
            ? $this->prestadas : $this->prestamos()->whereNull('fecha_devolucion')->sum('cantidad');
        $danadas = array_key_exists('fuera_servicio', $this->getAttributes())
            ? $this->fuera_servicio : $this->prestamos()->whereNotNull('fecha_devolucion')->sum('cantidad_danada');

        $reservadas = array_key_exists('reservadas', $this->getAttributes()) ? $this->reservadas
            : $this->solicitudes()->whereHas('solicitud', fn ($q) => $q->where('estado', 'aprobado'))->sum('cantidad');

        return $this->cantidad - (int) $prestadas - (int) $danadas - (int) $reservadas;
    }
}
