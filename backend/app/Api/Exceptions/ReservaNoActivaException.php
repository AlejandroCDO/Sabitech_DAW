<?php

namespace App\Api\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

/** La operación que se intenta realizar exige una reserva activa y no lo está. */
class ReservaNoActivaException extends HttpException
{
    public function __construct(string $message = 'La reserva no está activa.')
    {
        parent::__construct(409, $message);
    }
}
