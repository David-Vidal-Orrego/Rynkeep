<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Carpeta extends Model
{
    use SoftDeletes;

    protected $table = 'carpetas';

    const DELETED_AT = 'papelera';

    protected $fillable = ['nombre', 'color', 'carpeta_padre_id'];

    /**
     * Usuario dueño de la carpeta.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    /**
     * Carpeta que contiene a esta, o ninguna si está en la raíz.
     */
    public function padre(): BelongsTo
    {
        return $this->belongsTo(Carpeta::class, 'carpeta_padre_id');
    }

    /**
     * Carpetas que hay dentro de esta.
     */
    public function subcarpetas(): HasMany
    {
        return $this->hasMany(Carpeta::class, 'carpeta_padre_id');
    }

    /**
     * Archivos que hay dentro de esta carpeta.
     */
    public function archivos(): HasMany
    {
        return $this->hasMany(Archivo::class);
    }

    /**
     * Enlace público de la carpeta, si lo tiene.
     */
    public function enlace(): HasOne
    {
        return $this->hasOne(EnlaceCompartido::class);
    }

    /**
     * Indica si esta carpeta es la carpeta raíz dada o está dentro de ella, a cualquier profundidad.
     * Sube de padre en padre; si algún padre está en la papelera o no existe, la respuesta es no.
     */
    public function estaDentroDe(Carpeta $raiz): bool
    {
        $actual = $this;

        for ($i = 0; $i < 200 && $actual !== null; $i++) {
            if ($actual->id === $raiz->id) {
                return true;
            }

            $actual = $actual->padre;
        }

        return false;
    }
}
