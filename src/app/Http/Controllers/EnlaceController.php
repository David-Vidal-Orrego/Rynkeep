<?php

namespace App\Http\Controllers;

use App\Models\Archivo;
use App\Models\Carpeta;
use Illuminate\Http\Request;

class EnlaceController extends Controller
{
    /**
     * Crea el enlace público de un archivo, con contraseña y fecha de caducidad opcionales.
     */
    public function store(Request $request, Archivo $archivo)
    {
        abort_if($archivo->usuario_id !== $request->user()->id, 404);

        return $this->crear($request, $archivo, 'archivo-'.$archivo->id);
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

    /**
     * Crea el enlace público de una carpeta, con contraseña y fecha de caducidad opcionales.
     */
    public function storeCarpeta(Request $request, Carpeta $carpeta)
    {
        abort_if($carpeta->usuario_id !== $request->user()->id, 404);

        return $this->crear($request, $carpeta, 'carpeta-'.$carpeta->id);
    }

    /**
     * Deja de compartir una carpeta borrando su enlace.
     */
    public function destroyCarpeta(Request $request, Carpeta $carpeta)
    {
        abort_if($carpeta->usuario_id !== $request->user()->id, 404);

        $carpeta->enlace()->delete();

        return back()->with('status', __('Has dejado de compartir la carpeta.'));
    }

    /**
     * Valida los datos y crea el enlace de un archivo o de una carpeta.
     * Un elemento solo tiene un enlace a la vez: si ya había uno, se sustituye por otro con token nuevo.
     * Los errores van a una bolsa propia de cada elemento para poder reabrir su ventana.
     */
    private function crear(Request $request, Archivo|Carpeta $elemento, string $clave)
    {
        $datos = $request->validateWithBag('enlace-'.$clave, [
            'contrasena' => ['nullable', 'string', 'min:6', 'max:100'],
            'fecha_expiracion' => ['nullable', 'date', 'after:now'],
        ]);

        $atributos = ['fecha_expiracion' => $datos['fecha_expiracion'] ?? null];
        if (! empty($datos['contrasena'])) {
            $atributos['contrasena'] = $datos['contrasena'];
        }

        $elemento->enlace()->delete();
        $elemento->enlace()->create($atributos);

        return back()
            ->with('status', __('Enlace creado.'))
            ->with('compartir_abierto', $clave);
    }
}
