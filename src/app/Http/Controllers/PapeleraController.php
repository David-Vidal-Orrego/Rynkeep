<?php

namespace App\Http\Controllers;

use App\Models\Carpeta;
use App\Support\Papelera;
use Illuminate\Http\Request;

class PapeleraController extends Controller
{
    /**
     * Lista las carpetas y los archivos de la papelera del usuario, los más recientes primero.
     */
    public function index(Request $request)
    {
        $archivos = $request->user()->archivos()
            ->onlyTrashed()
            ->latest('papelera')
            ->get();

        $carpetas = $request->user()->carpetas()
            ->onlyTrashed()
            ->latest('papelera')
            ->get();

        return view('papelera', ['archivos' => $archivos, 'carpetas' => $carpetas]);
    }

    /**
     * Saca un archivo de la papelera. Si la carpeta donde estaba también está en la papelera,
     * el archivo vuelve a la raíz para que no quede escondido.
     */
    public function restaurar(Request $request, int $id)
    {
        $archivo = $request->user()->archivos()->onlyTrashed()->findOrFail($id);

        if ($archivo->carpeta_id && Carpeta::withTrashed()->find($archivo->carpeta_id)?->trashed()) {
            $archivo->carpeta_id = null;
        }

        $archivo->restore();

        return back()->with('status', __('Archivo restaurado.'));
    }

    /**
     * Borra un archivo de la papelera para siempre.
     */
    public function eliminar(Request $request, int $id)
    {
        $archivo = $request->user()->archivos()->onlyTrashed()->findOrFail($id);

        Papelera::eliminarDefinitivamente($archivo);

        return back()->with('status', __('Archivo eliminado definitivamente.'));
    }

    /**
     * Saca una carpeta de la papelera junto con todo lo que contiene. Si la carpeta que la contenía
     * también está en la papelera, vuelve a la raíz para que no quede escondida.
     */
    public function restaurarCarpeta(Request $request, int $id)
    {
        $carpeta = $request->user()->carpetas()->onlyTrashed()->findOrFail($id);

        if ($carpeta->carpeta_padre_id && Carpeta::withTrashed()->find($carpeta->carpeta_padre_id)?->trashed()) {
            $carpeta->carpeta_padre_id = null;
        }

        $carpeta->restore();

        return back()->with('status', __('Carpeta restaurada.'));
    }

    /**
     * Borra para siempre una carpeta de la papelera y todo lo que contiene.
     */
    public function eliminarCarpeta(Request $request, int $id)
    {
        $carpeta = $request->user()->carpetas()->onlyTrashed()->findOrFail($id);

        Papelera::eliminarCarpetaDefinitivamente($carpeta);

        return back()->with('status', __('Carpeta eliminada definitivamente.'));
    }
}
