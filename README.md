# SABITECH

Proyecto Final de Ciclo DAW. Gestión de charlas, reservas y control de acceso mediante QR.

**Integrantes:** Alberto Bellanato, Alejandro Company y Marc Penadés.

## Arquitectura

- `backend/`: API REST en Laravel 13 con autenticación por token (Sanctum).
- `frontend/`: SPA en React con Vite. De momento es la plantilla inicial y todavía no consume la API.
- Base de datos: MySQL 8 en Docker. Las pruebas automáticas usan SQLite en memoria.
- Perfiles: `admin`, `acceso` (personal de acceso) y `asistente`.
- Documentación de la API: Swagger UI en `http://localhost:8000/api/docs`.

## Requisitos

- Docker Desktop.
- PHP 8.4 o superior.
- Composer 2.
- Opcional para el frontend: Node.js 20 o superior.

## Arranque desde cero

### 1. Configurar el entorno de Docker

Desde la raíz del proyecto:

```powershell
Copy-Item .env.example .env
docker compose up -d
docker ps
```

Debe aparecer el contenedor `sabitech_mysql`. MySQL queda disponible en `127.0.0.1:3306`.

### 2. Crear la base de datos

El proyecto no usa `php artisan migrate` para la base de datos de desarrollo. El esquema se importa desde SQL.

Desde PowerShell, en la raíz del proyecto:

```powershell
cmd /c 'docker exec -i sabitech_mysql mysql -uroot -pcambiar_esta_contrasena_root --default-character-set=utf8mb4 < "database\Sentencia Creación.txt"'
cmd /c 'docker exec -i sabitech_mysql mysql -uroot -pcambiar_esta_contrasena_root --default-character-set=utf8mb4 < "database\Sanctum_PersonalAccessTokens.sql"'
```

El primer archivo crea las 7 tablas del modelo de datos. El segundo crea `personal_access_tokens`, una tabla técnica que Sanctum necesita para el login.

Comprobar que existen las 8 tablas:

```powershell
docker exec sabitech_mysql mysql -uroot -pcambiar_esta_contrasena_root -e "SHOW TABLES FROM sabitech_db;"
```

### 3. Configurar Laravel

```powershell
cd backend
Copy-Item .env.example .env
composer install
php artisan key:generate
```

Si `backend/.env` ya existe, revisa que tenga estos valores:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sabitech_db
DB_USERNAME=sabitech_user
DB_PASSWORD=cambiar_esta_contrasena
MAIL_MAILER=log
```

`MAIL_MAILER=log` guarda los correos en `backend/storage/logs/laravel.log`. Para usar Mailtrap, cambia a `smtp` y rellena `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME` y `MAIL_PASSWORD`.

### 4. Cargar los datos de laboratorio

```powershell
php artisan db:seed
```

Esto crea roles, usuarios, ubicaciones, ponentes, charlas y reservas de ejemplo.

### 5. Arrancar la API

```powershell
php artisan serve
```

- API: `http://localhost:8000/api`
- Swagger: `http://localhost:8000/api/docs`
- Comprobación rápida: `http://localhost:8000/up`

## Usuarios de laboratorio

La contraseña de todos es `password`. Son datos ficticios y no deben usarse fuera del laboratorio.

| Correo | Rol |
|---|---|
| `admin@sabitech.test` | admin |
| `acceso@sabitech.test` | acceso |
| `asistente@sabitech.test` | asistente |
| `asistente2@sabitech.test` | asistente |

## Cómo añadir datos

### Opción 1: seeders

Los datos de ejemplo están en `backend/database/seeders`. Para añadir o cambiar datos:

1. Edita el seeder correspondiente (`CharlaSeeder`, `PonenteSeeder`, etc.).
2. Ejecuta:

```powershell
cd backend
php artisan db:seed
```

También se puede ejecutar un seeder concreto:

```powershell
php artisan db:seed --class=CharlaSeeder
```

Si ejecutas `CharlaSeeder` solo, después ejecuta `ReservaSeeder` para que `plazas_ocupadas` vuelva a cuadrar.

### Opción 2: API de administración

Primero obtén un token de administrador:

```powershell
$login = Invoke-RestMethod -Method Post `
  -Uri "http://localhost:8000/api/auth/login" `
  -ContentType "application/json" `
  -Body (@{ email = "admin@sabitech.test"; password = "password" } | ConvertTo-Json)

$headers = @{
  Authorization = "Bearer $($login.token)"
  Accept = "application/json"
}
```

Crear un ponente:

```powershell
$ponente = Invoke-RestMethod -Method Post `
  -Uri "http://localhost:8000/api/admin/ponentes" `
  -Headers $headers `
  -ContentType "application/json" `
  -Body (@{ nombre = "Ponente de prueba"; especialidad = "Desarrollo web" } | ConvertTo-Json)
```

Crear una ubicación:

```powershell
$ubicacion = Invoke-RestMethod -Method Post `
  -Uri "http://localhost:8000/api/admin/ubicaciones" `
  -Headers $headers `
  -ContentType "application/json" `
  -Body (@{
    nombre = "Sala de prueba"
    provincia = "Valencia"
    municipio = "Valencia"
    calle = "Calle Mayor"
    codigo_postal = "46001"
    numero = "1"
    puerta = "A"
  } | ConvertTo-Json)
