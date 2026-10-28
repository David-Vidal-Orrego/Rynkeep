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
    public function store(Request $request)
    {
        $request->validate([
            'archivo' => ['required', 'file', 'max:102400'], // en KB: 100 MB
        ]);

        $fichero = $request->file('archivo');
        $tamano = $fichero->getSize();
        $usuarioId = $request->user()->id;
        $ruta = null;

        try {
            DB::transaction(function () use ($fichero, $tamano, $usuarioId, &$ruta) {
                $usuario = Usuario::whereKey($usuarioId)->lockForUpdate()->first();

                if ($usuario->espacio_usado + $tamano > $usuario->espacio_limite) {
                    throw ValidationException::withMessages([
                        'archivo' => 'No tienes espacio suficiente para subir este archivo.',
                    ]);
                }

                $ruta = Storage::disk('archivos')
                    ->putFileAs('u' . $usuario->id, $fichero, (string) Str::uuid());

                $archivo = new Archivo([
                    'nombre_original' => mb_substr($fichero->getClientOriginalName(), 0, 255),
                    'carpeta_id' => null,
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

        return back()->with('status', 'Archivo subido.');
    }

    public function descargar(Request $request, Archivo $archivo)
    {
        abort_if($archivo->usuario_id !== $request->user()->id, 404);

        return Storage::disk('archivos')->download(
            $archivo->ruta_almacenamiento,
            $archivo->nombre_original,
            ['Content-Type' => $archivo->tipo_archivo]
        );
    }

    public function destroy(Request $request, Archivo $archivo)
    {
        abort_if($archivo->usuario_id !== $request->user()->id, 404);
        
        $archivo->enlace()->delete();

        $archivo->delete();

        return back()->with('status', 'Archivo enviado a la papelera.');
    }
}
