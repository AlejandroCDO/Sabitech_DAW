<?php

namespace App\Api\OpenApi\Schemas;

use OpenApi\Attributes as OAT;

/**
 * Reserva tal y como la ve el asistente (modelo serializado con la charla
 * cargada; el secreto del QR nunca se expone).
 */
#[OAT\Schema(
    schema: 'Reserva',
    type: 'object',
    required: ['id', 'usuario_id', 'charla_id', 'estado'],
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 41),
        new OAT\Property(property: 'usuario_id', type: 'integer', example: 7),
        new OAT\Property(property: 'charla_id', type: 'integer', example: 12),
        new OAT\Property(property: 'estado', type: 'string', enum: ['pendiente', 'confirmada', 'cancelada'], example: 'confirmada'),
        new OAT\Property(property: 'email_intentos', type: 'integer', description: 'Envíos reales de confirmación realizados.', example: 1),
        new OAT\Property(property: 'email_ultimo_envio', format: 'date-time', type: 'string', nullable: true),
        new OAT\Property(property: 'charla', ref: '#/components/schemas/CharlaAdmin'),
        new OAT\Property(property: 'created_at', format: 'date-time', type: 'string'),
        new OAT\Property(property: 'updated_at', format: 'date-time', type: 'string'),
    ],
)]
final class Reserva {}
