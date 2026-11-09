<?php

namespace App\Api\Controllers\Asistente;

use App\Api\DTOs\Qr\QrTokenData;
use App\Enums\EstadoReserva;
use App\Http\Controllers\Controller;
use App\Models\Reserva;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;

class QrController extends Controller
{
    /**
     * Token temporal del QR de una reserva propia y activa.
     *
     * La validez caduca en la próxima frontera de 30 s (máximo 30 s), de
     * modo que la renovación en pantalla coincida con una caducidad real
     * comprobable en el servidor.
     *
     * Formato: {reservaId}.{ventana}.{firma}
     * firma = hash_hmac('sha256', "{reservaId}.{ventana}", reservas.qr_secret).
     */
    #[OAT\Get(
        path: '/api/asistente/reservas/{reserva}/qr',
        operationId: 'obtenerQrReserva',
        description: 'Token HMAC temporal del QR de una reserva propia, con el '
            .'momento exacto de caducidad (frontera global de 30 s). Falla con 409 '
            .'si la reserva o su charla están canceladas o la charla ya terminó.',
        tags: ['Asistente'],
        parameters: [new OAT\Parameter(name: 'reserva', description: 'Id de la reserva.', in: 'path', required: true, example: 41)],
        responses: [
            new OAT\Response(response: '200', description: 'Token del QR con su caducidad.', content: [
                new OAT\JsonContent(ref: '#/components/schemas/QrToken'),
            ]),
            new OAT\Response(response: '401', ref: '#/components/responses/401'),
            new OAT\Response(response: '403', ref: '#/components/responses/403'),
            new OAT\Response(response: '404', ref: '#/components/responses/404'),
            new OAT\Response(response: '409', ref: '#/components/responses/409'),
        ],
    )]
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

        $firma = hash_hmac('sha256', $reserva->id.'.'.$ventanaActual, $reserva->qr_secret);

        $qrToken = QrTokenData::fromArray([
            'reserva_id' => $reserva->id,
            'token' => $reserva->id.'.'.$ventanaActual.'.'.$firma,
            'expira_en' => $expiraEn,
        ]);

        return response()->json($qrToken->toArray());
    }
}
