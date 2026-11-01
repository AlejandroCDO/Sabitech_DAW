---
name: backend-development
description: Convenciones de backend de SABITECH WEB: Laravel 13 en laravel/ (API en laravel/app/Api: controladores, Form Requests, DTOs, Resources, servicios; modelos Eloquent y autorización). Úsala al crear o modificar endpoints, lógica de servidor, validaciones o la integración con el frontend.
---

# backend-development

## Objetivo
Implementar lógica de servidor segura, validada y probada sobre la estructura real de Laravel.

## Cuándo usarla
Encargos que toquen `laravel/app/**`, `laravel/routes/**`, `laravel/config/**`, `laravel/bootstrap/**`.

## Estado de partida (verificado)
- Laravel: `routes/web.php` y `routes/console.php` vacíos (plantilla limpiada); la estructura de la API ya existe en `app/Api/` (Controllers, Requests, DTOs, Resources, Services, Middleware, Exceptions) con **archivos vacíos**; modelos, enums, policies, mail y job también vacíos. **No existe `routes/api.php`** ni paquete de autenticación de API (Sanctum, Passport). `bootstrap/app.php` ya devuelve errores en JSON para `api/*`.
- Único modelo: `App\Models\User` (atributos PHP `#[Fillable]` y `#[Hidden]`, contraseña con cast `hashed`).
- `fastapi/` se eliminó (D-01): toda la API es Laravel.

## Procedimiento
### Laravel
1. Rutas: web en `routes/web.php`. Para una API, habilitarla (`php artisan install:api`) instala Sanctum: requiere autorización del usuario.
2. Controladores en `app/Api/Controllers/{Perfil}/` (namespace `App\Api\Controllers\...`), finos: Form Request → DTO → servicio de `app/Api/Services` → Resource.
3. Validación con Form Requests (`php artisan make:request`) o `$request->validate()`; nunca confiar en datos del cliente.
4. Autorización con policies/gates y middleware `auth` en rutas protegidas.
5. Modelos: declara `#[Fillable]` explícito (igual que `User`); nunca `$guarded = []`.
6. Respuestas JSON con API Resources cuando haya API; códigos HTTP correctos (201, 404, 422, 403).
7. Estilo: `vendor/bin/pint`.
8. Genera archivos con `php artisan make:*` cuando sea posible para respetar la estructura.

## Reglas y limitaciones
- No inventes reglas de negocio (reservas, aforo, QR, roles): pide la especificación al coordinador.
- `composer require`, `pip install`, `php artisan install:*` y migraciones necesitan autorización.
- Cambios de esquema: aplica también `database-integrity`.

## Comprobaciones
- `cd laravel && composer test` y `vendor/bin/pint --test` (si existe `vendor/`).
- Cada endpoint nuevo tiene al menos una prueba Feature con caso válido, inválido (422) y no autorizado (401/403) cuando aplique.
- Sin secretos en código ni en `config/` (usar `env()` solo dentro de `config/*.php`).

## Terminado cuando
Criterios del encargo cumplidos, pruebas nuevas y existentes pasando (con salida) o motivo documentado de por qué no se ejecutaron.

## Agente recomendado
`fullstack-developer` (Sonnet).
