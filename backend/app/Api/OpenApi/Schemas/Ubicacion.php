<?php

namespace App\Api\OpenApi\Schemas;

use OpenApi\Attributes as OAT;

/** Ubicación física donde se imparte la charla. */
#[OAT\Schema(
    schema: 'Ubicacion',
    type: 'object',
    required: ['id', 'nombre', 'provincia', 'municipio', 'calle', 'codigo_postal', 'numero'],
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 1),
        new OAT\Property(property: 'nombre', type: 'string', example: 'Aula Magna'),
        new OAT\Property(property: 'provincia', type: 'string', example: 'Alicante'),
        new OAT\Property(property: 'municipio', type: 'string', example: 'San Vicente del Raspeig'),
        new OAT\Property(property: 'calle', type: 'string', example: 'Calle Alcalá de Henares'),
        new OAT\Property(property: 'codigo_postal', type: 'string', example: '03690'),
        new OAT\Property(property: 'numero', type: 'string', example: '1'),
        new OAT\Property(property: 'puerta', type: 'string', nullable: true, example: '2B'),
        new OAT\Property(property: 'created_at', format: 'date-time', type: 'string'),
        new OAT\Property(property: 'updated_at', format: 'date-time', type: 'string'),
    ],
)]
final class Ubicacion {}
