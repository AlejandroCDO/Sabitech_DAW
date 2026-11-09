<?php

namespace App\Api\DTOs\Acceso;

final readonly class ValidacionManualData
{
    public function __construct(
        public int $reservaId,
        public int $charlaId,
    ) {}

    /** Recibe los datos ya validados. */
    public static function fromArray(array $datos): self
    {
        return new self(
            reservaId: (int) $datos['reserva_id'],
            charlaId: (int) $datos['charla_id'],
        );
    }
}
