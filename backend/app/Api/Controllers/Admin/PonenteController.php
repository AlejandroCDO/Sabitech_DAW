<?php

namespace App\Api\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ponente;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;

class PonenteController extends Controller
{
    #[OAT\Get(
        path: '/api/admin/ponentes',
        operationId: 'adminListarPonentes',
        description: 'Listado de ponentes ordenado por nombre.',
        tags: ['Administración'],
        parameters: [
            new OAT\QueryParameter(name: 'q', description: 'Filtrar por coincidencia en el nombre.', required: false, example: 'Ada'),
            new OAT\QueryParameter(name: 'per_page', description: 'Elementos por página (entre 1 y 100, por defecto 15).', required: false, example: 15),
            new OAT\QueryParameter(name: 'page', description: 'Página a consultar.', required: false, example: 1),
        ],
        responses: [
            new OAT\Response(response: '200', description: 'Listado paginado de ponentes.', content: [
                new OAT\JsonContent(
                    type: 'object',
                    properties: [
                        new OAT\Property(property: 'data', type: 'array', items: new OAT\Items(ref: '#/components/schemas/Ponente')),
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

        $ponentes = Ponente::query()
            ->when($request->filled('q'), fn ($q) => $q->where('nombre', 'like', '%'.$request->string('q').'%'))
            ->orderBy('nombre')
            ->paginate($perPage);

        return response()->json($ponentes);
    }

    #[OAT\Get(
        path: '/api/admin/ponentes/{ponente}',
        operationId: 'adminMostrarPonente',
        tags: ['Administración'],
        parameters: [new OAT\Parameter(name: 'ponente', description: 'Id del ponente.', in: 'path', required: true, example: 3)],
        responses: [
            new OAT\Response(response: '200', description: 'Ponente completo.', content: [
                new OAT\JsonContent(ref: '#/components/schemas/Ponente'),
            ]),
            new OAT\Response(response: '401', ref: '#/components/responses/401'),
            new OAT\Response(response: '403', ref: '#/components/responses/403'),
            new OAT\Response(response: '404', ref: '#/components/responses/404'),
        ],
    )]
    public function show(Ponente $ponente): JsonResponse
    {
        return response()->json($ponente);
    }

    #[OAT\Post(
        path: '/api/admin/ponentes',
        operationId: 'adminCrearPonente',
        tags: ['Administración'],
        requestBody: new OAT\RequestBody(
            required: true,
            content: [new OAT\MediaType(mediaType: 'application/json', schema: new OAT\Schema(
                required: ['nombre'],
                properties: [
                    new OAT\Property(property: 'nombre', type: 'string', maxLength: 100, example: 'Ada Lovelace'),
                    new OAT\Property(property: 'biografia', type: 'string', nullable: true),
                    new OAT\Property(property: 'foto_url', description: 'URL de la foto (http o https).', type: 'string', format: 'uri', maxLength: 255, nullable: true),
                    new OAT\Property(property: 'especialidad', type: 'string', maxLength: 100, nullable: true, example: 'Inteligencia artificial'),
                    new OAT\Property(property: 'linkedin_url', description: 'URL de LinkedIn (http o https).', type: 'string', format: 'uri', maxLength: 255, nullable: true),
                ],
            ))],
        ),
        responses: [
            new OAT\Response(response: '201', description: 'Ponente creado.', content: [
                new OAT\JsonContent(ref: '#/components/schemas/Ponente'),
            ]),
            new OAT\Response(response: '401', ref: '#/components/responses/401'),
            new OAT\Response(response: '403', ref: '#/components/responses/403'),
            new OAT\Response(response: '422', ref: '#/components/responses/422'),
        ],
    )]
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'biografia' => ['nullable', 'string'],
            'foto_url' => ['nullable', 'url:http,https', 'max:255'],
            'especialidad' => ['nullable', 'string', 'max:100'],
            'linkedin_url' => ['nullable', 'url:http,https', 'max:255'],
        ]);

        return response()->json(Ponente::create($data)->refresh(), 201);
    }

    #[OAT\Put(
        path: '/api/admin/ponentes/{ponente}',
        operationId: 'adminActualizarPonente',
        description: 'Actualiza los campos enviados. También responde PATCH.',
        tags: ['Administración'],
        parameters: [new OAT\Parameter(name: 'ponente', description: 'Id del ponente.', in: 'path', required: true, example: 3)],
        requestBody: new OAT\RequestBody(
            required: true,
            description: 'Solo los campos que se quieren cambiar.',
            content: [new OAT\MediaType(mediaType: 'application/json', schema: new OAT\Schema(
                properties: [
                    new OAT\Property(property: 'nombre', type: 'string', maxLength: 100, example: 'Ada Lovelace'),
                    new OAT\Property(property: 'biografia', type: 'string', nullable: true),
                    new OAT\Property(property: 'foto_url', description: 'URL de la foto (http o https).', type: 'string', format: 'uri', maxLength: 255, nullable: true),
                    new OAT\Property(property: 'especialidad', type: 'string', maxLength: 100, nullable: true),
                    new OAT\Property(property: 'linkedin_url', description: 'URL de LinkedIn (http o https).', type: 'string', format: 'uri', maxLength: 255, nullable: true),
                ],
            ))],
        ),
        responses: [
            new OAT\Response(response: '200', description: 'Ponente actualizado.', content: [
                new OAT\JsonContent(ref: '#/components/schemas/Ponente'),
            ]),
            new OAT\Response(response: '401', ref: '#/components/responses/401'),
            new OAT\Response(response: '403', ref: '#/components/responses/403'),
            new OAT\Response(response: '404', ref: '#/components/responses/404'),
            new OAT\Response(response: '422', ref: '#/components/responses/422'),
        ],
    )]
    public function update(Request $request, Ponente $ponente): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:100'],
            'biografia' => ['nullable', 'string'],
            'foto_url' => ['nullable', 'url:http,https', 'max:255'],
            'especialidad' => ['nullable', 'string', 'max:100'],
            'linkedin_url' => ['nullable', 'url:http,https', 'max:255'],
        ]);

        $ponente->update($data);

        return response()->json($ponente);
    }

    #[OAT\Delete(
        path: '/api/admin/ponentes/{ponente}',
        operationId: 'adminEliminarPonente',
        description: 'Elimina físicamente el ponente. Solo si no tiene charlas asignadas; '
            .'en caso contrario responde 409.',
        tags: ['Administración'],
        parameters: [new OAT\Parameter(name: 'ponente', description: 'Id del ponente.', in: 'path', required: true, example: 3)],
        responses: [
            new OAT\Response(response: '204', description: 'Ponente eliminado.'),
            new OAT\Response(response: '401', ref: '#/components/responses/401'),
            new OAT\Response(response: '403', ref: '#/components/responses/403'),
            new OAT\Response(response: '404', ref: '#/components/responses/404'),
            new OAT\Response(response: '409', ref: '#/components/responses/409'),
        ],
    )]
    public function destroy(Ponente $ponente): JsonResponse
    {
        if ($ponente->charlas()->exists()) {
            return response()->json([
                'message' => 'El ponente tiene charlas asignadas y no se puede eliminar.',
            ], 409);
        }

        try {
            $ponente->delete();
        } catch (QueryException) {
            return response()->json([
                'message' => 'El ponente tiene charlas asignadas y no se puede eliminar.',
            ], 409);
        }

        return response()->json(null, 204);
    }
}
