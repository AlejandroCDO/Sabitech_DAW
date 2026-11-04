<?php

namespace App\Api\DTOs\Qr;

use Carbon\CarbonImmutable;

final readonly class QrTokenData
{
    /** Segundos de validez de cada ventana del QR (renovación cada 30 s). */
    public const CADUCIDAD_SEGUNDOS = 30;

    public function __construct(
        public int $reservaId,
        public string $token,
        public CarbonImmutable $expiraEn,
    ) {}

    /** Recibe los datos ya generados por el controlador o el servicio de QR. */
    public static function fromArray(array $datos): self
    {
        return new self(
            reservaId: (int) $datos['reserva_id'],
            token: (string) $datos['token'],
            expiraEn: CarbonImmutable::parse($datos['expira_en']),
        );
    }

    /** Claves para la respuesta JSON del endpoint del QR. */
    public function toArray(): array
    {
        return [
            'reserva_id' => $this->reservaId,
            'token' => $this->token,
            'expira_en' => $this->expiraEn,
        ];
    }
}
