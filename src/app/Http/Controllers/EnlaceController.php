<?php

namespace App\Http\Controllers;

use App\Models\Archivo;
use Illuminate\Http\Request;

class EnlaceController extends Controller
{
    /**
     * Crea el enlace público de un archivo, con contraseña y fecha de caducidad opcionales.
     * Un archivo solo tiene un enlace a la vez: si ya había uno, se sustituye por otro con token nuevo.
     * Los errores van a una bolsa propia de cada archivo para poder reabrir su ventana.
     */
    public function store(Request $request, Archivo $archivo)
    {
        abort_if($archivo->usuario_id !== $request->user()->id, 404);

        $datos = $request->validateWithBag('enlace-'.$archivo->id, [
            'contrasena' => ['nullable', 'string', 'min:6', 'max:100'],
            'fecha_expiracion' => ['nullable', 'date', 'after:now'],
        ]);

        $atributos = ['fecha_expiracion' => $datos['fecha_expiracion'] ?? null];
        if (! empty($datos['contrasena'])) {
            $atributos['contrasena'] = $datos['contrasena'];
        }

        $archivo->enlace()->delete();
        $archivo->enlace()->create($atributos);

        return back()
            ->with('status', __('Enlace creado.'))
            ->with('compartir_abierto', $archivo->id);
    }

    /**
     * Deja de compartir un archivo borrando su enlace.
     */
    public function destroy(Request $request, Archivo $archivo)
    {
        abort_if($archivo->usuario_id !== $request->user()->id, 404);

        $archivo->enlace()->delete();

        return back()->with('status', __('Has dejado de compartir el archivo.'));
    }
}
