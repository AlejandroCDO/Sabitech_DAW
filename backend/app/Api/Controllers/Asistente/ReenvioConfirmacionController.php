<?php

namespace App\Api\Controllers\Asistente;

use App\Api\Services\Reservas\ConfirmacionReservaService;
use App\Enums\EstadoReserva;
use App\Http\Controllers\Controller;
use App\Models\Reserva;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OAT;

class ReenvioConfirmacionController extends Controller
{
    /** Segundos que deben pasar entre dos envíos de la misma reserva. */
    private const SEGUNDOS_MINIMOS = 60;

    /** Envíos de confirmación máximos por reserva. */
    private const INTENTOS_MAXIMOS = 5;

    public function __construct(private readonly ConfirmacionReservaService $confirmaciones) {}

    #[OAT\Post(
        path: '/api/asistente/reservas/{reserva}/reenviar-confirmacion',
        operationId: 'reenviarConfirmacion',
        description: 'Reenvía el correo de confirmación de una reserva propia, con '
            .'control anti-spam: máx. 5 envíos por reserva y al menos 60 s entre '
            .'envíos. No cambia el estado de la reserva.',
        tags: ['Asistente'],
        parameters: [new OAT\Parameter(name: 'reserva', description: 'Id de la reserva.', in: 'path', required: true, example: 41)],
        responses: [
            new OAT\Response(response: '200', description: 'Resultado del envío.', content: [new OAT\JsonContent(
                properties: [
                    new OAT\Property(property: 'email_enviado', description: 'True si el correo se envió ahora; false si el envío falló.', type: 'boolean', example: true),
                    new OAT\Property(property: 'email_intentos', description: 'Envíos reales acumulados de la reserva.', type: 'integer', example: 3),
                    new OAT\Property(property: 'email_ultimo_envio', description: 'Momento del último envío real, o null si nunca se envió.', type: 'string', format: 'date-time', nullable: true),
                ],
                type: 'object',
            )]),
            new OAT\Response(response: '401', ref: '#/components/responses/401'),
            new OAT\Response(response: '403', ref: '#/components/responses/403'),
            new OAT\Response(response: '404', ref: '#/components/responses/404'),
            new OAT\Response(response: '409', ref: '#/components/responses/409'),
            new OAT\Response(response: '429', ref: '#/components/responses/429'),
        ],
    )]

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

            if ($ultimoEnvio !== null && $ultimoEnvio->gt(now()->subSeconds(self::SEGUNDOS_MINIMOS))) {
                abort(429, 'Debes esperar un poco antes de solicitar otra confirmación.');
            }

            return $this->confirmaciones->enviar($actual);
        });

        $actualizada = $reserva->fresh();

        return response()->json([
            'email_enviado' => $enviado,
            'email_intentos' => $actualizada->email_intentos,
            'email_ultimo_envio' => $actualizada->email_ultimo_envio,
        ]);
    }
}
