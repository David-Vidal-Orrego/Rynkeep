<?php

namespace App\Support;

class Formato
{
    public static function bytes(int|float $bytes): string
    {
        $unidades = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($unidades) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, $i === 0 ? 0 : 1).' '.$unidades[$i];
    }
}