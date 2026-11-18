<?php

namespace App\Http\Controllers;

use App\Support\Papelera;
use Illuminate\Http\Request;

class PapeleraController extends Controller
{
    /**
     * Lista los archivos de la papelera del usuario, los más recientes primero.
     */
    public function index(Request $request)
    {
        $archivos = $request->user()->archivos()
            ->onlyTrashed()
            ->latest('papelera')
            ->get();

        return view('papelera', ['archivos' => $archivos]);
    }

    /**
     * Saca un archivo de la papelera.
     */
    public function restaurar(Request $request, int $id)
    {
        $archivo = $request->user()->archivos()->onlyTrashed()->findOrFail($id);

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
}