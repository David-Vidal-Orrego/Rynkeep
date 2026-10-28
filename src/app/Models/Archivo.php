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

    protected function casts(): array
    {
        return [
            'tamano' => 'integer',
        ];
    }

    public function tamanoLegible(): string
    {
        return Formato::bytes($this->tamano);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function carpeta(): BelongsTo
    {
        return $this->belongsTo(Carpeta::class);
    }

    public function etiquetas(): BelongsToMany
    {
        return $this->belongsToMany(Etiqueta::class, 'archivo_etiqueta')->withTimestamps();
    }

    public function enlace(): HasOne
    {
        return $this->hasOne(EnlaceCompartido::class);
    }
}
