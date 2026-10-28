<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Etiqueta extends Model
{
    protected $table = 'etiquetas';

    protected $fillable = ['nombre', 'color'];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function archivos(): BelongsToMany
    {
        return $this->belongsToMany(Archivo::class, 'archivo_etiqueta')->withTimestamps();
    }
}