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

    protected function casts(): array
    {
        return [
            'contrasena' => 'hashed',
            'es_admin' => 'boolean',
            'espacio_limite' => 'integer',
            'espacio_usado' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Usuario $usuario) {
            if ($usuario->espacio_limite === null) {
                $usuario->espacio_limite = config('rynkeep.limite_defecto_gb') * 1024 ** 3;
            }
        });
    }

    public function porcentajeUsado(): float
    {
        if ($this->espacio_limite <= 0) {
            return 0;
        }

        return min(100, round($this->espacio_usado / $this->espacio_limite * 100, 1));
    }

    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    public function getAuthPasswordName()
    {
        return 'contrasena';
    }

    public function carpetas(): HasMany
    {
        return $this->hasMany(Carpeta::class);
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(Archivo::class);
    }

    public function etiquetas(): HasMany
    {
        return $this->hasMany(Etiqueta::class);
    }
}
