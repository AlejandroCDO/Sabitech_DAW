<?php

namespace App\Api\DTOs\Auth;

final readonly class LoginData
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}

    /** Recibe los datos ya validados. */
    public static function fromArray(array $datos): self
    {
        return new self(
            email: trim($datos['email']),
            password: $datos['password'],
        );
    }
}
