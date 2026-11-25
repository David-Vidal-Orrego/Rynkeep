<?php

namespace App\Http\Middleware;

use App\Models\TokenApi;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AutenticarApi
{
    /**
     * Identifica al usuario de la API por el token de la cabecera Authorization: Bearer.
     *
     * Si falta, no existe o ha caducado, responde 401 en JSON. Si es válido, apunta su uso, deja al usuario
     * como el usuario de la petición y guarda el token en la petición para poder cerrarlo al salir.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $texto = $request->bearerToken();
        $token = $texto ? TokenApi::buscar($texto) : null;

        if ($token === null || $token->haCaducado()) {
            return response()->json(
                ['error' => __('No autenticado. Envía un token válido en la cabecera Authorization.')],
                401,
                ['WWW-Authenticate' => 'Bearer']
            );
        }

        $token->marcarUso();

        $usuario = $token->usuario;
        Auth::setUser($usuario);
        $request->setUserResolver(fn () => $usuario);
        $request->attributes->set('token_api', $token);

        return $next($request);
    }
}
