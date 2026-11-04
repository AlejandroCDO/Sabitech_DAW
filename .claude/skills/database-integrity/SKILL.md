---
name: database-integrity
description: Integridad y consistencia de datos en SABITECH WEB: migraciones de Laravel, relaciones Eloquent, claves foráneas, índices, factories y seeders, y coherencia de configuración entre SQLite (.env.example, tests) y MySQL 8 (docker-compose). Úsala al diseñar o revisar cambios de esquema.
---

# database-integrity

## Objetivo
Que cada cambio de esquema sea reversible, coherente con los modelos y proteja la integridad de los datos con restricciones en la base de datos, no solo en la aplicación.

## Cuándo usarla
Al crear o revisar migraciones (`backend/database/migrations`), modelos, relaciones, factories o seeders, o al cambiar la configuración de conexión.

## Estado de partida (verificado)
- Migraciones por defecto: `users`, `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`.
- **Inconsistencia abierta**: `backend/.env.example` usa `DB_CONNECTION=sqlite`, pero `docker-compose.yml` levanta MySQL 8 (`sabitech_db`). Las pruebas usan SQLite en memoria (`phpunit.xml`). Qué motor es el oficial es una decisión del usuario.

## Procedimiento
1. Crea migraciones nuevas con `php artisan make:migration`; **nunca edites una migración ya ejecutada en un entorno compartido**: crea otra.
2. Cada migración implementa `down()` reversible.
3. Relaciones: `foreignId()->constrained()` con `cascadeOnDelete()`/`restrictOnDelete()`/`nullOnDelete()` elegido de forma explícita y justificado en el informe.
4. Restricciones de unicidad que expresen reglas de negocio (p. ej. un registro por usuario y evento) van como índice `unique` en la base de datos, además de la validación.
5. Índices en columnas usadas en filtros y en claves foráneas.
6. Operaciones que cambian varias tablas o que compiten por un recurso limitado: `DB::transaction()` y, si hay concurrencia, bloqueo (`lockForUpdate()`).
7. Mantén alineados migración, modelo (`#[Fillable]`, casts, relaciones) y factory.
8. Comprueba que la migración funciona en SQLite (tests) y en MySQL 8 (evita tipos o funciones exclusivos de uno sin comprobarlo).

## Reglas y limitaciones
- Ejecutar `migrate`, `migrate:fresh`, `db:wipe`, `db:seed` o tocar el volumen `mysql_data` requiere autorización del usuario.
- No incluyas datos personales reales en seeders ni factories.
- No copies las credenciales de `docker-compose.yml` en código ni documentación.

## Comprobaciones
- `php artisan migrate --pretend` (muestra el SQL sin ejecutarlo) cuando haya entorno disponible.
- `composer test` (usa SQLite en memoria; no afecta a datos reales).
- Revisión manual: claves foráneas, índices, `down()`, coherencia con el modelo.

## Terminado cuando
Migración reversible, restricciones en base de datos, modelo y factory alineados y pruebas pasando (o motivo documentado de por qué no se ejecutaron).

## Agente recomendado
`fullstack-developer` (Sonnet) para implementar; `quick-reviewer` (Haiku) para revisar.
