<?php

namespace App\Api\OpenApi\Schemas;

use OpenApi\Attributes as OAT;

/** URLs de navegación del paginador de Laravel (primera, última, anterior, siguiente). */
#[OAT\Schema(
    schema: 'UrlPaginacion',
    type: 'object',
    properties: [
        new OAT\Property(property: 'first', type: 'string', nullable: true),
        new OAT\Property(property: 'last', type: 'string', nullable: true),
        new OAT\Property(property: 'prev', type: 'string', nullable: true),
        new OAT\Property(property: 'next', type: 'string', nullable: true),
    ],
)]
final class UrlPaginacion {}
