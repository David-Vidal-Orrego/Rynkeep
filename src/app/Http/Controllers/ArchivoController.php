<?php

namespace App\Http\Controllers;

use App\Models\Archivo;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ArchivoController extends Controller
{
    /**
     * Sube un archivo. Comprueba el tamaño máximo y el espacio libre del usuario dentro de una transacción con el usuario bloqueado,
     * para que dos subidas a la vez no se salten el límite. Si algo falla, borra el fichero que ya se hubiera guardado.
     */
    public function store(Request $request)
    {
        $maximoMb = config('rynkeep.tamano_maximo_mb');

        $request->validate([
            'archivo' => ['required', 'file', 'max:'.($maximoMb * 1024)],
            'carpeta_id' => ['nullable', 'integer'],
        ], [
            'archivo.max' => __('El archivo supera el límite de :mb MB.', ['mb' => $maximoMb]),
        ]);

        $fichero = $request->file('archivo');
        $tamano = $fichero->getSize();
        $usuarioId = $request->user()->id;
        $ruta = null;
        $carpetaId = $request->input('carpeta_id');

        if ($carpetaId) {
            abort_if($request->user()->carpetas()->find($carpetaId) === null, 404);
        }

        try {
            DB::transaction(function () use ($fichero, $tamano, $usuarioId, $carpetaId, &$ruta) {
                $usuario = Usuario::whereKey($usuarioId)->lockForUpdate()->first();

                if ($usuario->espacio_usado + $tamano > $usuario->espacio_limite) {
                    throw ValidationException::withMessages([
                        'archivo' => __('No tienes espacio suficiente para subir este archivo.'),
                    ]);
                }

                $ruta = Storage::disk('archivos')
                    ->putFileAs('u' . $usuario->id, $fichero, (string) Str::uuid());

                $archivo = new Archivo([
                    'nombre_original' => mb_substr($fichero->getClientOriginalName(), 0, 255),
                    'carpeta_id' => $carpetaId ?: null,
                ]);
                $archivo->usuario_id = $usuario->id;
                $archivo->ruta_almacenamiento = $ruta;
                $archivo->tamano = $tamano;
                $archivo->tipo_archivo = $fichero->getMimeType() ?? 'application/octet-stream';
                $archivo->save();

                $usuario->increment('espacio_usado', $tamano);
            });
        } catch (Throwable $e) {
            if ($ruta) {
                Storage::disk('archivos')->delete($ruta);
            }
            throw $e;
        }

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
