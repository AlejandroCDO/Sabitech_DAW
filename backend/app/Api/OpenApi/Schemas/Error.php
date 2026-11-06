<?php

namespace App\Api\OpenApi\Schemas;

use OpenApi\Attributes as OAT;

/** Error genérico del backend (exposición resumida para clientes y documentación). */
#[OAT\Schema(
    schema: 'Error',
    type: 'object',
    properties: [
        new OAT\Property(property: 'message', description: 'Mensaje del error.', type: 'string', example: 'No tienes permisos para realizar esta acción.'),
    ],
)]
final class Error {}
