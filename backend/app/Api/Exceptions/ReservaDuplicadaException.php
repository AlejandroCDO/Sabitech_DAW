<?php

namespace App\Api\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

/** El usuario ya tiene una reserva activa (pendiente o confirmada) para la charla. */
class ReservaDuplicadaException extends HttpException
{
    public function __construct(string $message = 'Ya tienes una reserva para esta charla.')
    {
        parent::__construct(409, $message);
    }
}
