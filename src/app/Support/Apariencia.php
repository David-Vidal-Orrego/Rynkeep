<?php

namespace App\Support;

use App\Models\Usuario;

class Apariencia
{
    public const TEMAS = ['sistema', 'claro', 'oscuro'];

    public const LETRAS = ['nunito', 'inter', 'atkinson', 'comic', 'sistema'];

    public const TAMANOS = ['normal', 'grande', 'muy_grande'];

    /**
     * Preferencias de apariencia de un usuario, con el valor por defecto en las que no ha elegido:
     * tema sistema, letra nunito y tamaño normal.
     */
    public static function de(Usuario $usuario): array
    {
        return [
            'tema' => $usuario->tema ?? 'sistema',
            'letra' => $usuario->letra ?? 'nunito',
            'tamano' => $usuario->tamano ?? 'normal',
        ];
    }

    /**
     * Reglas de validación de las tres preferencias. Todas son opcionales (se puede enviar solo la que cambia),
     * pero la que se envíe debe ser uno de los valores permitidos.
     */
    public static function reglas(): array
    {
        return [
            'tema' => ['sometimes', 'in:'.implode(',', self::TEMAS)],
            'letra' => ['sometimes', 'in:'.implode(',', self::LETRAS)],
            'tamano' => ['sometimes', 'in:'.implode(',', self::TAMANOS)],
        ];
    }

    /**
     * Guarda en el usuario las preferencias recibidas ya validadas y devuelve las preferencias completas.
     */
    public static function guardar(Usuario $usuario, array $datos): array
    {
        foreach ($datos as $campo => $valor) {
            $usuario->{$campo} = $valor;
        }

        $usuario->save();

        return self::de($usuario);
    }
}
