<?php

namespace App\Support;

use App\Models\Archivo;
use App\Models\Usuario;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class Almacenamiento
{
    /**
     * Guarda un archivo subido en una carpeta del usuario (o en la raíz) y lo apunta en la base de datos.
     * Lo usan la web y la API, así que las dos comprueban lo mismo.
     *
     * Comprueba el espacio libre del usuario dentro de una transacción con el usuario bloqueado, para que dos subidas
     * a la vez no se salten el límite. Si algo falla, borra el fichero que ya se hubiera guardado.
     *
     * Si en esa carpeta ya hay algo con el mismo nombre, hace falta indicar qué hacer: "sobrescribir" (el archivo existente
     * pasa a tener el contenido nuevo y conserva su identificador y su enlace público) o "renombrar" (el nuevo se guarda como
     * informe (2).pdf). Sin indicación, rechaza la subida. Una carpeta nunca se sobrescribe.
     * Si no se da un nombre, se usa el que trae el archivo.
     *
     * Guarda la huella SHA-256 del contenido. Si se indica la huella que el cliente cree que tiene el archivo del servidor
     * ($hashEsperado) y se sobrescribe, comprueba que coincida; si no, responde 409 y no lo pisa, para no perder cambios
     * hechos desde otro sitio. También puede guardar la fecha original del archivo ($modificadoOriginal), que solo informa.
     */
    public static function subir(Usuario $propietario, UploadedFile $fichero, ?int $carpetaId, ?string $conflicto, ?string $nombre = null, ?string $hashEsperado = null, ?Carbon $modificadoOriginal = null): Archivo
    {
        $tamano = $fichero->getSize();
        $hash = hash_file('sha256', $fichero->getRealPath());
        $nombre = mb_substr($nombre ?? $fichero->getClientOriginalName(), 0, 255);
        $ruta = null;
        $rutaAnterior = null;

        try {
            $archivo = DB::transaction(function () use ($propietario, $fichero, $tamano, $hash, $hashEsperado, $modificadoOriginal, $carpetaId, $conflicto, $nombre, &$ruta, &$rutaAnterior) {
                $usuario = Usuario::whereKey($propietario->id)->lockForUpdate()->first();

                $existente = Nombres::archivo($usuario->id, $carpetaId, $nombre);
                $hayCarpeta = Nombres::carpeta($usuario->id, $carpetaId, $nombre) !== null;

                if (($existente || $hayCarpeta) && $conflicto === null) {
                    throw ValidationException::withMessages([
                        'archivo' => __('Ya existe un archivo o carpeta con ese nombre aquí.'),
                    ]);
                }

                if ($hayCarpeta && $conflicto === 'sobrescribir') {
                    throw ValidationException::withMessages([
                        'archivo' => __('No se puede sobrescribir una carpeta.'),
                    ]);
                }

                if ($hashEsperado !== null && $conflicto === 'sobrescribir') {
                    abort_if($existente === null, 409, __('El archivo ya no existe en el servidor.'));

                    abort_unless(
                        hash_equals((string) $existente->asegurarHash(), strtolower($hashEsperado)),
                        409,
                        __('El archivo del servidor ha cambiado desde la última vez que lo viste.')
                    );
                }

                $sobrescribe = $conflicto === 'sobrescribir' && $existente !== null;

                if ($conflicto === 'renombrar' && ($existente || $hayCarpeta)) {
                    $nombre = Nombres::libre($usuario->id, $carpetaId, $nombre);
                }

                $liberado = $sobrescribe ? $existente->tamano : 0;

                if ($usuario->espacio_usado - $liberado + $tamano > $usuario->espacio_limite) {
                    throw ValidationException::withMessages([
                        'archivo' => __('No tienes espacio suficiente para subir este archivo.'),
                    ]);
                }

                $ruta = Storage::disk('archivos')
                    ->putFileAs('u' . $usuario->id, $fichero, (string) Str::uuid());

                if ($sobrescribe) {
                    $rutaAnterior = $existente->ruta_almacenamiento;
                    $archivo = $existente;
                } else {
                    $archivo = new Archivo([
                        'nombre_original' => $nombre,
                        'carpeta_id' => $carpetaId,
                    ]);
                    $archivo->usuario_id = $usuario->id;
                }

                $archivo->ruta_almacenamiento = $ruta;
                $archivo->tamano = $tamano;
                $archivo->tipo_archivo = $fichero->getMimeType() ?? 'application/octet-stream';
                $archivo->hash = $hash;
                $archivo->modificado_original = $modificadoOriginal;
                $archivo->save();

                $usuario->espacio_usado = $usuario->espacio_usado - $liberado + $tamano;
                $usuario->save();

                return $archivo;
            });
        } catch (Throwable $e) {
            if ($ruta) {
                Storage::disk('archivos')->delete($ruta);
            }
            throw $e;
        }

        if ($rutaAnterior) {
            Storage::disk('archivos')->delete($rutaAnterior);
        }

        return $archivo;
    }
}
