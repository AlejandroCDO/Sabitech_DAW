# QR de acceso y correo de confirmación

Este documento fija el mecanismo único acordado. **Todavía no está implementado en el código** (ver "Pendientes").

## Mecanismo del QR

- Contenido del QR: `{reservaId}.{ventana}.{firma}`.
- `ventana = intdiv(timestamp_del_servidor, 30)`: el token cambia cada 30 segundos.
- `firma = hash_hmac('sha256', "{reservaId}.{ventana}", qr_secret)`, donde `qr_secret` es el secreto de 64 caracteres de la reserva (único, nunca se devuelve en la API).
- La validación se hace siempre en el servidor, con el reloj del servidor. El navegador solo muestra el QR y lo renueva.
- Al validar se acepta la ventana actual y la inmediatamente anterior (tolerancia de reloj y de lectura).
- Solo vale para reservas en estado `confirmada`. Las canceladas no generan ni validan QR.
- Plazo de validez: desde 30 minutos antes del inicio de la charla hasta su fin. Fuera de ese plazo se rechaza.
- Una reserva solo puede tener una asistencia (índice único `reserva_id`); un segundo escaneo se rechaza como asistencia ya registrada.

## Permisos

| Acción | Rol |
|---|---|
| Obtener el QR de una reserva propia | `asistente` (una reserva ajena responde 404) |
| Validar asistencia por QR o manualmente | `acceso` y `admin` |
| Consultar asistencias de una charla | `admin` |

## Correo de confirmación

- Al crear una reserva se intenta enviar un correo de confirmación al asistente con los datos de la reserva (charla, fecha y hora, ubicación, ponente y referencia `SAB-000123`).
- El envío se hace fuera de la transacción de la reserva: si el correo falla, la reserva se conserva y se puede reenviar.
- Cada envío realizado incrementa `email_intentos` y guarda `email_ultimo_envio`. Un envío fallido no los modifica.
- El reenvío se hace con `POST /api/asistente/reservas/{reserva}/reenviar-confirmacion` y debe limitarse en el tiempo.
- En desarrollo se usa Mailtrap u otro buzón de pruebas (`MAIL_*` en `.env`); en las pruebas automáticas, `Mail::fake()`.

## Pendientes

- `backend/app/Api/Controllers/Asistente/QrController.php` y `backend/app/Api/Services/Qr/QrTokenService.php` aún no emiten el formato `{reservaId}.{ventana}.{firma}`; el controlador usa otro (`hash_hmac(qr_secret, expira_en)`) y el servicio un tercero. La validación de acceso tampoco coincide con el token emitido.
- La plantilla `backend/resources/views/email/confirmacion-reserva.blade.php` está vacía: hoy el correo se enviaría sin contenido.
- Los errores de correo se descartan sin registrarlos en el log.
- El reenvío debe revisarse (el cálculo del tiempo transcurrido desde el último envío).
