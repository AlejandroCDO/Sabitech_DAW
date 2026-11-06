<?php

namespace App\Api\OpenApi\Schemas;

use OpenApi\Attributes as OAT;

/** Token temporal del QR de una reserva, válido hasta la siguiente frontera de 30 s. */
#[OAT\Schema(
    schema: 'QrToken',
    type: 'object',
    required: ['reserva_id', 'token', 'expira_en'],
    properties: [
        new OAT\Property(property: 'reserva_id', type: 'integer', example: 41),
        new OAT\Property(property: 'token', description: 'hash_hmac(\'sha256\', qr_secret, expira) en hexadecimal.', type: 'string', example: 'a91c7d2c9de3f5173ffa5d4a703b475f0937f86811916a6ad20933b3d3a336f4'),
        new OAT\Property(property: 'expira_en', format: 'date-time', description: 'Momento (frontera global de 30 s) en el que el token caduca.', type: 'string', example: '2026-11-18 10:05:30'),
    ],
)]
final class QrToken {}
