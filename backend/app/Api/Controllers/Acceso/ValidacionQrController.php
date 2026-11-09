<?php

namespace App\Api\Controllers\Acceso;

use App\Api\DTOs\Acceso\ValidacionQrData;
use App\Api\Resources\Admin\AsistenciaAdminResource;
use App\Api\Services\Asistencias\AsistenciaService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;

/** Registra la asistencia a partir del QR que muestra el asistente. */
final class ValidacionQrController extends Controller
{
    #[OAT\Post(
        path: '/api/acceso/asistencias/qr',
        operationId: 'validarAsistenciaQr',
        description: 'Valida el token del QR que muestra el asistente (obtenido en '
            .'`GET /api/asistente/reservas/{reserva}/qr`) y registra su asistencia a la '
            .'charla indicada. Devuelve 422 si el token es inválido o ha caducado o '
            .'la charla no coincide; 409 si la reserva no está activa o la asistencia '
            .'ya estaba registrada.',
        tags: ['Personal de acceso'],
        requestBody: new OAT\RequestBody(
            required: true,
            content: [new OAT\MediaType(mediaType: 'application/json', schema: new OAT\Schema(
                required: ['qr_token', 'charla_id'],
                properties: [
                    new OAT\Property(property: 'qr_token', description: 'Token del QR tal y como lo devuelve la ruta del asistente.', type: 'string', maxLength: 128, example: 'a91c7d2c...3f36f4'),
                    new OAT\Property(property: 'charla_id', type: 'integer', example: 12),
                ],
            ))],
        ),
        responses: [
            new OAT\Response(response: '201', description: 'Asistencia registrada.', content: [
                new OAT\JsonContent(ref: '#/components/schemas/AsistenciaAdmin'),
            ]),
            new OAT\Response(response: '401', ref: '#/components/responses/401'),
            new OAT\Response(response: '403', ref: '#/components/responses/403'),
            new OAT\Response(response: '409', ref: '#/components/responses/409'),
            new OAT\Response(response: '422', ref: '#/components/responses/422'),
        ],
    )]
    public function __invoke(Request $request, AsistenciaService $servicio): JsonResponse
    {
        $datos = ValidacionQrData::fromArray($request->validate([
            'qr_token' => ['required', 'string', 'max:128'],
            'charla_id' => ['required', 'integer', 'exists:charlas,id'],
        ]));

        $asistencia = $servicio->registrarPorQr($datos, $request->user());

        return (new AsistenciaAdminResource($asistencia))->response()->setStatusCode(201);
    }
}
