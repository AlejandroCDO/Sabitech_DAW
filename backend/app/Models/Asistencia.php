<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

#[Fillable(['reserva_id', 'validado_por', 'qr_token', 'validada_en'])]
#[Hidden(['qr_token'])]
class Asistencia extends Model
{
    protected $table = 'asistencias';

    protected function casts(): array
    {
        return [
            'validada_en' => 'datetime',
        ];
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    /** Quién validó la asistencia (usuarios.id vía validado_por). */
    public function validador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'validado_por');
    }

    /** La charla se obtiene por la reserva. */
    public function charla(): HasOneThrough
    {
        return $this->hasOneThrough(Charla::class, Reserva::class, 'id', 'id', 'reserva_id', 'charla_id');
    }

    /** El asistente también se obtiene por la reserva. */
    public function usuario(): HasOneThrough
    {
        return $this->hasOneThrough(Usuario::class, Reserva::class, 'id', 'id', 'reserva_id', 'usuario_id');
    }
}