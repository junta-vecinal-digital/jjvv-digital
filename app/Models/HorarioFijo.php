<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Horario fijo semanal de un grupo: reserva recurrente (RFS-47, RFS-49). */
class HorarioFijo extends Model
{
    protected $table = 'horarios_fijos';

    protected $fillable = ['grupo_id', 'dia_semana', 'hora_inicio', 'hora_fin'];

    public const DIAS = [
        1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves',
        5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo',
    ];

    public function nombreDia(): string
    {
        return self::DIAS[$this->dia_semana] ?? '';
    }

    /** Valida un horario fijo: sede, bloques y superposición con otros horarios fijos (RFS-45, RFS-46). */
    public static function errorDeConflicto(int $dia, string $horaInicio, string $horaFin, ?int $ignorarId = null): ?string
    {
        if ($error = Reserva::errorDeBloques($horaInicio, $horaFin)) {
            return $error;
        }

        $choca = static::where('dia_semana', $dia)
            ->when($ignorarId, fn ($q, $id) => $q->where('id', '!=', $id))
            ->where('hora_inicio', '<', $horaFin)
            ->where('hora_fin', '>', $horaInicio)
            ->exists();

        if ($choca) {
            return 'Se superpone con otro horario fijo.';
        }

        $reservas = Reserva::ocupan()
            ->where('fin', '>', now())->get();

        foreach ($reservas as $reserva) {
            if ($reserva->inicio->isoWeekday() !== $dia) {
                continue;
            }

            $inicio = $reserva->inicio->format('H:i');
            $fin = $reserva->fin->format('H:i');

            // Guardar el mismo horario no modifica sus reservas ya generadas.
            if ($ignorarId !== null && (int) $reserva->horario_fijo_id === $ignorarId
                && $inicio === $horaInicio && $fin === $horaFin) {
                continue;
            }

            if ($inicio < $horaFin && $fin > $horaInicio) {
                return 'Se superpone con una reserva del '.$reserva->inicio->format('d-m-Y')
                    .'. Las reservas ya generadas se mantienen; elige otro horario.';
            }
        }

        return null;
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }
}
