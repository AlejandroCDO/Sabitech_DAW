<?php

namespace App\Api\OpenApi\Schemas;

use OpenApi\Attributes as OAT;

/** Error de validación de Laravel (HTTP 422). */
#[OAT\Schema(
    schema: 'ErrorValidacion',
    type: 'object',
    properties: [
        new OAT\Property(property: 'message', description: 'Mensaje general.', type: 'string', example: 'The email field is required.'),
        new OAT\Property(property: 'errors', description: 'Errores por campo.', type: 'object', additionalProperties: new OAT\AdditionalProperties(
            type: 'array', items: new OAT\Items(type: 'string'),
        )),
    ],
)]
final class ErrorValidacion {}
