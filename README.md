# SABITECH

Proyecto Final de Ciclo DAW

# Integrantes

Alberto Bellanato, Alejandro Company y Marc Penadés

# Arquitectura

- `backend/`: API REST en Laravel 13 (PHP 8.4) con autenticación por token (Sanctum). Es solo API: la interfaz es la SPA de `frontend/`. El código de la API está en `backend/app/Api` (controladores por perfil, requests, DTOs, servicios, resources y excepciones).
- `frontend/`: SPA en React con Vite (de momento sin conexión con la API).
- Base de datos: MySQL 8 en Docker (`docker-compose.yml`). Las pruebas automáticas usan SQLite en memoria.
- Perfiles: `admin`, `acceso` (personal de acceso) y `asistente`.
- Documentación de la API: Swagger UI en `http://localhost:8000/api/docs` (JSON en `/api/docs.json`) y [`docs/backend/API.md`](docs/backend/API.md). QR y correo: [`docs/backend/QR_Y_CORREO.md`](docs/backend/QR_Y_CORREO.md).

# Puesta en marcha del backend

Requisitos: Docker Desktop, PHP 8.4 y Composer.

## 1. Arrancar la base de datos (Docker)

Con Docker Desktop en marcha, desde la raíz del proyecto:

```
docker compose up -d
```

`docker-compose.yml` lee las variables `MYSQL_*` de un `.env` en la raíz (hay un `.env.example` como plantilla). El contenedor se llama `sabitech_mysql` y expone MySQL en `127.0.0.1:3306`.

## 2. Crear la estructura de la base de datos

La estructura oficial del modelo de datos está en [`database/Sentencia Creación.txt`](database/Sentencia Creación.txt). Se importa **tal cual** (crea `sabitech_db` y sus 7 tablas, sin datos). Ejecute esto **desde `cmd.exe`** en la raíz del proyecto (la redirección `<` no existe en PowerShell):

```
docker exec -i sabitech_mysql mysql -uroot -pcambiar_esta_contrasena_root --default-character-set=utf8mb4 < "database\Sentencia Creación.txt"
```

La base resultante debe tener exactamente: `roles`, `ubicaciones`, `ponentes`, `usuarios`, `charlas`, `reservas` y `asistencias`.

## 3. Configurar Laravel

```
cd backend
cp .env.example .env
php artisan key:generate
composer install
```

Rellene `backend/.env` con los mismos datos de base de datos que usó en Docker (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) y los datos del buzón de correo de pruebas (`MAIL_*`). Para la demo ponga `APP_DEBUG=false`. No suba `.env` al repositorio.

## 4. Arrancar la API

```
php artisan serve
```

- API: `http://localhost:8000/api/...`
- **Swagger UI: http://localhost:8000/api/docs** (JSON de OpenAPI en `/api/docs.json`). Para probar endpoints protegidos, obtenga el token con `POST /api/auth/login` y péguelo en el botón _Authorize_.
- Pruebas: `composer test` (usa SQLite en memoria, no toca la base de datos de desarrollo).

# Añadir datos a la base de datos

La base importada está vacía. Para poblarla:

- **Datos de laboratorio (recomendado para la demo)**, desde `backend/`:

  ```
  php artisan db:seed
  ```

  No use `php artisan migrate`: la estructura ya está creada desde la sentencia y las migraciones intentarían crear tablas que no forman parte del modelo.

- **Datos propios**: insértelos con cualquier cliente MySQL (phpMyAdmin, DBeaver, MySQL Workbench) conectando a `127.0.0.1:3306`, base `sabitech_db`, usuario `sabitech_user` y contraseña `cambiar_esta_contrasena`; o importe un `.sql` con `INSERT`.

  > **Login:** el modelo de datos no incluye la tabla `personal_access_tokens` que Sanctum necesita para emitir tokens. Swagger y los endpoints públicos funcionan sin ella, pero para usar `POST /api/auth/login` hay que crearla una vez con:
  > `php artisan migrate --path=database/migrations/2026_11_04_000008_create_personal_access_tokens_table.php`
  > (añade solo esa tabla; el resto del modelo no cambia).

# Usuarios de laboratorio

Los crea el seeder (`php artisan db:seed`); son datos ficticios y la contraseña de todos es `password`. No usar fuera de laboratorio.

| Correo                     | Rol       |
| -------------------------- | --------- |
| `admin@sabitech.test`      | admin     |
| `acceso@sabitech.test`     | acceso    |
| `asistente@sabitech.test`  | asistente |
| `asistente2@sabitech.test` | asistente |

El seeder también crea ubicaciones en Valencia y Alicante, ponentes, cinco charlas futuras (una con el aforo completo y una cancelada) y varias reservas.

# Limitaciones conocidas

- El QR emitido y el validado no coinciden todavía; la plantilla del correo de confirmación está vacía.
- Los formatos de error de la API no están unificados.
- Los servicios, requests y resources de `app/Api` aún no están conectados a todos los controladores.
- Los CHECK de `charlas` solo se crean en MySQL; las pruebas con SQLite no reproducen el bloqueo de filas ni esos CHECK, y las migraciones no se han ejecutado contra MySQL.
- El frontend todavía no consume la API.
