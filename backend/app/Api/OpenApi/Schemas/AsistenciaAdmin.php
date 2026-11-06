<?php

namespace App\Api\OpenApi\Schemas;

use OpenApi\Attributes as OAT;

/** Asistencia registrada por el personal de acceso, según AsistenciaAdminResource. */
#[OAT\Schema(
    schema: 'AsistenciaAdmin',
    type: 'object',
    required: ['id', 'reserva_id', 'validado_por', 'metodo', 'validada_en'],
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 9),
        new OAT\Property(property: 'reserva_id', type: 'integer', example: 41),
        new OAT\Property(property: 'validado_por', type: 'integer', description: 'Id del usuario (personal de acceso) que validó la asistencia.', example: 2),
        new OAT\Property(property: 'metodo', type: 'string', enum: ['qr', 'manual'], example: 'qr'),
        new OAT\Property(property: 'validada_en', format: 'date-time', type: 'string'),
        new OAT\Property(property: 'validador', ref: '#/components/schemas/Usuario'),
        new OAT\Property(property: 'reserva', ref: '#/components/schemas/ReservaAdmin'),
    ],
)]
final class AsistenciaAdmin {}
