<?php

namespace App\Api\OpenApi\Schemas;

use OpenApi\Attributes as OAT;

/**
 * Charla tal y como se publica en el catálogo público y en las reservas del
 * asistente, con el porcentaje calculado de plazas disponibles.
 */
#[OAT\Schema(
    schema: 'CharlaCatalogo',
    type: 'object',
    required: ['id', 'titulo', 'descripcion', 'inicio', 'fin', 'aforo', 'plazas_disponibles', 'cancelada'],
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 12),
        new OAT\Property(property: 'titulo', type: 'string', example: 'Introducción a la IA'),
        new OAT\Property(property: 'descripcion', type: 'string', example: 'Charla amena sobre lo que es la inteligencia artificial.'),
        new OAT\Property(property: 'inicio', format: 'date-time', type: 'string', example: '2026-11-20 10:00:00'),
        new OAT\Property(property: 'fin', format: 'date-time', type: 'string', example: '2026-11-20 12:00:00'),
        new OAT\Property(property: 'aforo', type: 'integer', example: 50),
        new OAT\Property(property: 'plazas_disponibles', type: 'integer', description: 'Sitios libres calculados como `max(aforo - plazas_ocupadas, 0)`.', example: 27),
        new OAT\Property(property: 'cancelada', type: 'boolean'),
        new OAT\Property(property: 'ponente', ref: '#/components/schemas/Ponente'),
        new OAT\Property(property: 'ubicacion', ref: '#/components/schemas/Ubicacion'),
    ],
)]
final class CharlaCatalogo {}
