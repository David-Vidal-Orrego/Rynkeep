<?php

namespace App\Http\Controllers;

use App\Models\Archivo;
use App\Support\Almacenamiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArchivoController extends Controller
{
    /**
     * Sube un archivo desde la web. Valida el tamaño y la carpeta de destino y deja el guardado en Almacenamiento::subir,
     * que comprueba el espacio libre y qué hacer si ya existe un archivo con el mismo nombre (campo conflicto).
     */
    public function store(Request $request)
    {
        $maximoMb = config('rynkeep.tamano_maximo_mb');

        $request->validate([
            'archivo' => ['required', 'file', 'max:'.($maximoMb * 1024)],
            'carpeta_id' => ['nullable', 'integer'],
            'conflicto' => ['nullable', 'in:sobrescribir,renombrar'],
        ], [
            'archivo.max' => __('El archivo supera el límite de :mb MB.', ['mb' => $maximoMb]),
        ]);

        $carpetaId = $request->input('carpeta_id') ?: null;

        if ($carpetaId) {
            abort_if($request->user()->carpetas()->find($carpetaId) === null, 404);
        }

        Almacenamiento::subir($request->user(), $request->file('archivo'), $carpetaId, $request->input('conflicto'));

        return back()->with('status', __('Archivo subido.'));
    }

    /**
     * Descarga un archivo del usuario. Si es de otro usuario, responde como si no existiera.
     */
    public function descargar(Request $request, Archivo $archivo)
    {
        abort_if($archivo->usuario_id !== $request->user()->id, 404);

        return Storage::disk('archivos')->download(
            $archivo->ruta_almacenamiento,
            $archivo->nombre_original,
            ['Content-Type' => $archivo->tipo_archivo]
        );
    }

    /**
     * Envía un archivo a la papelera y le quita el enlace público, si lo tenía.
     */
    public function destroy(Request $request, Archivo $archivo)
    {
        abort_if($archivo->usuario_id !== $request->user()->id, 404);

        $archivo->enlace()->delete();

        $archivo->delete();

        return back()->with('status', __('Archivo enviado a la papelera.'));
    }
}
