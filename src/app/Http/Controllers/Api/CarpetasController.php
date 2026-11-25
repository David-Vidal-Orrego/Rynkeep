<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Nombres;
use App\Support\Rutas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarpetasController extends Controller
{
    /**
     * Crea una carpeta en la ruta del campo path, por ejemplo /fotos/viaje: la carpeta fotos debe existir
     * y viaje es la que se crea. Responde 404 si falta la carpeta de arriba y 409 si ya hay algo con ese nombre.
     */
    public function crear(Request $request): JsonResponse
    {
        $usuario = $request->user();

        $request->validate(['path' => ['required', 'string', 'max:4096']]);

        $segmentos = Rutas::segmentos($request->input('path'));
        abort_if($segmentos === [], 400, __('Indica la ruta de la carpeta que quieres crear.'));

        $nombre = trim(array_pop($segmentos));
        Rutas::validarNombre($nombre, 100);

        $padre = Rutas::carpeta($usuario->id, $segmentos);

        abort_if(Nombres::ocupado($usuario->id, $padre?->id, $nombre), 409, __('Ya existe un archivo o carpeta con ese nombre aquí.'));

        $carpeta = $usuario->carpetas()->create(['nombre' => $nombre, 'carpeta_padre_id' => $padre?->id]);

        return response()->json(Rutas::describir($carpeta, Rutas::texto([...$segmentos, $nombre])), 201);
    }
}
