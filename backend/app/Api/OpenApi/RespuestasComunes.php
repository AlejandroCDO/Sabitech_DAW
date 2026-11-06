<?php

namespace App\Api\OpenApi;

use OpenApi\Attributes as OAT;

/**
 * Respuestas de error comunes que referencian los endpoints con `ref`.
 *
 * Quedan registradas en `components › responses` y se aplican a las
 * operaciones con `new OAT\Response(response: '401', ref: ...)`.
 */
#[OAT\Response(
    response: '401',
    description: 'No autenticado: falta la cabecera Authorization o el token no es válido.',
    content: [new OAT\JsonContent(
        schema: 'Error',
        example: ['message' => 'Unauthenticated.'],
    )],
)]
#[OAT\Response(
    response: '403',
    description: 'Autenticado pero sin el rol necesario para la acción.',
    content: [new OAT\JsonContent(
        schema: 'Error',
        example: ['message' => 'No tienes permisos para realizar esta acción.'],
    )],
)]
#[OAT\Response(
    response: '404',
    description: 'Recurso no encontrado (o no visible para el usuario autenticado).',
    content: [new OAT\JsonContent(schema: 'Error')],
)]
#[OAT\Response(
    response: '409',
    description: 'Conflicto con las reglas de negocio: el recurso no admite la acción en su estado actual.',
    content: [new OAT\JsonContent(schema: 'Error')],
)]
#[OAT\Response(
    response: '429',
    description: 'Demasiadas peticiones: se alcanzó el límite controlado por la aplicación.',
    content: [new OAT\JsonContent(schema: 'Error')],
)]
#[OAT\Response(
    response: '422',
    description: 'Validación incorrecta: el cuerpo de la petición no cumple las reglas.',
    content: [new OAT\JsonContent(schema: 'ErrorValidacion')],
)]
final class RespuestasComunes {}
