<?php

namespace App\Api\OpenApi\Schemas;

use OpenApi\Attributes as OAT;

/** Metadatos del paginador estándar de Laravel (SimplePagination). */
#[OAT\Schema(
    schema: 'MetaPaginacion',
    type: 'object',
    properties: [
        new OAT\Property(property: 'current_page', type: 'integer', example: 1),
        new OAT\Property(property: 'from', type: 'integer', nullable: true, example: 1),
        new OAT\Property(property: 'last_page', type: 'integer', example: 4),
        new OAT\Property(property: 'links', type: 'array', items: new OAT\Items(ref: '#/components/schemas/LinkPaginacion')),
        new OAT\Property(property: 'path', type: 'string', example: 'http://localhost:8000/api/charlas'),
        new OAT\Property(property: 'per_page', type: 'integer', example: 15),
        new OAT\Property(property: 'to', type: 'integer', nullable: true, example: 15),
        new OAT\Property(property: 'total', type: 'integer', example: 53),
    ],
)]
final class MetaPaginacion {}
