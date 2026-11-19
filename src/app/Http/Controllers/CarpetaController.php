<?php

namespace App\Http\Controllers;

use App\Models\Carpeta;
use App\Support\Papelera;
use Illuminate\Http\Request;

class CarpetaController extends Controller
{
    /**
     * Crea una carpeta, dentro de otra si se indica, comprobando que la carpeta padre es del usuario.
     */
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

        return back()->with('status', __('Carpeta creada.'));
    }

    /**
     * Cambia el nombre de una carpeta del usuario.
     *
     * Comprueba que la carpeta es del usuario que hace la petición (si no, responde 404 como si no existiera),
     * valida el nombre nuevo en una bolsa de errores propia de esa carpeta para que solo se reabra su ventana
     * si algo falla, y guarda el cambio.
     */
    public function update(Request $request, Carpeta $carpeta)
    {
        abort_if($carpeta->usuario_id !== $request->user()->id, 404);

        $datos = $request->validateWithBag('renombrar-'.$carpeta->id, [
            'nombre' => ['required', 'string', 'max:100'],
        ]);

        $carpeta->update($datos);

        return back()->with('status', __('Carpeta renombrada.'));
    }

    /**
     * Envía una carpeta a la papelera con todo lo que contiene.
     *
     * Solo se marca la carpeta: sus archivos y subcarpetas quedan ocultos porque ya no se puede llegar a ellos,
     * y vuelven todos juntos al restaurarla. Antes se quitan los enlaces públicos de la carpeta y de su contenido.
     */
    public function destroy(Request $request, Carpeta $carpeta)
    {
        abort_if($carpeta->usuario_id !== $request->user()->id, 404);

        Papelera::quitarEnlaces($carpeta);

        $carpeta->delete();

        return redirect()->route('dashboard', $carpeta->carpeta_padre_id)->with('status', __('Carpeta enviada a la papelera.'));
    }
}
