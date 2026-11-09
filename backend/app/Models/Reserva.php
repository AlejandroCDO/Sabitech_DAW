<?php

namespace App\Models;

use App\Enums\EstadoReserva;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'usuario_id', 'charla_id', 'estado', 'qr_secret',
    'email_intentos', 'email_ultimo_envio',
])]
#[Hidden(['qr_secret'])] // el secreto del QR nunca debe salir en las respuestas JSON
class Reserva extends Model
{
    use HasFactory;

    protected $table = 'reservas';

    protected function casts(): array
    {
        return [
            'estado' => EstadoReserva::class,
            'email_intentos' => 'integer',
            'email_ultimo_envio' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function charla(): BelongsTo
    {
        return $this->belongsTo(Charla::class, 'charla_id');
    }

    /** Relación 1 — 0..1: una reserva tiene como máximo una asistencia. */
    public function asistencia(): HasOne
    {
        return $this->hasOne(Asistencia::class, 'reserva_id');
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('estado', '!=', EstadoReserva::Cancelada->value);
    }
}
