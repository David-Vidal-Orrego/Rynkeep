<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    /**
     * Devuelve los datos del usuario del token: nombre, correo y el espacio usado, total y libre en bytes.
     */
    public function me(Request $request): JsonResponse
    {
        $usuario = $request->user();

        return response()->json([
            'id' => $usuario->id,
            'nombre' => $usuario->nombre,
            'email' => $usuario->email,
            'es_admin' => $usuario->es_admin,
            'espacio_usado' => $usuario->espacio_usado,
            'espacio_total' => $usuario->espacio_limite,
            'espacio_libre' => max(0, $usuario->espacio_limite - $usuario->espacio_usado),
            'porcentaje_usado' => $usuario->porcentajeUsado(),
        ]);
    }
}
