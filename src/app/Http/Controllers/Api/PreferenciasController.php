<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Apariencia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PreferenciasController extends Controller
{
    /**
     * Devuelve las preferencias de apariencia del usuario: tema, letra y tamaño.
     */
    public function mostrar(Request $request): JsonResponse
    {
        return response()->json(Apariencia::de($request->user()));
    }

    /**
     * Cambia las preferencias de apariencia. Se puede enviar solo lo que cambia, pero al menos una de las tres.
     * Un valor que no sea de los permitidos da error 422. Devuelve las preferencias completas ya guardadas.
     */
    public function guardar(Request $request): JsonResponse
    {
        $datos = $request->validate(Apariencia::reglas());

        abort_if($datos === [], 400, __('Indica al menos tema, letra o tamano.'));

        return response()->json(Apariencia::guardar($request->user(), $datos));
    }
}
