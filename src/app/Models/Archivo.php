<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Support\Formato;

class Archivo extends Model
{
    use SoftDeletes;

    protected $table = 'archivos';

    const DELETED_AT = 'papelera';

    protected $fillable = ['nombre_original', 'carpeta_id'];

    /**
     * Conversiones automáticas de los campos.
     */
    protected function casts(): array
    {
        return [
            'tamano' => 'integer',
        ];
    }

    /**
     * Tamaño del archivo en una unidad que se lee bien (KB, MB, GB...).
     */
    public function tamanoLegible(): string
    {
        return Formato::bytes($this->tamano);
    }

    /**
     * Usuario dueño del archivo.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    /**
     * Carpeta que contiene el archivo, o ninguna si está en la raíz.
     */
    public function carpeta(): BelongsTo
    {
        return $this->belongsTo(Carpeta::class);
    }

    /**
     * Etiquetas del archivo.
     */
    public function etiquetas(): BelongsToMany
    {
        return $this->belongsToMany(Etiqueta::class, 'archivo_etiqueta')->withTimestamps();
    }

    /**
     * Enlace público del archivo, si lo tiene. Solo puede haber uno.
     */
    public function enlace(): HasOne
    {
        return $this->hasOne(EnlaceCompartido::class);
    }
}
