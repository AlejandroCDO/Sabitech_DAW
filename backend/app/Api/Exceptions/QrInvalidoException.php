<?php

namespace App\Api\Exceptions;

class QrInvalidoException extends ApiException
{
    public function __construct(string $message = 'El código QR no es válido.')
    {
        parent::__construct($message, 422, 'qr_invalido');
    }
}