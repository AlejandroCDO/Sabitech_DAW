# SABITECH

Proyecto Final de Ciclo DAW

# Integrantes

Alberto Bellanato
Alejandro Company
Marc Penadés

# Arquitectura

- `backend/`: API REST en Laravel 13 (PHP 8.4) con autenticación por token (Sanctum). Es solo API: la interfaz es la SPA de `frontend/`. El código de la API está en `backend/app/Api` (controladores por perfil, requests, DTOs, servicios, resources y excepciones).
- `frontend/`: SPA en React con Vite (de momento sin conexión con la API).
- Base de datos: MySQL 8 en Docker (`docker-compose.yml`). Las pruebas automáticas usan SQLite en memoria.
- Perfiles: `admin`, `acceso` (personal de acceso) y `asistente`.
- Documentación de la API: [`docs/backend/API.md`](docs/backend/API.md). QR y correo: [`docs/backend/QR_Y_CORREO.md`](docs/backend/QR_Y_CORREO.md).

# Puesta en marcha del backend

Requisitos: PHP 8.4, Composer y Docker.

1. Base de datos MySQL con Docker. `docker-compose.yml` lee las variables `MYSQL_DATABASE`, `MYSQL_ROOT_PASSWORD`, `MYSQL_USER` y `MYSQL_PASSWORD` de un `.env` en la raíz (parta de `.env.example`):

   ```
   docker compose up -d
   ```

2. Configuración de Laravel:

   ```
   cd backend
   cp .env.example .env
   ```

   Edite `backend/.env` con los mismos datos de base de datos que usó en Docker (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) y los datos del buzón de correo de pruebas (`MAIL_*`). Para la demo ponga `APP_DEBUG=false`. No suba `.env` al repositorio.

3. Dependencias, clave y datos:

   ```
   composer install
   php artisan key:generate
   php artisan migrate --seed
   ```

4. Servidor de desarrollo y pruebas:

   ```
   php artisan serve
   composer test
   ```

   `composer test` usa SQLite en memoria (no toca la base de datos de desarrollo).

# Usuarios de laboratorio

Los crea el seeder (`php artisan migrate --seed`); son datos ficticios y la contraseña de todos es `password`. No usar fuera de laboratorio.

| Correo | Rol |
|---|---|
| `admin@sabitech.test` | admin |
| `acceso@sabitech.test` | acceso |
| `asistente@sabitech.test` | asistente |
| `asistente2@sabitech.test` | asistente |

El seeder también crea ubicaciones en Valencia y Alicante, ponentes, cinco charlas futuras (una con el aforo completo y una cancelada) y varias reservas.

# Limitaciones conocidas

- El QR emitido y el validado no coinciden todavía; la plantilla del correo de confirmación está vacía.
- Los formatos de error de la API no están unificados.
- Los servicios, requests y resources de `app/Api` aún no están conectados a todos los controladores.
- Los CHECK de `charlas` solo se crean en MySQL; las pruebas con SQLite no reproducen el bloqueo de filas ni esos CHECK, y las migraciones no se han ejecutado contra MySQL.
- El frontend todavía no consume la API.
