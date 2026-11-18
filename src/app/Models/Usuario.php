<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';

    protected $fillable = ['nombre', 'email', 'contrasena'];

    protected $hidden = ['contrasena', 'remember_token'];

    /**
     * Conversiones automáticas: la contraseña se cifra sola y los números y el permiso de administrador tienen su tipo.
     */
    protected function casts(): array
    {
        return [
            'contrasena' => 'hashed',
            'es_admin' => 'boolean',
            'espacio_limite' => 'integer',
            'espacio_usado' => 'integer',
        ];
    }

    /**
     * Si al crear un usuario no se indica su espacio, recibe el límite por defecto del .env.
     */
    protected static function booted(): void
    {
        static::creating(function (Usuario $usuario) {
            if ($usuario->espacio_limite === null) {
                $usuario->espacio_limite = config('rynkeep.limite_defecto_gb') * 1024 ** 3;
            }
        });
    }

    /**
     * Porcentaje de su espacio que ya ocupa, para la barra de Mis archivos.
     */
    public function porcentajeUsado(): float
    {
        if ($this->espacio_limite <= 0) {
            return 0;
        }

        return min(100, round($this->espacio_usado / $this->espacio_limite * 100, 1));
    }

    /**
     * Laravel lee la contraseña del usuario de la columna contrasena.
     */
    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    /**
     * Nombre de la columna donde está la contraseña.
     */
    public function getAuthPasswordName()
    {
        return 'contrasena';
    }

    /**
     * Carpetas del usuario.
     */
    public function carpetas(): HasMany
    {
        return $this->hasMany(Carpeta::class);
    }

    /**
     * Archivos del usuario.
     */
    public function archivos(): HasMany
    {
        return $this->hasMany(Archivo::class);
    }

    /**
     * Etiquetas del usuario.
     */
    public function etiquetas(): HasMany
    {
        return $this->hasMany(Etiqueta::class);
    }
}
