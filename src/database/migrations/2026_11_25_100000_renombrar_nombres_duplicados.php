<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Renombra los archivos y carpetas que comparten nombre dentro de la misma carpeta, añadiendo un número
     * (informe (2).pdf), para que a partir de ahora cada elemento se pueda identificar por su ruta.
     * Las carpetas van primero y conservan su nombre; después los archivos, del más antiguo al más nuevo.
     */
    public function up(): void
    {
        $usados = [];

        $reservar = function (int $usuario, ?int $carpeta, string $nombre, bool $esCarpeta) use (&$usados): string {
            $limite = $esCarpeta ? 100 : 255;
            $extension = $esCarpeta ? '' : pathinfo($nombre, PATHINFO_EXTENSION);
            $base = $esCarpeta ? $nombre : pathinfo($nombre, PATHINFO_FILENAME);

            if ($base === '') {
                $base = $nombre;
                $extension = '';
            }

            $sufijoExtension = $extension !== '' ? '.'.$extension : '';
            $candidato = $nombre;
            $numero = 2;

            while (isset($usados[$usuario.'|'.($carpeta ?? 0).'|'.mb_strtolower($candidato)])) {
                $sufijo = ' ('.$numero.')'.$sufijoExtension;
                $candidato = mb_substr($base, 0, $limite - mb_strlen($sufijo)).$sufijo;
                $numero++;
            }

            $usados[$usuario.'|'.($carpeta ?? 0).'|'.mb_strtolower($candidato)] = true;

            return $candidato;
        };

        foreach (DB::table('carpetas')->whereNull('papelera')->orderBy('id')->get() as $carpeta) {
            $nuevo = $reservar($carpeta->usuario_id, $carpeta->carpeta_padre_id, $carpeta->nombre, true);

            if ($nuevo !== $carpeta->nombre) {
                DB::table('carpetas')->where('id', $carpeta->id)->update(['nombre' => $nuevo]);
            }
        }

        foreach (DB::table('archivos')->whereNull('papelera')->orderBy('id')->get() as $archivo) {
            $nuevo = $reservar($archivo->usuario_id, $archivo->carpeta_id, $archivo->nombre_original, false);

            if ($nuevo !== $archivo->nombre_original) {
                DB::table('archivos')->where('id', $archivo->id)->update(['nombre_original' => $nuevo]);
            }
        }
    }

    /**
     * No se puede deshacer: los nombres antiguos no se guardan.
     */
    public function down(): void
    {
    }
};
