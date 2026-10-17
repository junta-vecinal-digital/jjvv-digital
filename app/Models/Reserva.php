<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/** Reserva de la sede y validación de horarios y superposiciones (RFS-44 a RFS-48). */
class Reserva extends Model
{
    protected $fillable = [
        'tipo', 'estado', 'inicio', 'fin', 'motivo',
        'grupo_id', 'socio_id', 'horario_fijo_id', 'creado_por',
        'valor_arriendo', 'valor_garantia', 'vence_en', 'validado_en',
        'validado_por', 'cancelada_en', 'cancelada_por',
        'comprobante_arriendo_path',
    ];

    protected $casts = [
        'inicio' => 'datetime',
        'fin' => 'datetime',
        'vence_en' => 'datetime',
        'validado_en' => 'datetime',
        'cancelada_en' => 'datetime',
    ];

    public const TIPOS = [
        'grupo' => 'Actividad de grupo',
        'particular' => 'Actividad particular',
        'arriendo' => 'Arriendo',
        'actividad' => 'Actividad de la junta',
    ];

    /** Estados que ocupan el horario. 'cancelada' (3b) lo libera. */
    public const ESTADOS_OCUPAN = ['confirmada', 'pendiente'];

    public const ESTADOS = [
        'pendiente' => 'Pendiente de validación',
        'confirmada' => 'Confirmada',
        'cancelada' => 'Cancelada',
        'vencida' => 'Plazo de validación vencido',
    ];

    public function scopeOcupan(Builder $query): Builder
    {
        return $query->whereIn('estado', self::ESTADOS_OCUPAN)
            ->where(fn (Builder $q) => $q->whereNull('vence_en')
                ->orWhere('estado', '!=', 'pendiente')->orWhere('vence_en', '>', now()));
    }

    /** El plazo libera el horario también cuando el scheduler no está ejecutándose. */
    public static function liberarVencidos(): int
    {
        return DB::transaction(function () {
            DB::table('parametros')->where('clave', 'sede_hora_inicio')->update(['valor' => DB::raw('valor')]);

            return static::where('tipo', 'arriendo')->where('estado', 'pendiente')
                ->where('vence_en', '<=', now())->update(['estado' => 'vencida']);
        });
    }

    public function garantia(): HasOne
    {
        return $this->hasOne(Garantia::class);
    }

    public function ingresos(): HasMany
    {
        return $this->hasMany(Ingreso::class);
    }

    /**
     * RFS-45: valida horario de la sede y bloques. Recibe horas "H:i".
     * Devuelve el mensaje de error, o null si está bien.
     */
    public static function errorDeBloques(string $horaInicio, string $horaFin): ?string
    {
        $aperturaTxt = substr(Parametro::valor('sede_hora_inicio'), 0, 5);
        $cierreTxt = substr(Parametro::valor('sede_hora_fin'), 0, 5);
        $bloque = Parametro::entero('sede_duracion_bloque_min');

        $apertura = self::minutos($aperturaTxt);
        $cierre = self::minutos($cierreTxt);
        $ini = self::minutos($horaInicio);
        $fin = self::minutos($horaFin);

        if ($fin <= $ini) {
            return 'La hora de término debe ser posterior a la de inicio.';
        }

        if ($ini < $apertura || $fin > $cierre) {
            return "La reserva debe estar dentro del horario de la sede ({$aperturaTxt} a {$cierreTxt}).";
        }

        if (($ini - $apertura) % $bloque !== 0 || ($fin - $ini) % $bloque !== 0) {
            return "Las reservas se hacen en bloques de {$bloque} minutos, contados desde las {$aperturaTxt}.";
        }

        return null;
    }

    /**
     * RFS-46 y RFS-48: valida una reserva puntual (mismo día, bloques y superposición con
     * otras reservas y con horarios fijos de grupo). Devuelve el error o null.
     * $ignorarHorarioFijoId se usa al generar las reservas de ese mismo horario fijo.
     */
    public static function errorDeConflicto(Carbon $inicio, Carbon $fin, ?int $ignorarHorarioFijoId = null): ?string
    {
        if (! $inicio->isSameDay($fin)) {
            return 'La reserva debe quedar dentro de un mismo día.';
        }

        if ($error = self::errorDeBloques($inicio->format('H:i'), $fin->format('H:i'))) {
            return $error;
        }

        $ocupada = static::ocupan()
            ->where('inicio', '<', $fin)
            ->where('fin', '>', $inicio)
            ->exists();

        if ($ocupada) {
            return 'Ese horario ya está ocupado por otra reserva.';
        }

        $choqueFijo = HorarioFijo::where('dia_semana', $inicio->isoWeekday())
            ->when($ignorarHorarioFijoId, fn ($q, $id) => $q->where('id', '!=', $id))
            ->whereDoesntHave('reservas', fn ($q) => $q->where('estado', 'cancelada')
                ->whereDate('inicio', $inicio->toDateString()))
            ->where('hora_inicio', '<', $fin->format('H:i'))
            ->where('hora_fin', '>', $inicio->format('H:i'))
            ->exists();

        return $choqueFijo ? 'Ese horario está ocupado por el horario fijo de un grupo.' : null;
    }

    private static function minutos(string $hora): int
    {
        [$h, $m] = array_map('intval', explode(':', substr($hora, 0, 5)));

        return $h * 60 + $m;
    }

    /** Horas de inicio y de término ("H:i") de cada bloque entre la apertura y el cierre de la sede. */
    public static function bloquesDeLaSede(): array
    {
        $bloque = Parametro::entero('sede_duracion_bloque_min');
        $cursor = Carbon::parse(substr(Parametro::valor('sede_hora_inicio'), 0, 5));
        $cierre = Carbon::parse(substr(Parametro::valor('sede_hora_fin'), 0, 5));
        $inicios = [];
        $fines = [];

        while ($cursor->copy()->addMinutes($bloque)->lte($cierre)) {
            $inicios[] = $cursor->format('H:i');
            $cursor->addMinutes($bloque);
            $fines[] = $cursor->format('H:i');
        }

        return ['inicios' => $inicios, 'fines' => $fines];
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    public function socio(): BelongsTo
    {
        return $this->belongsTo(Socio::class);
    }

    public function horarioFijo(): BelongsTo
    {
        return $this->belongsTo(HorarioFijo::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}
