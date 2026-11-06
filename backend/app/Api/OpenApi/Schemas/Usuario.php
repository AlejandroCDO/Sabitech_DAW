<?php

namespace App\Api\OpenApi\Schemas;

use OpenApi\Attributes as OAT;

/** Usuario del sistema; el backend devuelve la contraseña oculta y carga el rol. */
#[OAT\Schema(
    schema: 'Usuario',
    type: 'object',
    required: ['id', 'rol_id', 'nombre', 'email'],
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 1),
        new OAT\Property(property: 'rol_id', type: 'integer', example: 1),
        new OAT\Property(property: 'nombre', type: 'string', example: 'Alberto Bellanato'),
        new OAT\Property(property: 'email', format: 'email', type: 'string', example: 'admin@sabitech.test'),
        new OAT\Property(property: 'telefono', type: 'string', nullable: true, example: '600123456'),
        new OAT\Property(property: 'fecha_nacimiento', format: 'date', type: 'string', nullable: true, example: '2004-03-15'),
        new OAT\Property(property: 'rol', ref: '#/components/schemas/Rol'),
        new OAT\Property(property: 'created_at', format: 'date-time', type: 'string'),
        new OAT\Property(property: 'updated_at', format: 'date-time', type: 'string'),
    ],
)]
final class Usuario {}
