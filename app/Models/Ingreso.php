<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ingreso extends Model
{
    use Auditable;

    /** Conceptos de ingreso (RFS-58). */
    public const CONCEPTOS = [
        'arriendo' => 'Aporte por uso de sede (histórico)',
        'aporte_reserva' => 'Aporte por reserva de sede',
        'prestamo_insumos' => 'Aporte por préstamo de insumos',
        'cuota_socios' => 'Aporte de socios',
        'aporte_grupos' => 'Aporte de agrupaciones',
        'certificados' => 'Aportes por certificados',
        'otros' => 'Otros aportes',
    ];

    public const TIPOS_CUOTA = [
        'ordinaria' => 'Ordinaria',
        'extraordinaria' => 'Extraordinaria',
    ];

    public const MEDIOS = [
        'efectivo' => 'Efectivo',
        'transferencia' => 'Transferencia',
    ];

    protected $fillable = [
        'concepto', 'fecha_pago', 'monto', 'medio_pago',
        'talonario', 'numero_transaccion', 'observaciones',
        'tipo_cuota', 'certificado_id', 'registrado_por', 'reserva_id', 'garantia_id',
        'solicitud_insumo_id', 'comprobante_path',
    ];

    protected function casts(): array
    {
        return [
            'fecha_pago' => 'date',
            'anulado_en' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Los movimientos no se borran; se anulan con motivo (RFS-67).
        static::deleting(fn () => throw new \LogicException('Los ingresos no se pueden eliminar.'));
    }

    public function scopeVigentes(Builder $query): Builder
    {
        return $query->whereNull('anulado_en');
    }

    /** Saldo de caja (RFS-68): ingresos menos egresos menos devoluciones, sin contar lo anulado. */
    public static function saldoCaja(): int
    {
        return (int) static::vigentes()->sum('monto')
            - (int) Egreso::vigentes()->sum('monto')
            - (int) Devolucion::sum('monto');
    }

    public function estaAnulado(): bool
    {
        return $this->anulado_en !== null;
    }

    /** Anulación con motivo obligatorio. Queda registrada en la auditoría por el trait Auditable. */
    public function anular(string $motivo, User $por): void
    {
        $this->forceFill([
            'anulado_en' => now(),
            'motivo_anulacion' => $motivo,
            'anulado_por' => $por->id,
        ])->save();
    }

    public function devolucion(): HasOne
    {
        return $this->hasOne(Devolucion::class);
    }

    /** Solo cuotas de socios y aportes de grupos, vigentes y sin devolución previa (RFS-66). */
    public function puedeDevolverse(): bool
    {
        return in_array($this->concepto, ['cuota_socios', 'aporte_grupos'], true)
            && ! $this->estaAnulado()
            && ! $this->devolucion()->exists();
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function certificado(): BelongsTo
    {
        return $this->belongsTo(Certificado::class);
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }

    public function garantia(): BelongsTo
    {
        return $this->belongsTo(Garantia::class);
    }

    public function solicitudInsumo(): BelongsTo
    {
        return $this->belongsTo(SolicitudInsumo::class);
    }
}
