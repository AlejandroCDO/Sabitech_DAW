# API de SABITECH (backend Laravel)

Documento basado en `backend/routes/api.php` y los controladores actuales. Todas las rutas llevan el prefijo `/api`. Las peticiones deben enviar `Accept: application/json` (el backend responde JSON en `api/*` de todos modos).

## Autenticación

Laravel Sanctum con token de API. Se obtiene en `POST /api/auth/login` y se envía en cada petición protegida:

```
Authorization: Bearer <token>
```

Los tokens no caducan automáticamente (solo se revocan con logout). El acceso por perfil lo comprueba el middleware `role:<nombres>` en el servidor; los roles son `admin`, `acceso` y `asistente`.

## Formatos de respuesta de error (estado actual)

El código actual **no tiene un formato de error único**. Hay tres:

| Origen | Cuándo | Cuerpo |
|---|---|---|
| Validación de Laravel | 422 | `{"message": "...", "errors": {"campo": ["..."]}}` |
| `abort()` / `HttpException` | 401 del middleware de rol, 403, 404, 409 de `ReservaController` | `{"message": "..."}` |
| `ApiException` y derivadas | Excepciones de `app/Api/Exceptions` (usadas por los servicios, aún no conectados a los controladores) | `{"message": "...", "code": "..."}` |

Además, Sanctum devuelve `{"message": "Unauthenticated."}` (401) cuando falta el token o no es válido. Un identificador de modelo inexistente produce 404 con el mensaje estándar de Laravel.

Unificar estos formatos está pendiente.

## Autenticación y sesión

### POST /api/auth/login
- Autenticación: ninguna.
- Entrada (JSON): `email` (obligatorio, email, máx. 150), `password` (obligatorio).
- 200: `{"token": "...", "usuario": {id, rol_id, nombre, email, telefono, fecha_nacimiento, rol: {id, nombre}}}`. El modelo se serializa tal cual (la contraseña está oculta).
- 422: credenciales incorrectas, con `errors.email` y mensaje genérico (no revela si el correo existe); también si faltan campos o el formato es inválido.

### GET /api/auth/me
- Autenticación: token.
- 200: el usuario autenticado con su `rol`.
- 401: sin token o token inválido (`{"message": "Unauthenticated."}`).

### POST /api/auth/logout
- Autenticación: token.
- 200: `{"message": "Sesión cerrada correctamente."}`; revoca el token usado.
- 401: sin token.

## Catálogo público (sin autenticación)

### GET /api/charlas
- Entrada (query): `provincia` (opcional, texto), `per_page` (opcional, 1 a 100, por defecto 15), `page`.
- Solo devuelve charlas no canceladas cuyo `fin` es posterior a la hora actual, ordenadas por `inicio`.
- 200: paginación estándar de Laravel (`data`, `links`, `meta`...). Cada elemento de `data`: `id`, `titulo`, `descripcion`, `inicio`, `fin`, `aforo`, `plazas_disponibles` (aforo menos plazas ocupadas, mínimo 0), `cancelada`, `ponente` y `ubicacion`.
- 422: `provincia` no válida (p. ej. más de 100 caracteres).

### GET /api/charlas/{charla}
- 200: `{"data": {...}}` con los mismos campos que un elemento del listado. Este endpoint también devuelve charlas ya canceladas (con `cancelada: true`).
- 404: la charla no existe.

### GET /api/provincias
- 200: `{"data": ["Alicante", "Valencia", ...]}` con las provincias que tienen alguna ubicación.

## Reservas del asistente

Autenticación: token con rol `asistente`. Sin token: 401. Con otro rol: 403 `{"message": "No tienes permisos para realizar esta acción."}`.

### GET /api/asistente/reservas
- 200: array JSON (sin envoltorio `data`) con las reservas del usuario, de la más reciente a la más antigua, con `charla` (incluidas `ubicacion` y `ponente`). Se devuelve el modelo sin Resource; `qr_secret` está oculto.

### POST /api/asistente/reservas
- Entrada (JSON): `charla_id` (obligatorio, entero, debe existir).
- 201: la reserva creada (modelo con `charla.ubicacion` y `charla.ponente`). El estado inicial es `confirmada`: la plaza queda garantizada al reservar. Suma una plaza a `plazas_ocupadas`. Si el usuario tenía una reserva cancelada de esa charla, se reactiva la misma fila (nuevo `qr_secret`) y también suma la plaza.
- 409 (`{"message": ...}`):
  - La charla está cancelada.
  - La charla ya ha empezado.
  - Ya existe una reserva no cancelada del usuario para esa charla.
  - No quedan plazas.
- 422: `charla_id` ausente, no entero o inexistente.
- Tras reservar se intenta enviar el correo de confirmación; si falla, la reserva se conserva. (La plantilla del correo está pendiente: ver `QR_Y_CORREO.md`.)

### DELETE /api/asistente/reservas/{reserva}
- Cancela una reserva propia y libera la plaza. Es idempotente en este controlador: si ya estaba cancelada, responde 200 sin cambios.
- 200: la reserva actualizada.
- 404: la reserva no existe o pertenece a otro usuario.

### GET /api/asistente/reservas/{reserva}/qr
- Token temporal del QR. **Pendiente de unificar**: el formato emitido no es el acordado (ver `QR_Y_CORREO.md`).
- 404: reserva ajena. 409: reserva cancelada, charla cancelada o ya terminada.

### POST /api/asistente/reservas/{reserva}/reenviar-confirmacion
- Reenvía el correo de confirmación. Pendiente de revisión (ver `QR_Y_CORREO.md`).

## Resto de rutas (resumen)

Todas con token. No se detallan sus cuerpos de entrada y salida porque están pendientes de unificar.

### Personal de acceso (roles `acceso` y `admin`)
- `GET /api/acceso/charlas/{charla}/reservas`: buscar reservas de una charla.
- `POST /api/acceso/asistencias/qr`: validar por QR (`qr_token`, `charla_id`).
- `POST /api/acceso/asistencias/manual`: validar manualmente (`reserva_id`, `charla_id`).

### Administración (rol `admin`)
- `GET|POST /api/admin/charlas`, `GET|PUT|PATCH|DELETE /api/admin/charlas/{charla}`
- `POST /api/admin/charlas/{charla}/cancelar`
- `GET|POST /api/admin/ponentes`, `GET|PUT|PATCH|DELETE /api/admin/ponentes/{ponente}`
- `GET|POST /api/admin/ubicaciones`, `GET|PUT|PATCH|DELETE /api/admin/ubicaciones/{ubicacion}`
- `GET /api/admin/charlas/{charla}/reservas`
- `POST /api/admin/charlas/{charla}/reservas/{reserva}/cancelar`
- `GET /api/admin/charlas/{charla}/asistencias`

Un usuario sin sesión recibe 401 y uno con otro rol recibe 403 en todas ellas.
