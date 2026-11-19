<?php

namespace App\Support;

use App\Models\Archivo;
use App\Models\Carpeta;
use App\Models\EnlaceCompartido;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Papelera
{
    /**
     * Borra para siempre un archivo que esta en la papelera: lo quita de la base de datos,
     * devuelve su espacio al usuario y borra el fichero del disco.
     */
    public static function eliminarDefinitivamente(Archivo $archivo): void
    {
        $ruta = $archivo->ruta_almacenamiento;
        $tamano = $archivo->tamano;
        $usuarioId = $archivo->usuario_id;

        DB::transaction(function () use ($archivo, $tamano, $usuarioId) {
            $usuario = Usuario::whereKey($usuarioId)->lockForUpdate()->first();

            $archivo->forceDelete();

            if ($usuario) {
                $usuario->espacio_usado = max(0, $usuario->espacio_usado - $tamano);
                $usuario->save();
            }
        });

        Storage::disk('archivos')->delete($ruta);
    }

    /**
     * Fecha en la que se borrara solo un archivo o una carpeta de la papelera, o null si nunca se borra solo.
     */
    public static function fechaDeBorrado(Archivo|Carpeta $elemento)
    {
        $dias = (int) config('rynkeep.dias_papelera');

        return $dias > 0 ? $elemento->papelera->copy()->addDays($dias) : null;
    }

    /**
     * Devuelve los ids de una carpeta y de todas las carpetas que hay dentro de ella, a cualquier profundidad,
     * incluidas las que estén en la papelera.
     */
    public static function idsDeCarpetas(Carpeta $carpeta): array
    {
        $ids = [$carpeta->id];
        $pendientes = [$carpeta->id];

        while ($pendientes) {
            $hijos = Carpeta::withTrashed()->whereIn('carpeta_padre_id', $pendientes)->pluck('id')->all();
            $ids = array_merge($ids, $hijos);
            $pendientes = $hijos;
        }

        return $ids;
    }

    /**
     * Quita los enlaces públicos de una carpeta, de sus subcarpetas y de todos los archivos que contienen.
     */
    public static function quitarEnlaces(Carpeta $carpeta): void
    {
        $ids = self::idsDeCarpetas($carpeta);

        EnlaceCompartido::where(function ($consulta) use ($ids) {
            $consulta->whereIn('carpeta_id', $ids)
                ->orWhereIn('archivo_id', Archivo::withTrashed()->whereIn('carpeta_id', $ids)->select('id'));
        })->delete();
    }

    /**
     * Cuenta los archivos que hay dentro de una carpeta (y sus subcarpetas) y lo que ocupan en total.
     * Devuelve un array con las claves archivos y tamano.
     */
    public static function resumenCarpeta(Carpeta $carpeta): array
    {
        $consulta = Archivo::whereIn('carpeta_id', self::idsDeCarpetas($carpeta));

        return [
            'archivos' => (clone $consulta)->count(),
            'tamano' => (int) (clone $consulta)->sum('tamano'),
        ];
    }

    /**
     * Borra para siempre una carpeta de la papelera junto con todo lo que contiene: cada archivo
     * se elimina del disco y devuelve su espacio al usuario, y después se borra la carpeta con sus subcarpetas.
     */
    public static function eliminarCarpetaDefinitivamente(Carpeta $carpeta): void
    {
        $ids = self::idsDeCarpetas($carpeta);

        Archivo::withTrashed()->whereIn('carpeta_id', $ids)->chunkById(100, function ($archivos) {
            foreach ($archivos as $archivo) {
                self::eliminarDefinitivamente($archivo);
            }
        });

        $carpeta->forceDelete();
    }
}
