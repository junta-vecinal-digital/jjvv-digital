<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\Auditable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use Auditable, HasFactory, Notifiable;

    protected $attributes = [
        'role' => 'vecino',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }
    public const ROLES = [
        'admin' => 'Administrador',
        'presidente' => 'Presidente',
        'secretario' => 'Secretario',
        'tesorero' => 'Tesorero',
        'vecino' => 'Vecino',
    ];
    public function nombreRol(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

        /** Dirigente: presidente, secretario o tesorero. El administrador no lo es. */
    public function esDirigente(): bool
    {
        return $this->hasRole('presidente', 'secretario', 'tesorero');
    }

    /** Socio: se deduce de la ficha en estado activo, no del rol. */
    public function esSocioActivo(): bool
    {
        return $this->socio?->estado === 'activo';
    }

    public function socio(): HasOne
    {
        return $this->hasOne(Socio::class);
    }

}
