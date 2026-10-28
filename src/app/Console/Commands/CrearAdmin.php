<?php

namespace App\Console\Commands;

use App\Models\Usuario;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CrearAdmin extends Command
{
    protected $signature = 'rynkeep:crear-admin {--limite= : Límite de espacio en GB (si no se indica, se usa el del .env)}';

    protected $description = 'Crea una cuenta de administrador';

    public function handle(): int
    {
        $nombre = $this->ask('Nombre');
        $email = $this->ask('Correo electrónico');
        $contrasena = $this->secret('Contraseña (mínimo 8 caracteres)');
        $confirmacion = $this->secret('Repite la contraseña');
        $limite = $this->option('limite');

        $validador = Validator::make(
            [
                'nombre' => $nombre,
                'email' => $email,
                'contrasena' => $contrasena,
                'contrasena_confirmation' => $confirmacion,
                'limite' => $limite,
            ],
            [
                'nombre' => ['required', 'string', 'max:100'],
                'email' => ['required', 'email', 'max:150', 'unique:usuarios,email'],
                'contrasena' => ['required', 'string', 'min:8', 'confirmed'],
                'limite' => ['nullable', 'numeric', 'min:1'],
            ]
        );

        if ($validador->fails()) {
            foreach ($validador->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        $usuario = new Usuario([
            'nombre' => $nombre,
            'email' => $email,
            'contrasena' => $contrasena,
        ]);

        $usuario->es_admin = true;

        if ($limite !== null) {
            $usuario->espacio_limite = (int) ($limite * 1024 ** 3);
        }

        $usuario->save();

        $this->info("Administrador {$email} creado correctamente.");

        return self::SUCCESS;
    }
}