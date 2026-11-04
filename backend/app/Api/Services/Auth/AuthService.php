<?php

namespace App\Api\Services\Auth;

use App\Api\DTOs\Auth\LoginData;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * @return array{token: string, usuario: Usuario}
     */
    public function login(LoginData $datos): array
    {
        $usuario = Usuario::where('email', $datos->email)->first();

        if ($usuario === null || ! Hash::check($datos->password, $usuario->password)) {
            // Mensaje genérico para no revelar si el email existe en la base de datos.
            throw ValidationException::withMessages([
                'email' => 'Las credenciales proporcionadas no son correctas.',
            ]);
        }

        $token = $usuario->createToken('api');

        return [
            'token' => $token->plainTextToken,
            'usuario' => $usuario->load('rol'),
        ];
    }

    public function logout(Usuario $usuario): void
    {
        $usuario->currentAccessToken()?->delete();
    }
}
