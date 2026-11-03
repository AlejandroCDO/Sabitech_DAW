<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['rol_id', 'nombre', 'email', 'telefono', 'fecha_nacimiento', 'password'])]
#[Hidden(['password'])]
class Usuario extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'usuarios';

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'password'         => 'hashed',
        ];
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'usuario_id');
    }

    /** Asistencias que este usuario ha validado (cuando actúa como personal de acceso). */
    public function asistenciasValidadas(): HasMany
    {
        return $this->hasMany(Asistencia::class, 'validado_por');
    }
}