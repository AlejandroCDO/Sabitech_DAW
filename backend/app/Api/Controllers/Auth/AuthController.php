<?php

namespace App\Api\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OAT;

final class AuthController extends Controller
{
    #[OAT\Post(
        path: '/api/auth/login',
        operationId: 'login',
        description: 'Autentica con email y contraseña y devuelve un token de Sanctum '
            .'junto al perfil del usuario con su rol. El token no caduca automáticamente: '
            .'se revoca solo con logout. El mensaje de error es genérico para no revelar '
            .'si el email existe.',
        tags: ['Autenticación'],
        security: [],
        requestBody: new OAT\RequestBody(
            description: 'Credenciales del usuario.',
            required: true,
            content: [
                new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(
                        required: ['email', 'password'],
                        properties: [
                            new OAT\Property(property: 'email', type: 'string', format: 'email', maxLength: 150, example: 'asistente1@sabitech.test'),
                            new OAT\Property(property: 'password', type: 'string', example: 'password'),
                        ],
                    ),
                ),
            ],
        ),
        responses: [
            new OAT\Response(response: '200', description: 'Sesión iniciada: token y usuario.', content: [new OAT\JsonContent(
                properties: [
                    new OAT\Property(property: 'token', description: 'Token de Sanctum en claro. Enviarlo como Authorization: Bearer <token>.', type: 'string', example: '73|YSfGk7...'),
                    new OAT\Property(property: 'usuario', ref: '#/components/schemas/Usuario'),
                ],
                type: 'object',
            )]),
            new OAT\Response(response: '422', ref: '#/components/responses/422'),
        ],
    )]
    /**
     * Inicia sesión y devuelve un token de API (Sanctum) junto al perfil.
     */
    public function login(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'email' => ['required', 'string', 'email', 'max:150'],
            'password' => ['required', 'string'],
        ]);

        $usuario = Usuario::where('email', $datos['email'])->first();

        if ($usuario === null || ! Hash::check($datos['password'], $usuario->password)) {
            // Mensaje genérico para no revelar si el email existe en la base de datos.
            throw ValidationException::withMessages([
                'email' => 'Las credenciales proporcionadas no son correctas.',
            ]);
        }

        $token = $usuario->createToken('api');

        return response()->json([
            'token' => $token->plainTextToken,
            'usuario' => $usuario->load('rol'),
        ]);
    }

    #[OAT\Get(
        path: '/api/auth/me',
        operationId: 'perfilUsuario',
        description: 'Perfil del usuario autenticado con su rol.',
        tags: ['Autenticación'],
        security: [['bearerAuth' => []]],
        responses: [
            new OAT\Response(response: '200', description: 'Usuario autenticado.', content: [
                new OAT\JsonContent(ref: '#/components/schemas/Usuario'),
            ]),
            new OAT\Response(response: '401', ref: '#/components/responses/401'),
        ],
    )]
    /**
     * Devuelve el perfil del usuario autenticado con su rol.
     */
    public function usuario(Request $request): JsonResponse
    {
        return response()->json($request->user()->load('rol'));
    }

    #[OAT\Post(
        path: '/api/auth/logout',
        operationId: 'logout',
        description: 'Revoca el token de Sanctum con el que se hizo la petición.',
        tags: ['Autenticación'],
        security: [['bearerAuth' => []]],
        responses: [
            new OAT\Response(response: '200', description: 'Sesión cerrada.', content: [new OAT\JsonContent(schema: 'Error', example: ['message' => 'Sesión cerrada correctamente.'])]),
            new OAT\Response(response: '401', ref: '#/components/responses/401'),
        ],
    )]
    /**
     * Cierra la sesión revocando el token con el que se hizo la petición.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }
}
