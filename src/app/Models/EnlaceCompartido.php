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

    protected function casts(): array
    {
        return [
            'contrasena' => 'hashed',
            'fecha_expiracion' => 'datetime',
        ];
    }

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

    public function archivo(): BelongsTo
    {
        return $this->belongsTo(Archivo::class);
    }

    public function carpeta(): BelongsTo
    {
        return $this->belongsTo(Carpeta::class);
    }

    public function haCaducado(): bool
    {
        return $this->fecha_expiracion !== null && $this->fecha_expiracion->isPast();
    }
}