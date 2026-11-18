<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use InvalidArgumentException;

class EnlaceCompartido extends Model
{
    protected $table = 'enlaces_compartidos';

    protected $fillable = ['contrasena', 'fecha_expiracion'];

    protected $hidden = ['contrasena'];

    /**
     * Conversiones automáticas: la contraseña se guarda cifrada y la caducidad es una fecha.
     */
    protected function casts(): array
    {
        return [
            'contrasena' => 'hashed',
            'fecha_expiracion' => 'datetime',
        ];
    }

    /**
     * Al crear un enlace se genera su token aleatorio. Al guardarlo se comprueba
     * que apunte a un archivo o a una carpeta, pero no a los dos a la vez.
     */
    protected static function booted(): void
    {
        static::creating(function (EnlaceCompartido $enlace) {
            $enlace->token = Str::random(64);
        });

        static::saving(function (EnlaceCompartido $enlace) {
            if (is_null($enlace->archivo_id) === is_null($enlace->carpeta_id)) {
                throw new InvalidArgumentException('Un enlace debe apuntar a un archivo o a una carpeta, pero no a ambos.');
            }
        });
    }

    /**
     * Archivo al que apunta el enlace.
     */
    public function archivo(): BelongsTo
    {
        return $this->belongsTo(Archivo::class);
    }

    /**
     * Carpeta a la que apunta el enlace.
     */
    public function carpeta(): BelongsTo
    {
        return $this->belongsTo(Carpeta::class);
    }

    /**
     * Indica si el enlace tiene fecha de caducidad y ya ha pasado.
     */
    public function haCaducado(): bool
    {
        return $this->fecha_expiracion !== null && $this->fecha_expiracion->isPast();
    }
}