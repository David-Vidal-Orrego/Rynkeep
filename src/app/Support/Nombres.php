<?php

namespace App\Support;

use App\Models\Archivo;
use App\Models\Carpeta;

class Nombres
{
    /**
     * Busca el archivo con ese nombre dentro de una carpeta (o de la raíz) del usuario, sin contar la papelera.
     * Se puede indicar un archivo que debe ignorarse, útil al renombrarlo o moverlo.
     */
    public static function archivo(int $usuarioId, ?int $carpetaId, string $nombre, ?int $ignorarId = null): ?Archivo
    {
        return Archivo::where('usuario_id', $usuarioId)
            ->where('carpeta_id', $carpetaId)
            ->where('nombre_original', $nombre)
            ->when($ignorarId, fn ($consulta) => $consulta->where('id', '!=', $ignorarId))
            ->first();
    }

    /**
     * Busca la subcarpeta con ese nombre dentro de otra carpeta (o de la raíz) del usuario, sin contar la papelera.
     * Se puede indicar una carpeta que debe ignorarse, útil al renombrarla.
     */
    public static function carpeta(int $usuarioId, ?int $carpetaId, string $nombre, ?int $ignorarId = null): ?Carpeta
    {
        return Carpeta::where('usuario_id', $usuarioId)
            ->where('carpeta_padre_id', $carpetaId)
            ->where('nombre', $nombre)
            ->when($ignorarId, fn ($consulta) => $consulta->where('id', '!=', $ignorarId))
            ->first();
    }

    /**
     * Indica si ya hay algo con ese nombre en esa carpeta. Archivos y carpetas comparten nombres,
     * porque en una ruta como /fotos/viaje no puede haber a la vez un archivo y una carpeta llamados viaje.
     * Se pueden indicar una carpeta y un archivo que deben ignorarse, útil al renombrar o mover algo.
     */
    public static function ocupado(int $usuarioId, ?int $carpetaId, string $nombre, ?int $ignorarCarpetaId = null, ?int $ignorarArchivoId = null): bool
    {
        return self::archivo($usuarioId, $carpetaId, $nombre, $ignorarArchivoId) !== null
            || self::carpeta($usuarioId, $carpetaId, $nombre, $ignorarCarpetaId) !== null;
    }

    /**
     * Devuelve un nombre que no esté ocupado en esa carpeta. Si el pedido está libre lo devuelve igual;
     * si no, le añade un número antes de la extensión: informe.pdf pasa a informe (2).pdf.
     */
    public static function libre(int $usuarioId, ?int $carpetaId, string $nombre, bool $esCarpeta = false): string
    {
        if (! self::ocupado($usuarioId, $carpetaId, $nombre)) {
            return $nombre;
        }

        $extension = $esCarpeta ? '' : pathinfo($nombre, PATHINFO_EXTENSION);
        $base = $esCarpeta ? $nombre : pathinfo($nombre, PATHINFO_FILENAME);

        if ($base === '') {
            $base = $nombre;
            $extension = '';
        }

        $sufijoExtension = $extension !== '' ? '.'.$extension : '';
        $limite = $esCarpeta ? 100 : 255;

        for ($numero = 2; $numero < 1000; $numero++) {
            $sufijo = ' ('.$numero.')'.$sufijoExtension;
            $candidato = mb_substr($base, 0, $limite - mb_strlen($sufijo)).$sufijo;

            if (! self::ocupado($usuarioId, $carpetaId, $candidato)) {
                return $candidato;
            }
        }

        $sufijo = ' ('.uniqid().')'.$sufijoExtension;

        return mb_substr($base, 0, $limite - mb_strlen($sufijo)).$sufijo;
    }

    /**
     * Nombres en minúsculas de los archivos y de las carpetas que hay en una carpeta (o en la raíz),
     * para que la página avise antes de subir un archivo repetido.
     */
    public static function deCarpeta(int $usuarioId, ?int $carpetaId): array
    {
        $minusculas = fn ($nombres) => $nombres->map(fn ($nombre) => mb_strtolower($nombre))->values()->all();

        return [
            'archivos' => $minusculas(Archivo::where('usuario_id', $usuarioId)->where('carpeta_id', $carpetaId)->pluck('nombre_original')),
            'carpetas' => $minusculas(Carpeta::where('usuario_id', $usuarioId)->where('carpeta_padre_id', $carpetaId)->pluck('nombre')),
        ];
    }
}
