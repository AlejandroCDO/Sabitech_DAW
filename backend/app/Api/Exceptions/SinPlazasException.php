<?php

namespace App\Api\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class SinPlazasException extends HttpException
{
    public function __construct(string $message = 'No quedan plazas disponibles en esta charla.')
    {
        parent::__construct(409, $message);
    }
}