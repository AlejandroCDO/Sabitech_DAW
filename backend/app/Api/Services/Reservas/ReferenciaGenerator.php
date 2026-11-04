<?php

namespace App\Api\Services\Reservas;

use App\Models\Reserva;

/** Referencia estable asociada a una reserva (SAB-000031). */
final class ReferenciaGenerator
{
    private const PREFIJO = 'SAB-';

    private const LONGITUD = 6;

    /** Convierte el id de la reserva en una referencia reconocible por el usuario. */
    public static function generar(Reserva $reserva): string
    {
        return self::PREFIJO.str_pad((string) $reserva->id, self::LONGITUD, '0', STR_PAD_LEFT);
    }
}
