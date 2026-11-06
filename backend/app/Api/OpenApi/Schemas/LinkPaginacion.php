<?php

namespace App\Api\OpenApi\Schemas;

use OpenApi\Attributes as OAT;

/** Enlace de paginación del paginador de Laravel. */
#[OAT\Schema(
    schema: 'LinkPaginacion',
    type: 'object',
    properties: [
        new OAT\Property(property: 'url', type: 'string', nullable: true, example: 'http://localhost:8000/api/charlas?page=2'),
        new OAT\Property(property: 'label', type: 'string', example: '2'),
        new OAT\Property(property: 'page', type: 'integer', nullable: true, example: 2),
        new OAT\Property(property: 'active', type: 'boolean', example: false),
    ],
)]
final class LinkPaginacion {}
