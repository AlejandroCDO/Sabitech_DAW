<?php

namespace App\Api\OpenApi\Schemas;

use OpenApi\Attributes as OAT;

/** Rol de un usuario. */
#[OAT\Schema(
    schema: 'Rol',
    type: 'object',
    required: ['id', 'nombre'],
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 3),
        new OAT\Property(property: 'nombre', type: 'string', example: 'asistente'),
    ],
)]
final class Rol {}
