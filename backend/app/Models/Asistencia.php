<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['reserva_id', 'validado_por', 'qr_token', 'validada_en'])]
#[Hidden(['qr_token'])]
class Asistencia extends Model
{
    use HasFactory;

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
}
