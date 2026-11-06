<?php

namespace App\Api\OpenApi\Schemas;

use OpenApi\Attributes as OAT;

/**
 * Reserva documentada por el recurso ReservaAdminResource, usada en las
 * listas del personal de acceso y del administrador.
 */
#[OAT\Schema(
    schema: 'ReservaAdmin',
    type: 'object',
    required: ['id', 'charla_id', 'estado'],
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 41),
        new OAT\Property(property: 'charla_id', type: 'integer', example: 12),
        new OAT\Property(property: 'estado', type: 'string', enum: ['pendiente', 'confirmada', 'cancelada'], example: 'confirmada'),
        new OAT\Property(property: 'email_intentos', type: 'integer', example: 1),
        new OAT\Property(property: 'email_ultimo_envio', format: 'date-time', type: 'string', nullable: true),
        new OAT\Property(property: 'created_at', format: 'date-time', type: 'string'),
        new OAT\Property(property: 'usuario', ref: '#/components/schemas/Usuario'),
        new OAT\Property(property: 'asistencia', ref: '#/components/schemas/AsistenciaAdmin'),
    ],
)]
final class ReservaAdmin {}
