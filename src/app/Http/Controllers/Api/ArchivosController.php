<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Archivo;
use App\Models\Carpeta;
use App\Support\Almacenamiento;
use App\Support\Nombres;
use App\Support\Papelera;
use App\Support\Rutas;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArchivosController extends Controller
{
    /**
     * Lista el contenido de una carpeta del usuario (por defecto la raíz, "/"): primero las carpetas y luego los archivos,
     * cada uno con id, nombre, tipo, ruta, tamaño y fecha de modificación. Si la ruta es un archivo, responde 400.
     */
    public function index(Request $request): JsonResponse
    {
        $usuario = $request->user();
        $segmentos = Rutas::segmentos((string) ($request->query('path') ?? '/'));
        $carpeta = Rutas::elemento($usuario->id, $segmentos);

        abort_if($carpeta instanceof Archivo, 400, __('La ruta es un archivo, no una carpeta.'));

        $ruta = Rutas::texto($segmentos);
        $unir = fn (string $nombre) => rtrim($ruta, '/').'/'.$nombre;

        $carpetas = $usuario->carpetas()
            ->where('carpeta_padre_id', $carpeta?->id)
            ->orderBy('nombre')
            ->get()
            ->map(fn (Carpeta $hija) => Rutas::describir($hija, $unir($hija->nombre)));

        $archivos = $usuario->archivos()
            ->where('carpeta_id', $carpeta?->id)
            ->orderBy('nombre_original')
            ->get()
            ->map(fn (Archivo $archivo) => Rutas::describir($archivo, $unir($archivo->nombre_original)));

        return response()->json([
            'ruta' => $ruta,
            'elementos' => $carpetas->concat($archivos)->values(),
        ]);
    }

    /**
     * Descarga el archivo al que lleva la ruta del parámetro path. Si la ruta es una carpeta o la raíz, responde 400.
     */
    public function descargar(Request $request): StreamedResponse
    {
        $segmentos = Rutas::segmentos((string) $request->query('path'));
        abort_if($segmentos === [], 400, __('Indica la ruta de un archivo en el parámetro path.'));

        $archivo = Rutas::elemento($request->user()->id, $segmentos);
        abort_unless($archivo instanceof Archivo, 400, __('La ruta es una carpeta, no un archivo.'));

        return Storage::disk('archivos')->download(
            $archivo->ruta_almacenamiento,
            $archivo->nombre_original,
            ['Content-Type' => $archivo->tipo_archivo]
        );
    }

    /**
     * Sube un archivo (multipart, campo file) a la carpeta del campo path, que por defecto es la raíz y debe existir.
     * El campo name permite guardarlo con otro nombre. Si ya existe algo con ese nombre, responde 409, salvo que el campo
     * conflict valga overwrite (reemplaza el contenido) o rename (lo guarda como nombre (2).ext).
     * Con overwrite, el campo hash_esperado (la huella SHA-256 que el cliente cree que tiene el archivo del servidor) evita
     * pisar cambios ajenos: si no coincide, o el archivo ya no existe, responde 409. El campo modificado (UTC con Z) guarda
     * la fecha original del archivo. Responde 201 si crea un archivo nuevo y 200 si sobrescribe uno existente.
     */
    public function subir(Request $request): JsonResponse
    {
        $usuario = $request->user();
        $maximoMb = config('rynkeep.tamano_maximo_mb');

        $request->validate([
            'file' => ['required', 'file', 'max:'.($maximoMb * 1024)],
            'path' => ['nullable', 'string', 'max:4096'],
            'name' => ['nullable', 'string', 'max:255'],
            'conflict' => ['nullable', 'in:overwrite,rename'],
            'hash_esperado' => ['nullable', 'string', 'regex:/^[A-Fa-f0-9]{64}$/'],
            'modificado' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d+)?Z$/', 'date'],
        ], [
            'file.max' => __('El archivo supera el límite de :mb MB.', ['mb' => $maximoMb]),
        ]);

        $segmentos = Rutas::segmentos((string) ($request->input('path') ?? '/'));
        $carpeta = Rutas::carpeta($usuario->id, $segmentos);
        $fichero = $request->file('file');

        $nombre = $request->input('name') ?? $fichero->getClientOriginalName();
        Rutas::validarNombre($nombre, 255);

        $conflicto = match ($request->input('conflict')) {
            'overwrite' => 'sobrescribir',
            'rename' => 'renombrar',
            default => null,
        };

        $hayCarpeta = Nombres::carpeta($usuario->id, $carpeta?->id, $nombre) !== null;
        $hayArchivo = Nombres::archivo($usuario->id, $carpeta?->id, $nombre) !== null;

        abort_if(($hayCarpeta || $hayArchivo) && $conflicto === null, 409, __('Ya existe un archivo o carpeta con ese nombre aquí.'));
        abort_if($hayCarpeta && $conflicto === 'sobrescribir', 409, __('No se puede sobrescribir una carpeta.'));

        $modificado = $request->filled('modificado')
            ? Carbon::parse($request->input('modificado'))->setTimezone(config('app.timezone'))
            : null;

        $archivo = Almacenamiento::subir($usuario, $fichero, $carpeta?->id, $conflicto, $nombre, $request->input('hash_esperado'), $modificado);

        return response()->json(
            Rutas::describir($archivo, Rutas::deElemento($archivo)),
            $archivo->wasRecentlyCreated ? 201 : 200
        );
    }

    /**
     * Renombra y/o mueve un archivo o una carpeta. Recibe path (lo que se cambia) y al menos uno de estos dos campos:
     * name (el nombre nuevo) y to (la carpeta de destino, que debe existir). Responde 409 si en el destino ya hay algo
     * con ese nombre y 400 si se intenta mover una carpeta dentro de sí misma o tocar la raíz.
     */
    public function actualizar(Request $request): JsonResponse
    {
        $usuario = $request->user();

        $request->validate([
            'path' => ['required', 'string', 'max:4096'],
            'name' => ['nullable', 'string', 'max:255'],
            'to' => ['nullable', 'string', 'max:4096'],
        ]);

        abort_if(! $request->filled('name') && ! $request->filled('to'), 400, __('Indica name, to o los dos.'));

        $segmentos = Rutas::segmentos($request->input('path'));
        abort_if($segmentos === [], 400, __('No se puede modificar la carpeta raíz.'));

        $elemento = Rutas::elemento($usuario->id, $segmentos);
        $esArchivo = $elemento instanceof Archivo;
        $nombreActual = $esArchivo ? $elemento->nombre_original : $elemento->nombre;
        $padreActual = $esArchivo ? $elemento->carpeta_id : $elemento->carpeta_padre_id;

        $nombre = $request->filled('name') ? $request->input('name') : $nombreActual;
        Rutas::validarNombre($nombre, $esArchivo ? 255 : 100);

        $padreNuevo = $padreActual;

        if ($request->filled('to')) {
            $destino = Rutas::carpeta($usuario->id, Rutas::segmentos($request->input('to')));

            abort_if(! $esArchivo && $destino !== null && $destino->estaDentroDe($elemento), 400, __('No se puede mover una carpeta dentro de sí misma.'));

            $padreNuevo = $destino?->id;
        }

        if ($nombre !== $nombreActual || $padreNuevo !== $padreActual) {
            $ocupado = Nombres::ocupado(
                $usuario->id,
                $padreNuevo,
                $nombre,
                $esArchivo ? null : $elemento->id,
                $esArchivo ? $elemento->id : null
            );

            abort_if($ocupado, 409, __('Ya existe un archivo o carpeta con ese nombre aquí.'));

            if ($esArchivo) {
                $elemento->nombre_original = $nombre;
                $elemento->carpeta_id = $padreNuevo;
            } else {
                $elemento->nombre = $nombre;
                $elemento->carpeta_padre_id = $padreNuevo;
            }

            $elemento->save();
        }

        return response()->json(Rutas::describir($elemento, Rutas::deElemento($elemento)));
    }

    /**
     * Envía a la papelera el archivo o la carpeta (con todo lo que contiene) al que lleva la ruta del parámetro path,
     * igual que hace la web, y le quita los enlaces públicos. Desde la papelera se puede restaurar. La raíz no se puede borrar.
     */
    public function borrar(Request $request): JsonResponse
    {
        $segmentos = Rutas::segmentos((string) $request->query('path'));
        abort_if($segmentos === [], 400, __('Indica la ruta de lo que quieres borrar en el parámetro path.'));

        $elemento = Rutas::elemento($request->user()->id, $segmentos);

        if ($elemento instanceof Archivo) {
            $elemento->enlace()->delete();
        } else {
            Papelera::quitarEnlaces($elemento);
        }

        $elemento->delete();

        return response()->json(['mensaje' => __('Enviado a la papelera.')]);
    }
}
