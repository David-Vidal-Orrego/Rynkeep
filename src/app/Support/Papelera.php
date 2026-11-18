<?php

namespace App\Support;

use App\Models\Archivo;
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
     * Fecha en la que se borrara solo un archivo de la papelera, o null si nunca se borra solo.
     */
    public static function fechaDeBorrado(Archivo $archivo)
    {
        $dias = (int) config('rynkeep.dias_papelera');

        return $dias > 0 ? $archivo->papelera->copy()->addDays($dias) : null;
    }
}
