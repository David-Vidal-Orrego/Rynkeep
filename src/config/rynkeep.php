<?php

return [
    'limite_defecto_gb' => (int) env('RYNKEEP_LIMITE_DEFECTO_GB', 10),
    'registro_abierto' => (bool) env('RYNKEEP_REGISTRO_ABIERTO', false),
    'dias_papelera' => (int) env('RYNKEEP_DIAS_PAPELERA', 30),
];