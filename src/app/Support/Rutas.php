<?php

namespace App\Support;

use App\Models\Archivo;
use App\Models\Carpeta;

class Rutas
{
    /**
     * Parte una ruta como /fotos/viaje/playa.jpg en sus trozos. Ignora las barras repetidas o del final.
     * Responde 400 si la ruta tiene caracteres de control o trozos como . y .. que servirían para escaparse de la carpeta.
     */
    public static function segmentos(string $ruta): array
    {
        if (preg_match('/[\x00-\x1F]/', $ruta)) {
            abort(400, __('La ruta contiene caracteres no válidos.'));
        }

        $segmentos = array_values(array_filter(explode('/', $ruta), fn ($trozo) => $trozo !== ''));

        foreach ($segmentos as $trozo) {
            if ($trozo === '.' || $trozo === '..') {
                abort(400, __('La ruta contiene caracteres no válidos.'));
            }
        }

        return $segmentos;
    }

    /**
     * Vuelve a juntar los trozos en una ruta que empieza por barra. Sin trozos es la raíz, "/".
     */
    public static function texto(array $segmentos): string
    {
        return '/'.implode('/', $segmentos);
    }

    /**
     * Comprueba que un nombre sirve para un archivo o una carpeta: no vacío, sin barras ni caracteres de control,
     * que no sea . ni .. y que no pase del tamaño máximo. Si no sirve, responde 400.
     */
    public static function validarNombre(string $nombre, int $maximo): void
    {
        if ($nombre === '' || $nombre === '.' || $nombre === '..' || mb_strlen($nombre) > $maximo || preg_match('/[\/\x00-\x1F]/', $nombre)) {
            abort(400, __('El nombre no es válido.'));
        }
    }

    /**
     * Busca la carpeta a la que lleva una ruta, bajando carpeta a carpeta desde la raíz.
     * Sin trozos devuelve null, que significa la raíz. Si algún trozo no existe responde 404.
     */
    public static function carpeta(int $usuarioId, array $segmentos): ?Carpeta
    {
        $actual = null;

        foreach ($segmentos as $nombre) {
            $actual = Nombres::carpeta($usuarioId, $actual?->id, $nombre);

            abort_if($actual === null, 404, __('No encontrado.'));
        }

        return $actual;
    }

    /**
     * Busca el archivo o la carpeta al que lleva una ruta. Sin trozos devuelve null, que significa la raíz.
     * Si no existe responde 404.
     */
    public static function elemento(int $usuarioId, array $segmentos): Archivo|Carpeta|null
    {
        if ($segmentos === []) {
            return null;
        }

        $nombre = array_pop($segmentos);
        $padre = self::carpeta($usuarioId, $segmentos);

        $carpeta = Nombres::carpeta($usuarioId, $padre?->id, $nombre);

        if ($carpeta !== null) {
            return $carpeta;
        }

        $archivo = Nombres::archivo($usuarioId, $padre?->id, $nombre);

        abort_if($archivo === null, 404, __('No encontrado.'));

        return $archivo;
    }

    /**
     * Calcula la ruta completa de un archivo o carpeta subiendo de padre en padre hasta la raíz.
     */
    public static function deElemento(Archivo|Carpeta $elemento): string
    {
        $esArchivo = $elemento instanceof Archivo;
        $partes = [$esArchivo ? $elemento->nombre_original : $elemento->nombre];
        $padreId = $esArchivo ? $elemento->carpeta_id : $elemento->carpeta_padre_id;

        for ($i = 0; $i < 200 && $padreId !== null; $i++) {
            $padre = Carpeta::find($padreId);

            if ($padre === null) {
                break;
            }

            array_unshift($partes, $padre->nombre);
            $padreId = $padre->carpeta_padre_id;
        }

        return self::texto($partes);
    }

    /**
     * Prepara los datos de un archivo o carpeta tal y como los devuelve la API: id, nombre, tipo (archivo o carpeta),
     * ruta, tamaño en bytes, tipo de contenido y huella SHA-256 (solo archivos), fecha de modificación en el servidor y fecha
     * original del archivo si el cliente la indicó (solo archivos). Las fechas van en UTC (2026-11-25T10:00:00Z).
     * Los id de archivos y de carpetas pueden coincidir, así que el tipo es lo que los distingue.
     */
    public static function describir(Archivo|Carpeta $elemento, string $ruta): array
    {
        $esArchivo = $elemento instanceof Archivo;

        return [
            'id' => $elemento->id,
            'nombre' => $esArchivo ? $elemento->nombre_original : $elemento->nombre,
            'tipo' => $esArchivo ? 'archivo' : 'carpeta',
            'ruta' => $ruta,
            'tamano' => $esArchivo ? $elemento->tamano : null,
            'tipo_mime' => $esArchivo ? $elemento->tipo_archivo : null,
            'hash' => $esArchivo ? $elemento->hash : null,
            'modificado' => $elemento->updated_at?->copy()->utc()->format('Y-m-d\TH:i:s\Z'),
            'modificado_original' => $esArchivo ? $elemento->modificado_original?->copy()->utc()->format('Y-m-d\TH:i:s\Z') : null,
        ];
    }
}
