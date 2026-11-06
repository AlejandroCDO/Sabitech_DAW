<?php

namespace App\Api\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ubicacion;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;

class UbicacionController extends Controller
{
    #[OAT\Get(
        path: '/api/admin/ubicaciones',
        operationId: 'adminListarUbicaciones',
        description: 'Listado de ubicaciones ordenado por provincia y municipio.',
        tags: ['Administración'],
        parameters: [
            new OAT\QueryParameter(name: 'provincia', description: 'Filtrar por provincia exacta.', required: false, example: 'Alicante'),
            new OAT\QueryParameter(name: 'municipio', description: 'Filtrar por municipio exacto.', required: false, example: 'San Vicente del Raspeig'),
            new OAT\QueryParameter(name: 'per_page', description: 'Elementos por página (entre 1 y 100, por defecto 15).', required: false, example: 15),
            new OAT\QueryParameter(name: 'page', description: 'Página a consultar.', required: false, example: 1),
        ],
        responses: [
            new OAT\Response(response: '200', description: 'Listado paginado de ubicaciones.', content: [
                new OAT\JsonContent(
                    type: 'object',
                    properties: [
                        new OAT\Property(property: 'data', type: 'array', items: new OAT\Items(ref: '#/components/schemas/Ubicacion')),
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

        $ubicaciones = Ubicacion::query()
            ->when($request->filled('provincia'), fn ($q) => $q->where('provincia', $request->string('provincia')))
            ->when($request->filled('municipio'), fn ($q) => $q->where('municipio', $request->string('municipio')))
            ->orderBy('provincia')
            ->orderBy('municipio')
            ->paginate($perPage);

        return response()->json($ubicaciones);
    }

    #[OAT\Get(
        path: '/api/admin/ubicaciones/{ubicacion}',
        operationId: 'adminMostrarUbicacion',
        tags: ['Administración'],
        parameters: [new OAT\Parameter(name: 'ubicacion', description: 'Id de la ubicación.', in: 'path', required: true, example: 2)],
        responses: [
            new OAT\Response(response: '200', description: 'Ubicación completa.', content: [
                new OAT\JsonContent(ref: '#/components/schemas/Ubicacion'),
            ]),
            new OAT\Response(response: '401', ref: '#/components/responses/401'),
            new OAT\Response(response: '403', ref: '#/components/responses/403'),
            new OAT\Response(response: '404', ref: '#/components/responses/404'),
        ],
    )]
    public function show(Ubicacion $ubicacion): JsonResponse
    {
        return response()->json($ubicacion);
    }

    #[OAT\Post(
        path: '/api/admin/ubicaciones',
        operationId: 'adminCrearUbicacion',
        tags: ['Administración'],
        requestBody: new OAT\RequestBody(
            required: true,
            content: [new OAT\MediaType(mediaType: 'application/json', schema: new OAT\Schema(
                required: ['nombre', 'provincia', 'municipio', 'calle', 'codigo_postal', 'numero'],
                properties: [
                    new OAT\Property(property: 'nombre', type: 'string', maxLength: 50, example: 'Aula Magna'),
                    new OAT\Property(property: 'provincia', type: 'string', maxLength: 100, example: 'Alicante'),
                    new OAT\Property(property: 'municipio', type: 'string', maxLength: 100, example: 'San Vicente del Raspeig'),
                    new OAT\Property(property: 'calle', type: 'string', maxLength: 100, example: 'Calle Alcalá de Henares'),
                    new OAT\Property(property: 'codigo_postal', type: 'string', maxLength: 10, example: '03690'),
                    new OAT\Property(property: 'numero', type: 'string', maxLength: 10, example: '1'),
                    new OAT\Property(property: 'puerta', type: 'string', maxLength: 10, nullable: true, example: '2B'),
                ],
            ))],
        ),
        responses: [
            new OAT\Response(response: '201', description: 'Ubicación creada.', content: [
                new OAT\JsonContent(ref: '#/components/schemas/Ubicacion'),
            ]),
            new OAT\Response(response: '401', ref: '#/components/responses/401'),
            new OAT\Response(response: '403', ref: '#/components/responses/403'),
            new OAT\Response(response: '422', ref: '#/components/responses/422'),
        ],
    )]
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:50'],
            'provincia' => ['required', 'string', 'max:100'],
            'municipio' => ['required', 'string', 'max:100'],
            'calle' => ['required', 'string', 'max:100'],
            'codigo_postal' => ['required', 'string', 'max:10'],
            'numero' => ['required', 'string', 'max:10'],
            'puerta' => ['nullable', 'string', 'max:10'],
        ]);

        return response()->json(Ubicacion::create($data)->refresh(), 201);
    }

    #[OAT\Put(
        path: '/api/admin/ubicaciones/{ubicacion}',
        operationId: 'adminActualizarUbicacion',
        description: 'Actualiza los campos enviados. También responde PATCH.',
        tags: ['Administración'],
        parameters: [new OAT\Parameter(name: 'ubicacion', description: 'Id de la ubicación.', in: 'path', required: true, example: 2)],
        requestBody: new OAT\RequestBody(
            required: true,
            description: 'Solo los campos que se quieren cambiar.',
            content: [new OAT\MediaType(mediaType: 'application/json', schema: new OAT\Schema(
                properties: [
                    new OAT\Property(property: 'nombre', type: 'string', maxLength: 50, example: 'Aula Magna'),
                    new OAT\Property(property: 'provincia', type: 'string', maxLength: 100, example: 'Alicante'),
                    new OAT\Property(property: 'municipio', type: 'string', maxLength: 100),
                    new OAT\Property(property: 'calle', type: 'string', maxLength: 100),
                    new OAT\Property(property: 'codigo_postal', type: 'string', maxLength: 10, example: '03690'),
                    new OAT\Property(property: 'numero', type: 'string', maxLength: 10, example: '1'),
                    new OAT\Property(property: 'puerta', type: 'string', maxLength: 10, nullable: true, example: '2B'),
                ],
            ))],
        ),
        responses: [
            new OAT\Response(response: '200', description: 'Ubicación actualizada.', content: [
                new OAT\JsonContent(ref: '#/components/schemas/Ubicacion'),
            ]),
            new OAT\Response(response: '401', ref: '#/components/responses/401'),
            new OAT\Response(response: '403', ref: '#/components/responses/403'),
            new OAT\Response(response: '404', ref: '#/components/responses/404'),
            new OAT\Response(response: '422', ref: '#/components/responses/422'),
        ],
    )]
    public function update(Request $request, Ubicacion $ubicacion): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:50'],
            'provincia' => ['sometimes', 'string', 'max:100'],
            'municipio' => ['sometimes', 'string', 'max:100'],
            'calle' => ['sometimes', 'string', 'max:100'],
            'codigo_postal' => ['sometimes', 'string', 'max:10'],
            'numero' => ['sometimes', 'string', 'max:10'],
            'puerta' => ['nullable', 'string', 'max:10'],
        ]);

        $ubicacion->update($data);

        return response()->json($ubicacion);
    }

    #[OAT\Delete(
        path: '/api/admin/ubicaciones/{ubicacion}',
        operationId: 'adminEliminarUbicacion',
        description: 'Elimina físicamente la ubicación. Solo si no tiene charlas '
            .'asociadas; en caso contrario responde 409.',
        tags: ['Administración'],
        parameters: [new OAT\Parameter(name: 'ubicacion', description: 'Id de la ubicación.', in: 'path', required: true, example: 2)],
        responses: [
            new OAT\Response(response: '204', description: 'Ubicación eliminada.'),
            new OAT\Response(response: '401', ref: '#/components/responses/401'),
            new OAT\Response(response: '403', ref: '#/components/responses/403'),
            new OAT\Response(response: '404', ref: '#/components/responses/404'),
            new OAT\Response(response: '409', ref: '#/components/responses/409'),
        ],
    )]
    public function destroy(Ubicacion $ubicacion): JsonResponse
    {
        if ($ubicacion->charlas()->exists()) {
            return response()->json([
                'message' => 'La ubicación tiene charlas asociadas y no se puede eliminar.',
            ], 409);
        }

        try {
            $ubicacion->delete();
        } catch (QueryException) {
            return response()->json([
                'message' => 'La ubicación tiene charlas asociadas y no se puede eliminar.',
            ], 409);
        }

        return response()->json(null, 204);
    }
}
