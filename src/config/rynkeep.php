<?php

return [
    'limite_defecto_gb' => (int) env('RYNKEEP_LIMITE_DEFECTO_GB', 10),
    'registro_abierto' => (bool) env('RYNKEEP_REGISTRO_ABIERTO', false),
    // Tamano maximo de un archivo subido, en MB (lo comparten Nginx, PHP y la aplicacion)
    'tamano_maximo_mb' => (int) env('RYNKEEP_TAMANO_MAXIMO_MB', 100),
    'dias_papelera' => (int) env('RYNKEEP_DIAS_PAPELERA', 30),

    // Idiomas disponibles: se detectan solos mirando los archivos lang/xx.json (ver README)
    'idiomas' => \App\Support\Idiomas::disponibles(),
];