```

Crear una charla:

```powershell
$charla = Invoke-RestMethod -Method Post `
  -Uri "http://localhost:8000/api/admin/charlas" `
  -Headers $headers `
  -ContentType "application/json" `
  -Body (@{
    ponente_id = $ponente.id
    ubicacion_id = $ubicacion.id
    titulo = "Charla de prueba"
    descripcion = "Charla creada desde la API."
    inicio = "2026-12-15 17:00:00"
    fin = "2026-12-15 19:00:00"
    aforo = 50
  } | ConvertTo-Json)
```

### Opción 3: tinker o SQL

Para crear datos sueltos:

```powershell
cd backend
php artisan tinker
```

```php
App\Models\Ponente::create([
    'nombre' => 'Ponente de prueba',
    'especialidad' => 'Desarrollo web',
]);
```

Las contraseñas se cifran solas gracias al cast `hashed` del modelo `Usuario`. Las reservas deben crearse preferentemente por la API para que `plazas_ocupadas` se mantenga correcto.

También puedes usar phpMyAdmin, DBeaver o MySQL Workbench con estos datos:

- Host: `127.0.0.1`
- Puerto: `3306`
- Base de datos: `sabitech_db`
- Usuario: `sabitech_user`
- Contraseña: `cambiar_esta_contrasena`

## Endpoints principales

### Públicos

- `GET /api/charlas`
- `GET /api/charlas/{charla}`
- `GET /api/provincias`

### Autenticación

- `POST /api/auth/login`
- `GET /api/auth/me`
- `POST /api/auth/logout`

### Asistente

- `GET /api/asistente/reservas`
- `POST /api/asistente/reservas`
- `DELETE /api/asistente/reservas/{reserva}`
- `GET /api/asistente/reservas/{reserva}/qr`
- `POST /api/asistente/reservas/{reserva}/reenviar-confirmacion`

### Personal de acceso

- `GET /api/acceso/charlas/{charla}/reservas`
- `POST /api/acceso/asistencias/qr`
- `POST /api/acceso/asistencias/manual`

### Administración

- CRUD de `/api/admin/charlas`, `/api/admin/ponentes` y `/api/admin/ubicaciones`.
- `POST /api/admin/charlas/{charla}/cancelar`.
- Consulta de reservas y asistencias de una charla.

## Demostración para el checkpoint

1. Abrir Swagger en `http://localhost:8000/api/docs`.
2. Consultar `GET /api/charlas` y comprobar que aparecen las charlas con plazas disponibles.
3. Hacer login con `asistente@sabitech.test` y guardar el token.
4. Reservar una charla con `POST /api/asistente/reservas`.
5. Intentar reservar la misma charla otra vez: debe devolver `409`.
6. Intentar reservar la charla de aforo completo: debe devolver `409`.
7. Consultar `GET /api/asistente/reservas`.
8. Obtener el QR con `GET /api/asistente/reservas/{id}/qr`.
9. Hacer login con `acceso@sabitech.test` y validar el QR con `POST /api/acceso/asistencias/qr`.
10. Repetir la validación: debe devolver `409` porque la asistencia ya existe.
11. Solicitar el reenvío de la confirmación y comprobar el correo en `backend/storage/logs/laravel.log`.

El QR cambia cada 30 segundos y se acepta la ventana actual y la anterior. Si tarda demasiado en validarse, hay que pedir un QR nuevo.

## Pruebas

Desde `backend/`:

```powershell
composer test
vendor/bin/pint --test
```

Las pruebas usan SQLite en memoria y no tocan la base de datos de Docker.

Para regenerar la documentación de Swagger:

```powershell
php artisan l5-swagger:generate
```

## Parar y reiniciar

```powershell
docker compose stop
docker compose up -d
```

Para borrar la base de datos y empezar de cero:

```powershell
docker compose down -v
docker compose up -d
```

Después hay que volver a importar los dos archivos SQL y ejecutar `php artisan db:seed`.

## Problemas frecuentes

- **La base se llama `sabitech_daw`:** revisa `MYSQL_DATABASE` en el `.env` de la raíz. Debe ser `sabitech_db`.
- **Access denied for user `sabitech_user`:** el contenedor se creó con otra base. Ejecuta `docker compose down -v`, revisa `.env` y vuelve a levantar.
- **`personal_access_tokens` no existe:** importa `database/Sanctum_PersonalAccessTokens.sql`.
- **El login devuelve error 500:** normalmente falta la tabla anterior o `backend/.env` apunta a otra base de datos.
- **El puerto 3306 está ocupado:** para el otro MySQL o cambia el puerto en `docker-compose.yml`.
- **`php artisan migrate` falla:** no uses migraciones en desarrollo. El esquema se importa desde `database/Sentencia Creación.txt`.
- **No veo el correo:** con `MAIL_MAILER=log`, búscalo en `backend/storage/logs/laravel.log`.
- **El QR no vale:** ha caducado. Pide uno nuevo y valídalo en menos de 30 segundos.

## Limitaciones conocidas

- El frontend todavía no consume la API.
- Los formatos de error de la API no están unificados.
- Las pruebas con SQLite no reproducen los `CHECK` ni los bloqueos de fila de MySQL.

## Seguridad

- No subir `.env` al repositorio. Ya está incluido en `.gitignore`.
- Cambiar las contraseñas de laboratorio antes de publicar el proyecto.
- Usar `APP_DEBUG=false` en la demostración.
- En producción, servir la API por HTTPS para poder usar la cámara del móvil.
