<?php

namespace App\Api\Controllers\Asistente;

use App\Enums\EstadoReserva;
use App\Http\Controllers\Controller;
use App\Models\Reserva;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ReenvioConfirmacionController extends Controller
{
    /** Segundos que deben pasar entre dos envíos de la misma reserva. */
    private const SEGUNDOS_MINIMOS = 60;

    /** Envíos de confirmación máximos por reserva. */
    private const INTENTOS_MAXIMOS = 5;

    /**
     * Reenvía la confirmación de una reserva propia, controlado para que
     * nadie lo use como spam y sin crear una segunda inscripción.
     */
    public function reenviar(Request $request, Reserva $reserva): JsonResponse
    {
        if ($reserva->usuario_id !== $request->user()->id) {
            abort(404); // no revela reservas de otros usuarios
        }

        if ($reserva->estado === EstadoReserva::Cancelada) {
            abort(409, 'No se puede reenviar la confirmación de una reserva cancelada.');
        }

        $enviado = DB::transaction(function () use ($reserva) {
            // El bloqueo evita que dos peticiones simultáneas entren en
            // la misma ventana de tiempo y consuman intentos de más.
            $actual = Reserva::lockForUpdate()->findOrFail($reserva->id);

            if ($actual->email_intentos >= self::INTENTOS_MAXIMOS) {
                abort(429, 'Se ha alcanzado el número máximo de envíos de confirmación.');
            }

            $ultimoEnvio = $actual->email_ultimo_envio;

            if ($ultimoEnvio !== null && now()->diffInSeconds($ultimoEnvio) < self::SEGUNDOS_MINIMOS) {
                abort(429, 'Debes esperar un poco antes de solicitar otra confirmación.');
            }

            return $this->enviarConfirmacion($actual);
        });

        $actualizada = $reserva->fresh();

        return response()->json([
            'email_enviado' => $enviado,
            'email_intentos' => $actualizada->email_intentos,
            'email_ultimo_envio' => $actualizada->email_ultimo_envio,
        ]);
    }

    /**
     * Envía el correo de confirmación con la vista disponible
     * (resources/views/email/confirmacion-reserva.blade.php), que recibirá
     * la reserva (con charla, ubicación, ponente y usuario) y la referencia.
     *
     * Solo se registran envíos reales; un fallo del correo no consume intentos.
     */
    private function enviarConfirmacion(Reserva $reserva): bool
    {
        $reserva->load(['charla.ubicacion', 'charla.ponente', 'usuario']);

        try {
            Mail::send('email.confirmacion-reserva', [
                'reserva' => $reserva,
                'referencia' => self::referenciaDe($reserva),
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

    /** Referencia estable asociada a la reserva (SAB-000031). */
    private static function referenciaDe(Reserva $reserva): string
    {
        return 'SAB-'.str_pad((string) $reserva->id, 6, '0', STR_PAD_LEFT);
    }
}
