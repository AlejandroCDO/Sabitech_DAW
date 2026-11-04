<?php

namespace App\Api\Exceptions;

use Exception;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\JsonResponse;

// Errores 4xx esperados: no se envían al log (ShouldntReport).
abstract class ApiException extends Exception implements ShouldntReport
{
    public function __construct(
        string $message,
        private readonly int $status,
        private readonly string $errorCode,
    ) {
        parent::__construct($message);
    }

    public function status(): int
    {
        return $this->status;
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'code' => $this->errorCode,
        ], $this->status);
    }
}
