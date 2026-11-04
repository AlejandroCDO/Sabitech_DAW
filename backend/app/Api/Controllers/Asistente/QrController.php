<?php

namespace App\Api\Controllers\Asistente;

use App\Api\DTOs\Qr\QrTokenData;
use App\Enums\EstadoReserva;
use App\Http\Controllers\Controller;
use App\Models\Reserva;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QrController extends Controller
{
    /**
     * Token temporal del QR de una reserva propia y activa.
     *
     * La validez caduca en la próxima frontera de 30 s (máximo 30 s), de
     * modo que la renovación en pantalla coincida con una caducidad real
     * comprobable en el servidor.
     *
     * El token es compatible con la validación del personal de acceso:
     * token = hash_hmac('sha256', qr_secret, expira_en), de modo que
     * ValidaciónQr puede recomputarlo para la ventana actual (y tolerar la
     * anterior) sin guardar estado.
     */
    public function mostrar(Request $request, Reserva $reserva): JsonResponse
    {
        if ($reserva->usuario_id !== $request->user()->id) {
            abort(404); // no revela reservas de otros usuarios
        }

        if ($reserva->estado === EstadoReserva::Cancelada) {
            abort(409, 'La reserva está cancelada y no genera QR.');
        }

        $charla = $reserva->charla;

        if ($charla === null || $charla->cancelada) {
            abort(409, 'La charla está cancelada y no genera QR.');
        }

        if (now()->gte($charla->fin)) {
            abort(409, 'La charla ya ha terminado y no genera QR.');
        }

        // La ventana se ancla a fronteras globales de 30 s del reloj del
        // servidor: así la validación puede recomputar el token para la
        // ventana actual (y tolerar la anterior) sin más estado.
        $ventanaActual = intdiv(now()->getTimestamp(), QrTokenData::CADUCIDAD_SEGUNDOS);
        $expiraEn = now()
            ->setTimestamp(($ventanaActual + 1) * QrTokenData::CADUCIDAD_SEGUNDOS);

        $qrToken = QrTokenData::fromArray([
            'reserva_id' => $reserva->id,
            'token' => hash_hmac('sha256', $reserva->qr_secret, (string) $expiraEn->getTimestamp()),
            'expira_en' => $expiraEn,
        ]);

        return response()->json($qrToken->toArray());
    }
}
