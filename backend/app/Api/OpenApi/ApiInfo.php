<?php

namespace App\Api\OpenApi;

use OpenApi\Attributes as OAT;

/**
 * Definición global de la documentación OpenAPI de la API de SABITECH.
 *
 * El paquete l5-swagger (Swagger UI) escanea app/Api/OpenApi y los
 * controladores para construir la especificación que sirve en /api/docs.
 */
#[OAT\Info(
    title: 'API de SABITECH',
    description: <<<'DESC'
        API REST del proyecto final de DAW: gestión de charlas, reservas,
        asistencias con QR y perfiles de usuario.

        Todos los endpoints están bajo el prefijo `/api` y responden JSON
        (las peticiones pueden enviar `Accept: application/json`).

        Las peticiones protegidas utilizan un token de Sanctum, que se envía
        en la cabecera `Authorization: Bearer <token>`. El token se obtiene
        en `POST /api/auth/login` y se introduce con el botón **Authorize**
        (con el botón verde y el candado a la derecha: pegar solo el token,
        sin la palabra Bearer).
        DESC,
    version: '1.0.0',
    contact: new OAT\Contact(name: 'SABITECH (Proyecto Final DAW)'),
)]
#[OAT\Server(url: 'http://localhost:8000', description: 'Servidor de desarrollo local (php artisan serve)')]
#[OAT\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum',
    description: 'Token de API de Laravel Sanctum obtenido en POST /api/auth/login. Enviar sin la palabra Bearer.',
)]
#[OAT\Tag(name: 'Público', description: 'Catálogo de charlas y provincias, sin autenticación.')]
#[OAT\Tag(name: 'Autenticación', description: 'Login, perfil y logout con tokens de Sanctum.')]
#[OAT\Tag(name: 'Asistente', description: 'Acciones del asistente autenticado (rol asistente).')]
#[OAT\Tag(name: 'Personal de acceso', description: 'Validación de asistencias en la puerta (roles acceso y admin).')]
#[OAT\Tag(name: 'Administración', description: 'Gestión de charlas, ponentes, ubicaciones (rol admin).')]
final class ApiInfo {}
