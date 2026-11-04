<?php

namespace App\Api\DTOs\Acceso;

final readonly class ValidacionQrData
{
    public function __construct(
        public string $qrToken,
        public int $charlaId,
    ) {
    }

    /** Recibe los datos ya validados. */
    public static function fromArray(array $datos): self
    {
        return new self(
            qrToken: trim($datos['qr_token']),
            charlaId: (int) $datos['charla_id'],
        );
    }
}
