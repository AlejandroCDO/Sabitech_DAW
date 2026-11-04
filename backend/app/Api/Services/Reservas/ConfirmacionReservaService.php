<?php

namespace App\Api\Services\Reservas;

use App\Models\Reserva;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Envía el correo de confirmación de una reserva con la vista disponible
 * (resources/views/email/confirmacion-reserva.blade.php), que recibe la
 * reserva (con charla, ubicación, ponente y usuario) y su referencia.
 *
 * Solo se registran envíos reales: si el correo falla, la reserva no se
 * toca y queda pendiente de reenvío (el fallo no consume intentos).
 */
class ConfirmacionReservaService
{
    public function enviar(Reserva $reserva): bool
    {
        $reserva->load(['charla.ubicacion', 'charla.ponente', 'usuario']);

        try {
            Mail::send('email.confirmacion-reserva', [
                'reserva' => $reserva,
                'referencia' => ReferenciaGenerator::generar($reserva),
            ], function ($mensaje) use ($reserva) {
                $mensaje->to($reserva->usuario->email, $reserva->usuario->nombre)
                    ->subject('Confirmación de reserva: '.$reserva->charla->titulo);
            });
        } catch (Throwable) {
            return false;
        }

        $reserva->update([
            'email_intentos' => $reserva->email_intentos + 1,
            'email_ultimo_envio' => now(),
        ]);

        return true;
    }
}
