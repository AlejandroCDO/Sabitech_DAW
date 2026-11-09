<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmación de reserva</title>
</head>
<body style="font-family: Arial, sans-serif; color: #222; line-height: 1.5;">
    <h1 style="color: #1d4ed8;">Reserva confirmada</h1>

    <p>Hola {{ $reserva->usuario->nombre }},</p>

    <p>Tu reserva se ha registrado correctamente.</p>

    <table cellpadding="6" cellspacing="0" style="border-collapse: collapse; border: 1px solid #ddd;">
        <tr>
            <td><strong>Referencia</strong></td>
            <td>{{ $referencia }}</td>
        </tr>
        <tr>
            <td><strong>Charla</strong></td>
            <td>{{ $reserva->charla->titulo }}</td>
        </tr>
        <tr>
            <td><strong>Fecha y hora</strong></td>
            <td>{{ $reserva->charla->inicio->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td><strong>Ubicación</strong></td>
            <td>
                {{ $reserva->charla->ubicacion->nombre }}<br>
                {{ $reserva->charla->ubicacion->calle }} {{ $reserva->charla->ubicacion->numero }}
                @if ($reserva->charla->ubicacion->puerta)
                    , puerta {{ $reserva->charla->ubicacion->puerta }}
                @endif
                <br>
                {{ $reserva->charla->ubicacion->municipio }} ({{ $reserva->charla->ubicacion->provincia }})
            </td>
        </tr>
        <tr>
            <td><strong>Ponente</strong></td>
            <td>{{ $reserva->charla->ponente->nombre }}</td>
        </tr>
    </table>

    <p>Este correo confirma la reserva, pero no sustituye al registro de asistencia.</p>

    <p>Un saludo,<br>SABITECH</p>
</body>
</html>
