<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TokenApi;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    /**
     * Entra con correo y contraseña y devuelve un token de sesión que caduca pronto.
     *
     * Limita a 5 fallos cada 15 minutos por correo y dirección IP, como el resto de la aplicación.
     * Con credenciales incorrectas responde 401 sin decir si falló el correo o la contraseña.
     */
    public function login(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'dispositivo' => ['nullable', 'string', 'max:100'],
        ]);

        $clave = 'api-login|'.mb_strtolower($datos['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($clave, 5)) {
            $segundos = RateLimiter::availableIn($clave);

            return response()->json(
                ['error' => __('Demasiados intentos. Vuelve a probar en :min minuto(s).', ['min' => max(1, (int) ceil($segundos / 60))])],
                429,
                ['Retry-After' => $segundos]
            );
        }

        $usuario = Usuario::where('email', $datos['email'])->first();

        if ($usuario === null || ! Hash::check($datos['password'], $usuario->contrasena)) {
            RateLimiter::hit($clave, 900);

            return response()->json(['error' => __('Correo o contraseña incorrectos.')], 401);
        }

        RateLimiter::clear($clave);

        [$token, $texto] = TokenApi::generar(
            $usuario,
            $datos['dispositivo'] ?? __('Sesión de la API'),
            TokenApi::TIPO_SESION,
            now()->addHours((int) config('rynkeep.api_token_horas'))
        );

        return response()->json([
            'token' => $texto,
            'tipo' => 'Bearer',
            'caduca' => $token->caduca->toIso8601String(),
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'email' => $usuario->email,
            ],
        ]);
    }

    /**
     * Cierra la sesión de la API: borra el token con el que se ha hecho la petición.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->attributes->get('token_api')->delete();

        return response()->json(['mensaje' => __('Sesión cerrada.')]);
    }
}
