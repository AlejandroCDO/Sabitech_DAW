<?php

namespace App\Enums;

enum EstadoReserva: string
{
    case Pendiente = 'pendiente';
    case Confirmada = 'confirmada';
    case Cancelada = 'cancelada';
}
