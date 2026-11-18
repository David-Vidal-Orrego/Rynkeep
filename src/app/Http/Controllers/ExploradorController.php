<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Carpeta;

class ExploradorController extends Controller
{
    /**
     * Pantalla Mis archivos: carpetas y archivos de la carpeta actual (o de la raíz) y la ruta para las migas de pan.
     */
    public function index(Request $request, ?Carpeta $carpeta = null)
    {
        $usuario = $request->user();

        if ($carpeta && $carpeta->usuario_id !== $usuario->id) {
            abort(404);
        }

        $carpetas = $usuario->carpetas()
            ->where('carpeta_padre_id', $carpeta?->id)
            ->orderBy('nombre')
            ->get();

        $archivos = $usuario->archivos()
            ->where('carpeta_id', $carpeta?->id)
            ->with('enlace')
            ->latest()
            ->get();

        $ruta = [];
        $actual = $carpeta;
        while ($actual) {
            array_unshift($ruta, $actual);
            $actual = $actual->padre;
        }

        return view('dashboard', [
            'usuario' => $usuario,
            'carpetaActual' => $carpeta,
            'carpetas' => $carpetas,
            'archivos' => $archivos,
            'ruta' => $ruta,
        ]);
    }
}
