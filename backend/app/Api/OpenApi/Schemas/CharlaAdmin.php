<?php

namespace App\Api\OpenApi\Schemas;

use OpenApi\Attributes as OAT;

/**
 * Charla completa tal y como la devuelve el perfil de administración
 * (modelo Eloquent serializado, con las relaciones cargadas según la ruta).
 */
#[OAT\Schema(
    schema: 'CharlaAdmin',
    type: 'object',
    required: ['id', 'ponente_id', 'ubicacion_id', 'titulo', 'descripcion', 'inicio', 'fin', 'aforo', 'plazas_ocupadas', 'cancelada'],
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 12),
        new OAT\Property(property: 'ponente_id', type: 'integer', example: 3),
        new OAT\Property(property: 'ubicacion_id', type: 'integer', example: 2),
        new OAT\Property(property: 'titulo', type: 'string', example: 'Introducción a la IA'),
        new OAT\Property(property: 'descripcion', type: 'string'),
        new OAT\Property(property: 'inicio', format: 'date-time', type: 'string', example: '2026-11-20 10:00:00'),
        new OAT\Property(property: 'fin', format: 'date-time', type: 'string', example: '2026-11-20 12:00:00'),
        new OAT\Property(property: 'aforo', type: 'integer', example: 50),
        new OAT\Property(property: 'plazas_ocupadas', type: 'integer', example: 23),
        new OAT\Property(property: 'cancelada', type: 'boolean'),
        new OAT\Property(property: 'ponente', ref: '#/components/schemas/Ponente'),
        new OAT\Property(property: 'ubicacion', ref: '#/components/schemas/Ubicacion'),
        new OAT\Property(property: 'created_at', format: 'date-time', type: 'string'),
        new OAT\Property(property: 'updated_at', format: 'date-time', type: 'string'),
    ],
)]
final class CharlaAdmin {}
