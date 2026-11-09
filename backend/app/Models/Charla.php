<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable([
    'ponente_id', 'ubicacion_id', 'titulo', 'descripcion',
    'inicio', 'fin', 'aforo', 'plazas_ocupadas', 'cancelada',
])]
class Charla extends Model
{
    use HasFactory;

    protected $table = 'charlas';

    protected function casts(): array
    {
        return [
            'inicio' => 'datetime',
            'fin' => 'datetime',
            'aforo' => 'integer',
            'plazas_ocupadas' => 'integer',
            'cancelada' => 'boolean',
        ];
    }

    public function ponente(): BelongsTo
    {
        return $this->belongsTo(Ponente::class, 'ponente_id');
    }

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'charla_id');
    }

    /** Asistencias de la charla, a través de sus reservas (asistencias no tiene charla_id). */
    public function asistencias(): HasManyThrough
    {
        return $this->hasManyThrough(Asistencia::class, Reserva::class, 'charla_id', 'reserva_id');
    }

    public function getPlazasDisponiblesAttribute(): int
    {
        return max($this->aforo - $this->plazas_ocupadas, 0);
    }
}
