<?php

namespace App\Api\Controllers\Acceso;

use App\Api\DTOs\Acceso\ValidacionManualData;
use App\Api\Resources\Admin\AsistenciaAdminResource;
use App\Api\Services\Asistencias\AsistenciaService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;

/** Registra la asistencia sin QR, cuando el asistente no puede mostrar su código. */
final class ValidacionManualController extends Controller
{
    #[OAT\Post(
        path: '/api/acceso/asistencias/manual',
        operationId: 'validarAsistenciaManual',
        description: 'Registra la asistencia a partir de la reserva y la charla indicadas, '
            .'cuando el asistente no puede mostrar su QR (por ejemplo, por problemas de '
            .'batería). Devuelve 409 si la reserva no está activa, la charla no coincide '
            .'con la de la reserva o ya está registrada la asistencia.',
        tags: ['Personal de acceso'],
        requestBody: new OAT\RequestBody(
            required: true,
            content: [new OAT\MediaType(mediaType: 'application/json', schema: new OAT\Schema(
                required: ['reserva_id', 'charla_id'],
                properties: [
                    new OAT\Property(property: 'reserva_id', type: 'integer', example: 41),
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
        $datos = ValidacionManualData::fromArray($request->validate([
            'reserva_id' => ['required', 'integer', 'exists:reservas,id'],
            'charla_id' => ['required', 'integer', 'exists:charlas,id'],
        ]));

        $asistencia = $servicio->registrarManual($datos, $request->user());

        return (new AsistenciaAdminResource($asistencia))->response()->setStatusCode(201);
    }
}
