# QR de acceso y correo de confirmación

## QR

- Formato del token: `{reservaId}.{ventana}.{firma}`.
- `ventana = intdiv(now()->getTimestamp(), 30)`. El token cambia cada 30 segundos.
- `firma = hash_hmac('sha256', "{reservaId}.{ventana}", reservas.qr_secret)`.
- `qr_secret` es un valor aleatorio de 64 caracteres que nunca se devuelve en la API.
- La validación se hace en el servidor. Se acepta la ventana actual y la anterior.
- Solo se valida para reservas en estado `confirmada`.
- Una reserva solo puede tener una asistencia.
- El plazo para registrar la asistencia va desde 30 minutos antes del inicio hasta el fin de la charla.

### Errores

- `422`: token inválido, caducado o de otra charla.
- `409`: reserva no activa, charla cancelada, fuera de plazo o asistencia ya registrada.

## Correo de confirmación

- Al crear una reserva se envía un correo al asistente con la charla, fecha, ubicación, ponente y la referencia `SAB-000123`.
- Si el correo falla, la reserva se conserva y se puede reenviar.
- Cada envío correcto incrementa `email_intentos` y actualiza `email_ultimo_envio`.
- El reenvío se hace con `POST /api/asistente/reservas/{reserva}/reenviar-confirmacion`.
- Hay un máximo de 5 envíos por reserva y deben pasar al menos 60 segundos entre dos envíos.
- En local, con `MAIL_MAILER=log`, el correo se guarda en `backend/storage/logs/laravel.log`.
- En Mailtrap, cambia `MAIL_MAILER` a `smtp` y rellena las variables `MAIL_*`.

## Permisos

| Acción | Rol |
|---|---|
| Obtener el QR de una reserva propia | `asistente` |
| Validar asistencia por QR o manualmente | `acceso` y `admin` |
| Consultar asistencias de una charla | `admin` |

## Pendiente

- La pantalla del frontend que muestra el QR y lo renueva automáticamente.
- Unificar el formato de todos los errores de la API.
