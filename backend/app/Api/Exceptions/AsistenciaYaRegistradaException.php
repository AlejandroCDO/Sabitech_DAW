<?php

namespace App\Api\Exceptions;

class AsistenciaYaRegistradaException extends ApiException
{
    public function __construct(string $message = 'La asistencia de esta reserva ya está registrada.')
    {
        parent::__construct($message, 409, 'asistencia_ya_registrada');
    }
}
