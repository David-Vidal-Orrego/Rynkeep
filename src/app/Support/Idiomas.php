<?php

namespace App\Support;

class Idiomas
{

    /**
     * Idiomas disponibles: uno por cada archivo lang/xx.json que tenga la clave "_idioma" (el nombre que sale en el selector).
     * Devuelve ['es' => 'Español', 'en' => 'English', ...]. Un archivo sin esa clave, o con un nombre raro, se ignora.
     */
    public static function disponibles(): array
    {
        $idiomas = [];

        foreach (glob(lang_path('*.json')) ?: [] as $archivo) {
            $codigo = basename($archivo, '.json');

            if (! preg_match('/^[a-z]{2,3}(_[A-Z]{2})?$/', $codigo)) {
                continue;
            }

            $datos = json_decode((string) file_get_contents($archivo), true);
            $nombre = is_array($datos) ? ($datos['_idioma'] ?? null) : null;

            if (is_string($nombre) && trim($nombre) !== '') {
                $idiomas[$codigo] = trim($nombre);
            }
        }

        ksort($idiomas);

        return $idiomas;
    }
}
