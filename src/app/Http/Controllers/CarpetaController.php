<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CarpetaController extends Controller
{
    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'carpeta_padre_id' => ['nullable', 'integer'],
        ]);

        if (! empty($datos['carpeta_padre_id'])) {
            $padre = $request->user()->carpetas()->find($datos['carpeta_padre_id']);
            abort_if($padre === null, 404);
        }

        $request->user()->carpetas()->create($datos);

        return back()->with('status', 'Carpeta creada.');
    }
}