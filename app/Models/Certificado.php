<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Parametro;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Certificado extends Model
{
    use Auditable;

    public const TIPOS = [
        'residencia' => 'Certificado de residencia',
        'socio' => 'Certificado de socio',
    ];

    public const ESTADOS = [
        'solicitado' => 'Pendiente de revisión',
        'emitido' => 'Emitido',
        'rechazado' => 'Rechazado',
        'anulado' => 'Anulado',
    ];

    protected $attributes = ['estado' => 'solicitado'];

    protected $fillable = [
        'tipo', 'user_id', 'socio_id', 'nombre_solicitante', 'rut_solicitante',
        'direccion', 'dirigido_a', 'aporte', 'documentos_verificados', 'observaciones',
        'numero_transaccion', 'comprobante_path', 'medio_pago', 'talonario',
        'solo_impresion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_vencimiento' => 'date',
            'fecha_retiro' => 'date',
            'descarga_disponible_hasta' => 'datetime',
            'documentos_verificados' => 'boolean',
            'solo_impresion' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Certificado $certificado) {
            $certificado->codigo_verificacion ??= self::generarCodigo();
        });
    }

    /** Código aleatorio sin caracteres ambiguos (0/O, 1/I) para verificar el certificado en línea. */
    public static function generarCodigo(): string
    {
        $alfabeto = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $codigo = '';
            for ($i = 0; $i < 10; $i++) {
                $codigo .= $alfabeto[random_int(0, strlen($alfabeto) - 1)];
            }
        } while (static::where('codigo_verificacion', $codigo)->exists());

        return $codigo;
    }

    /** Incluye certificados antiguos de ventanilla sin cuenta guardada, ligados a la ficha del usuario. */
    public function scopeDelUsuario(Builder $query, User $usuario): Builder
    {
        $socioId = $usuario->socio?->id;

        return $query->where('solo_impresion', false)->where(function (Builder $consulta) use ($usuario, $socioId) {
            $consulta->where('user_id', $usuario->id);

            if ($socioId !== null) {
                $consulta->orWhere(fn (Builder $q) => $q->whereNull('user_id')->where('socio_id', $socioId));
            }
        });
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function socio(): BelongsTo
    {
        return $this->belongsTo(Socio::class);
    }

    public function otorgante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'otorgado_por');
    }

    public function descargas(): HasMany
    {
        return $this->hasMany(DescargaCertificado::class);
    }

    public function ingresos(): HasMany
    {
        return $this->hasMany(Ingreso::class);
    }

    /** El certificado de socio no tiene plazo (RFS-20); el de residencia sí (RFS-24). */
    public function puedeDescargarse(): bool
    {
        if ($this->estado !== 'emitido' || $this->solo_impresion) {
            return false;
        }

        if ($this->tipo === 'socio') {
            return true;
        }

        return $this->descarga_disponible_hasta?->isFuture() ?? false;
    }

    /** Registra el pago de la solicitud como ingreso, sin requerir acceso a finanzas (RFS-37). */
    public function registrarIngreso(User $por): void
    {
        $aporte = $this->aporte ?? Parametro::entero('certificado_residencia_valor');
        if ($this->tipo !== 'residencia' || $aporte <= 0 || $this->ingresos()->exists()) {
            return;
        }

        Ingreso::create([
            'concepto' => 'certificados',
            'fecha_pago' => now()->toDateString(),
            'monto' => $aporte,
            'medio_pago' => $this->medio_pago ?? 'transferencia',
            'talonario' => $this->talonario,
            'numero_transaccion' => $this->numero_transaccion,
            'certificado_id' => $this->id,
            'registrado_por' => $por->id,
        ]);
    }

    public function etiquetaTipo(): string
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }

    public function etiquetaEstado(): string
    {
        return self::ESTADOS[$this->estado] ?? $this->estado;
    }

    public function codigoFormateado(): string
    {
        return substr($this->codigo_verificacion, 0, 5).'-'.substr($this->codigo_verificacion, 5);
    }

    public function urlVerificacion(): string
    {
        return route('verificar.mostrar', $this->codigo_verificacion);
    }

    /** vigente | vencido | anulado | null (si aún no tiene validez pública). */
    public function estadoPublico(): ?string
    {
        return match ($this->estado) {
            'anulado' => 'anulado',
            'emitido' => $this->fecha_vencimiento->lt(today()) ? 'vencido' : 'vigente',
            default => null,
        };
    }

    /**
     * Asigna folio correlativo por año ("2026-0001"), fechas y emisor.
     * La restricción única (anio, correlativo) evita folios repetidos si dos
     * dirigentes emiten a la vez; en ese caso se reintenta.
     */
    public function emitir(User $por): void
    {
        for ($intento = 1; ; $intento++) {
            try {
                DB::transaction(function () use ($por) {
                    // Comparte el bloqueo de folios con la nueva emisión autónoma.
                    DB::table('parametros')->where('clave', 'certificado_vigencia_dias')->update(['valor' => DB::raw('valor')]);
                    $anio = (int) now()->year;
                    $correlativo = ((int) static::where('anio', $anio)->max('correlativo')) + 1;

                    $this->forceFill([
                        'estado' => 'emitido',
                        'anio' => $anio,
                        'correlativo' => $correlativo,
                        'folio' => sprintf('%d-%04d', $anio, $correlativo),
                        'fecha_emision' => now()->toDateString(),
                        'fecha_vencimiento' => now()->addDays(Parametro::entero('certificado_vigencia_dias'))->toDateString(),
                        'descarga_disponible_hasta' => now()->addDays(Parametro::entero('certificado_plazo_descarga_dias')),
                        'otorgado_por' => $por->id,
                        'otorgante_nombre' => $por->name,
                        'otorgante_cargo' => $por->nombreRol(),
                        'aporte' => $this->tipo === 'socio' ? 0 : ($this->aporte ?? Parametro::entero('certificado_residencia_valor')),
                    ])->save();
                });

                return;
            } catch (UniqueConstraintViolationException $e) {
                if ($intento >= 3) {
                    throw $e;
                }
            }
        }
    }

    public function nombreArchivoPdf(): string
    {
        return 'certificado-'.($this->folio ?? 'borrador-'.$this->id).'.pdf';
    }

    public function toPdf()
    {
        return Pdf::loadView('certificados.pdf', [
            'c' => $this,
            'junta' => config('jjvv.nombre'),
        ])->setPaper('letter');
    }
}
