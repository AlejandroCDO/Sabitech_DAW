<?php

namespace App\Api\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
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

    /**
     * Cierra la sesión revocando el token con el que se hizo la petición.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }

    /**
     * Devuelve el perfil del usuario autenticado con su rol.
     */
    public function usuario(Request $request): JsonResponse
    {
        return response()->json($request->user()->load('rol'));
    }
}
