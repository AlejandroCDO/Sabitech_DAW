<?php

namespace App\Api\OpenApi\Schemas;

use OpenApi\Attributes as OAT;

/** Ponente de una charla. */
#[OAT\Schema(
    schema: 'Ponente',
    type: 'object',
    required: ['id', 'nombre'],
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 1),
        new OAT\Property(property: 'nombre', type: 'string', example: 'Ada Lovelace'),
        new OAT\Property(property: 'biografia', type: 'string', nullable: true),
        new OAT\Property(property: 'foto_url', format: 'uri', type: 'string', nullable: true, example: 'https://picsum.photos/300'),
        new OAT\Property(property: 'especialidad', type: 'string', nullable: true, example: 'Inteligencia artificial'),
        new OAT\Property(property: 'linkedin_url', format: 'uri', type: 'string', nullable: true, example: 'https://www.linkedin.com/in/ada'),
        new OAT\Property(property: 'created_at', format: 'date-time', type: 'string'),
        new OAT\Property(property: 'updated_at', format: 'date-time', type: 'string'),
    ],
)]
final class Ponente {}
