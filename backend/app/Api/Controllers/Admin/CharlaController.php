<?php

namespace App\Api\Controllers\Admin;

use App\Enums\EstadoReserva;
use App\Http\Controllers\Controller;
use App\Models\Charla;
use App\Models\Reserva;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OAT;

class CharlaController extends Controller
{
    #[OAT\Get(
        path: '/api/admin/charlas',
        operationId: 'adminListarCharlas',
        description: 'Listado completo de charlas (incluye las canceladas y las pasadas), '
            .'ordenadas por inicio, con ponente y ubicación resumidos.',
        tags: ['Administración'],
        parameters: [
            new OAT\QueryParameter(name: 'ponente_id', description: 'Filtrar por id de ponente.', required: false, example: 3),
            new OAT\QueryParameter(name: 'ubicacion_id', description: 'Filtrar por id de ubicación.', required: false, example: 2),
            new OAT\QueryParameter(name: 'solo_activas', description: 'Si vale 1, solo charlas no canceladas.', required: false, example: 1),
            new OAT\QueryParameter(name: 'per_page', description: 'Elementos por página (entre 1 y 100, por defecto 15).', required: false, example: 15),
            new OAT\QueryParameter(name: 'page', description: 'Página a consultar.', required: false, example: 1),
        ],
        responses: [
            new OAT\Response(response: '200', description: 'Listado paginado de charlas.', content: [
                new OAT\JsonContent(
                    type: 'object',
                    properties: [
                        new OAT\Property(property: 'data', type: 'array', items: new OAT\Items(
                            ref: '#/components/schemas/CharlaAdmin',
                        )),
                        new OAT\Property(property: 'meta', ref: '#/components/schemas/MetaPaginacion'),
                        new OAT\Property(property: 'links', ref: '#/components/schemas/UrlPaginacion'),
                    ],
                ),
            ]),
            new OAT\Response(response: '401', ref: '#/components/responses/401'),
            new OAT\Response(response: '403', ref: '#/components/responses/403'),
        ],
    )]
    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min($request->integer('per_page', 15), 100));

        $charlas = Charla::with(['ponente:id,nombre', 'ubicacion:id,nombre,municipio'])
            ->when($request->filled('ponente_id'), fn ($q) => $q->where('ponente_id', $request->integer('ponente_id')))
            ->when($request->filled('ubicacion_id'), fn ($q) => $q->where('ubicacion_id', $request->integer('ubicacion_id')))
            ->when($request->boolean('solo_activas'), fn ($q) => $q->where('cancelada', false))
            ->orderBy('inicio')
            ->paginate($perPage);

        return response()->json($charlas);
    }

    #[OAT\Get(
        path: '/api/admin/charlas/{charla}',
        operationId: 'adminMostrarCharla',
        description: 'Detalle de una charla con su ponente y su ubicación completos.',
        tags: ['Administración'],
        parameters: [new OAT\Parameter(name: 'charla', description: 'Id de la charla.', in: 'path', required: true, example: 12)],
        responses: [
            new OAT\Response(response: '200', description: 'Detalle de la charla.', content: [
                new OAT\JsonContent(ref: '#/components/schemas/CharlaAdmin'),
            ]),
            new OAT\Response(response: '401', ref: '#/components/responses/401'),
            new OAT\Response(response: '403', ref: '#/components/responses/403'),
            new OAT\Response(response: '404', ref: '#/components/responses/404'),
        ],
    )]
    public function show(Charla $charla): JsonResponse
    {
        return response()->json($charla->load(['ponente', 'ubicacion']));
    }

    #[OAT\Post(
        path: '/api/admin/charlas',
        operationId: 'adminCrearCharla',
        description: 'Crea una charla nueva con `plazas_ocupadas` 0 y `cancelada` false.',
        tags: ['Administración'],
        requestBody: new OAT\RequestBody(
            required: true,
            content: [new OAT\MediaType(mediaType: 'application/json', schema: new OAT\Schema(
                required: ['ponente_id', 'ubicacion_id', 'titulo', 'descripcion', 'inicio', 'fin', 'aforo'],
                properties: [
                    new OAT\Property(property: 'ponente_id', type: 'integer', example: 3),
                    new OAT\Property(property: 'ubicacion_id', type: 'integer', example: 2),
                    new OAT\Property(property: 'titulo', type: 'string', maxLength: 100, example: 'Introducción a la IA'),
                    new OAT\Property(property: 'descripcion', type: 'string'),
                    new OAT\Property(property: 'inicio', description: 'Momento en el que empieza.', type: 'string', format: 'date-time', example: '2026-11-20 10:00:00'),
                    new OAT\Property(property: 'fin', description: 'Momento en el que termina; debe ser posterior a `inicio`.', type: 'string', format: 'date-time', example: '2026-11-20 12:00:00'),
                    new OAT\Property(property: 'aforo', description: 'Aforo máximo de plazas (al menos 1).', type: 'integer', minimum: 1, maximum: 2147483647, example: 50),
                ],
            ))],
        ),
        responses: [
            new OAT\Response(response: '201', description: 'Charla creada, con ponente y ubicación cargados.', content: [
                new OAT\JsonContent(ref: '#/components/schemas/CharlaAdmin'),
            ]),
            new OAT\Response(response: '401', ref: '#/components/responses/401'),
            new OAT\Response(response: '403', ref: '#/components/responses/403'),
            new OAT\Response(response: '422', ref: '#/components/responses/422'),
        ],
    )]
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ponente_id' => ['required', 'integer', 'exists:ponentes,id'],
            'ubicacion_id' => ['required', 'integer', 'exists:ubicaciones,id'],
            'titulo' => ['required', 'string', 'max:100'],
            'descripcion' => ['required', 'string'],
            'inicio' => ['required', 'date'],
            'fin' => ['required', 'date', 'after:inicio'],   // CHECK (fin > inicio)
            'aforo' => ['required', 'integer', 'min:1', 'max:2147483647'],      // CHECK (aforo > 0)
        ]);

        $charla = Charla::create($data + [
            'plazas_ocupadas' => 0,
            'cancelada' => false,
        ]);

        return response()->json($charla->load(['ponente', 'ubicacion']), 201);
    }

    #[OAT\Put(
        path: '/api/admin/charlas/{charla}',
        operationId: 'adminActualizarCharla',
        description: 'Actualiza los campos enviados de una charla. Comprueba sobre la fila '
            .'bloqueada que `fin` sea posterior a `inicio` y que el nuevo aforo no sea '
            .'menor que las plazas ya ocupadas (si lo es, 422). También responde PATCH.',
        tags: ['Administración'],
        parameters: [new OAT\Parameter(name: 'charla', description: 'Id de la charla.', in: 'path', required: true, example: 12)],
        requestBody: new OAT\RequestBody(
            required: true,
            description: 'Solo los campos que se quieren cambiar.',
            content: [new OAT\MediaType(mediaType: 'application/json', schema: new OAT\Schema(
                properties: [
                    new OAT\Property(property: 'ponente_id', type: 'integer', example: 4),
                    new OAT\Property(property: 'ubicacion_id', type: 'integer', example: 2),
                    new OAT\Property(property: 'titulo', type: 'string', maxLength: 100, example: 'Título actualizado'),
                    new OAT\Property(property: 'descripcion', type: 'string'),
                    new OAT\Property(property: 'inicio', type: 'string', format: 'date-time', example: '2026-11-21 10:00:00'),
                    new OAT\Property(property: 'fin', type: 'string', format: 'date-time', example: '2026-11-21 12:00:00'),
                    new OAT\Property(property: 'aforo', type: 'integer', minimum: 1, example: 60),
                ],
            ))],
        ),
        responses: [
            new OAT\Response(response: '200', description: 'Charla actualizada, con ponente y ubicación cargados.', content: [
                new OAT\JsonContent(ref: '#/components/schemas/CharlaAdmin'),
            ]),
            new OAT\Response(response: '401', ref: '#/components/responses/401'),
            new OAT\Response(response: '403', ref: '#/components/responses/403'),
            new OAT\Response(response: '404', ref: '#/components/responses/404'),
            new OAT\Response(response: '422', ref: '#/components/responses/422'),
        ],
    )]
    public function update(Request $request, Charla $charla): JsonResponse
    {
        $data = $request->validate([
            'ponente_id' => ['sometimes', 'integer', 'exists:ponentes,id'],
            'ubicacion_id' => ['sometimes', 'integer', 'exists:ubicaciones,id'],
            'titulo' => ['sometimes', 'string', 'max:100'],
            'descripcion' => ['sometimes', 'string'],
            'inicio' => ['sometimes', 'date'],
            'fin' => ['sometimes', 'date'],
            'aforo' => ['sometimes', 'integer', 'min:1', 'max:2147483647'],
        ]);

        DB::transaction(function () use ($data, $charla) {
            // Bloqueamos la fila para que no entre una reserva mientras cambiamos el aforo
            $actual = Charla::lockForUpdate()->findOrFail($charla->id);

            $inicio = Carbon::parse($data['inicio'] ?? $actual->inicio);
            $fin = Carbon::parse($data['fin'] ?? $actual->fin);

            if ($fin->lessThanOrEqualTo($inicio)) {
                throw ValidationException::withMessages([
                    'fin' => 'La fecha de fin debe ser posterior a la de inicio.',
                ]);
            }

            if (isset($data['aforo']) && $data['aforo'] < $actual->plazas_ocupadas) {
                throw ValidationException::withMessages([
                    'aforo' => "El aforo no puede ser menor que las plazas ya ocupadas ({$actual->plazas_ocupadas}).",
                ]);
            }

            $actual->update($data);
        });

        return response()->json($charla->fresh()->load(['ponente', 'ubicacion']));
    }

    #[OAT\Post(
        path: '/api/admin/charlas/{charla}/cancelar',
        operationId: 'adminCancelarCharla',
        description: 'Cancela la charla y sus reservas activas, y pone `plazas_ocupadas` '
            .'a 0. Idempotente: si ya estaba cancelada responde 200 sin cambios.',
        tags: ['Administración'],
        parameters: [new OAT\Parameter(name: 'charla', description: 'Id de la charla.', in: 'path', required: true, example: 12)],
        responses: [
            new OAT\Response(response: '200', description: 'Charla cancelada.', content: [
                new OAT\JsonContent(ref: '#/components/schemas/CharlaAdmin'),
            ]),
            new OAT\Response(response: '401', ref: '#/components/responses/401'),
            new OAT\Response(response: '403', ref: '#/components/responses/403'),
            new OAT\Response(response: '404', ref: '#/components/responses/404'),
        ],
    )]
    /**
     * Cancela la charla y todas sus reservas activas.
     */
    public function cancelar(Charla $charla): JsonResponse
    {
        DB::transaction(function () use ($charla) {
            $actual = Charla::lockForUpdate()->findOrFail($charla->id);

            if ($actual->cancelada) {
                return;
            }

            Reserva::where('charla_id', $actual->id)
                ->activas()->update(['estado' => EstadoReserva::Cancelada->value]);

            $actual->update(['cancelada' => true, 'plazas_ocupadas' => 0]);
        });

        return response()->json($charla->fresh());
    }

    #[OAT\Delete(
        path: '/api/admin/charlas/{charla}',
        operationId: 'adminEliminarCharla',
        description: 'Elimina físicamente la charla. Solo si no tiene reservas: si '
            .'tienen reservas responde 409 y hay que cancelarla en su lugar.',
        tags: ['Administración'],
        parameters: [new OAT\Parameter(name: 'charla', description: 'Id de la charla.', in: 'path', required: true, example: 12)],
        responses: [
            new OAT\Response(response: '204', description: 'Charla eliminada.'),
            new OAT\Response(response: '401', ref: '#/components/responses/401'),
            new OAT\Response(response: '403', ref: '#/components/responses/403'),
            new OAT\Response(response: '404', ref: '#/components/responses/404'),
            new OAT\Response(response: '409', ref: '#/components/responses/409'),
        ],
    )]
    public function destroy(Charla $charla): JsonResponse
    {
        if ($charla->reservas()->exists()) {
            return response()->json([
                'message' => 'La charla tiene reservas. Cancélala en lugar de borrarla.',
            ], 409);
        }

        try {
            $charla->delete();
        } catch (QueryException) {
            return response()->json([
                'message' => 'La charla tiene reservas. Cancélala en lugar de borrarla.',
            ], 409);
        }

        return response()->json(null, 204);
    }
}
