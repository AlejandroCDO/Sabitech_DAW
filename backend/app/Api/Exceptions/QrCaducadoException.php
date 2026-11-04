<?php

namespace App\Api\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class QrCaducadoException extends HttpException
{
    public function __construct(string $message = 'El código QR ha caducado.')
    {
        parent::__construct(410, $message);
    }
}