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
}
