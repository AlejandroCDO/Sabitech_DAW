<?php

namespace App\Api\Exceptions;

class FueraDePlazoException extends ApiException
{
    public function __construct(string $message = 'Fuera de plazo: se registra desde 30 minutos antes del inicio hasta el fin de la charla.')
    {
        parent::__construct($message, 409, 'fuera_de_plazo');
    }
}
