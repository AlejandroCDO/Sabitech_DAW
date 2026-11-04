<?php

namespace App\Enums;

/**
 * Estado del correo de confirmación de una reserva.
 * No tiene columna propia en el modelo de datos: se guardan email_intentos y email_ultimo_envio.
 */
enum EstadoEmail: string
{
    case Pendiente = 'pendiente';
    case Enviado   = 'enviado';
    case Fallido   = 'fallido';
}
