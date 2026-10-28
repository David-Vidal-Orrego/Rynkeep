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

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function padre(): BelongsTo
    {
        return $this->belongsTo(Carpeta::class, 'carpeta_padre_id');
    }

    public function subcarpetas(): HasMany
    {
        return $this->hasMany(Carpeta::class, 'carpeta_padre_id');
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(Archivo::class);
    }

    public function enlace(): HasOne
    {
        return $this->hasOne(EnlaceCompartido::class);
    }
}
