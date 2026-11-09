<?php

namespace App\Api\Controllers\Asistente;

use App\Api\DTOs\Reservas\CrearReservaData;
use App\Api\Services\Reservas\ConfirmacionReservaService;
use App\Enums\EstadoReserva;
use App\Http\Controllers\Controller;
use App\Models\Charla;
use App\Models\Reserva;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OAT;

class ReservaController extends Controller
{
    public function __construct(private readonly ConfirmacionReservaService $confirmaciones) {}

    #[OAT\Get(
        path: '/api/asistente/reservas',
        operationId: 'listarReservasAsistente',
        description: 'Reservas del usuario autenticado, de la más reciente a la más '
            .'antigua, cada una con su charla, ubicación y ponente. El secreto del QR '
            .'nunca se incluye.',
        tags: ['Asistente'],
        responses: [
            new OAT\Response(response: '200', description: 'Reservas del asistente.', content: [
                new OAT\JsonContent(type: 'array', items: new OAT\Items(ref: '#/components/schemas/Reserva')),
            ]),
            new OAT\Response(response: '401', ref: '#/components/responses/401'),
            new OAT\Response(response: '403', ref: '#/components/responses/403'),
        ],
    )]
    /**
     * Reservas del usuario autenticado, de la más reciente a la más antigua.
     */
    public function index(Request $request): JsonResponse
    {
        $reservas = $request->user()
            ->reservas()
            ->with(['charla.ubicacion', 'charla.ponente'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($reservas);
    }

    #[OAT\Post(
        path: '/api/asistente/reservas',
        operationId: 'crearReserva',
        description: 'Reserva la charla indicada para el usuario autenticado. La reserva '
            .'se queda en estado `confirmada` y ocupa una plaza. Si el usuario tenía una '
            .'reserva cancelada de esa charla, se reactiva la misma fila. Devuelve 409 '
            .'si la charla está cancelada, si ya ha empezado, si el usuario ya tiene '
            .'otra reserva para ella o si no quedan plazas.',
        tags: ['Asistente'],
        requestBody: new OAT\RequestBody(
            required: true,
            content: [new OAT\MediaType(mediaType: 'application/json', schema: new OAT\Schema(
                required: ['charla_id'],
                properties: [new OAT\Property(property: 'charla_id', type: 'integer', example: 12)],
            ))],
        ),
        responses: [
            new OAT\Response(response: '201', description: 'Reserva creada o reactivada.', content: [
                new OAT\JsonContent(ref: '#/components/schemas/Reserva'),
            ]),
            new OAT\Response(response: '401', ref: '#/components/responses/401'),
            new OAT\Response(response: '403', ref: '#/components/responses/403'),
            new OAT\Response(response: '409', ref: '#/components/responses/409'),
            new OAT\Response(response: '422', ref: '#/components/responses/422'),
        ],
    )]
    /**
     * Reserva la charla indicada para el usuario autenticado.
     *
     * La exclusión mutua sobre la fila de la charla garantiza que la
     * concurrencia de última plaza no supere el aforo.
     */
    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'charla_id' => ['required', 'integer', 'exists:charlas,id'],
        ]);

        $reservaData = CrearReservaData::fromArray($datos + [
            'usuario_id' => $request->user()->id,
        ]);

        $reserva = DB::transaction(function () use ($reservaData) {
            $charla = Charla::lockForUpdate()->findOrFail($reservaData->charlaId);

            if ($charla->cancelada) {
                abort(409, 'La charla está cancelada y no admite reservas.');
            }

            if (now()->gte($charla->inicio)) {
                abort(409, 'La charla ya ha empezado y no admite reservas.');
            }

            $existente = Reserva::where('usuario_id', $reservaData->usuarioId)
                ->where('charla_id', $reservaData->charlaId)
                ->first();

            if ($existente !== null && $existente->estado !== EstadoReserva::Cancelada) {
                abort(409, 'Ya tienes una reserva para esta charla.');
            }

            if ($charla->plazas_ocupadas >= $charla->aforo) {
                abort(409, 'No quedan plazas disponibles para esta charla.');
            }

            if ($existente !== null) {
                // Se reactiva la reserva cancelada en lugar de crear una fila
                // nueva (el índice único incluye clave_activa) y la plaza
                // vuelve a ocuparse.
                $existente->update([
                    'estado' => EstadoReserva::Confirmada,
                    'qr_secret' => self::generarQrSecret(),
                    'email_intentos' => 0,
                    'email_ultimo_envio' => null,
                ]);

                $charla->increment('plazas_ocupadas');

                return $existente;
            }

            $reserva = Reserva::create([
                'usuario_id' => $reservaData->usuarioId,
                'charla_id' => $reservaData->charlaId,
                'estado' => EstadoReserva::Confirmada,
                'qr_secret' => self::generarQrSecret(),
            ]);

            $charla->increment('plazas_ocupadas');

            return $reserva;
        });

        // La plaza queda garantizada al reservar (estado confirmada). Si el
        // correo falla la reserva no se pierde; queda pendiente de
        // reenvío y el usuario puede solicitarlo desde su listado.
        $this->confirmaciones->enviar($reserva);

        return response()->json(
            $reserva->load(['charla.ubicacion', 'charla.ponente']),
            201,
        );
    }

    #[OAT\Delete(
        path: '/api/asistente/reservas/{reserva}',
        operationId: 'cancelarReserva',
        description: 'Cancela una reserva propia y libera su plaza. Operación '
            .'idempotente: si ya estaba cancelada responde 200 sin cambios. En '
            .'reservas de otros usuarios responde 404 para no revelar su existencia.',
        tags: ['Asistente'],
        parameters: [new OAT\Parameter(name: 'reserva', description: 'Id de la reserva.', in: 'path', required: true, example: 41)],
        responses: [
            new OAT\Response(response: '200', description: 'Reserva cancelada (o ya lo estaba).', content: [
                new OAT\JsonContent(ref: '#/components/schemas/Reserva'),
            ]),
            new OAT\Response(response: '401', ref: '#/components/responses/401'),
            new OAT\Response(response: '403', ref: '#/components/responses/403'),
            new OAT\Response(response: '404', ref: '#/components/responses/404'),
        ],
    )]
    /**
     * Cancela una reserva propia y libera la plaza.
     *
     * Si ya estaba cancelada no se toca nada (operación idempotente).
     */
    public function cancelar(Request $request, Reserva $reserva): JsonResponse
    {
        if ($reserva->usuario_id !== $request->user()->id) {
            abort(404); // no revela reservas de otros usuarios
        }

        DB::transaction(function () use ($reserva) {
            $actual = Reserva::lockForUpdate()->findOrFail($reserva->id);

            if ($actual->estado === EstadoReserva::Cancelada) {
                return;
            }

            $actual->update(['estado' => EstadoReserva::Cancelada]);

            $charla = Charla::lockForUpdate()->findOrFail($actual->charla_id);

            if (! $charla->cancelada) {
                $charla->decrement('plazas_ocupadas');
            }
        });

        return response()->json(
            $reserva->fresh(['charla.ubicacion', 'charla.ponente']),
        );
    }

    /** Secreto aleatorio de 64 caracteres, único en reservas (uq_reservas_qr_secret). */
    private static function generarQrSecret(): string
    {
        return bin2hex(random_bytes(32));
    }
}
