<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Carpeta;

class ExploradorController extends Controller
{
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
