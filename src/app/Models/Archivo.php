<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Support\Formato;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
            'modificado_original' => 'datetime',
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
     * Devuelve la huella (SHA-256) del contenido del archivo. Si todavía no está guardada, la calcula leyendo el fichero
     * del disco y la guarda sin cambiar la fecha de modificación. Devuelve null si el fichero no está en el disco.
     */
    public function asegurarHash(): ?string
    {
        if ($this->hash === null) {
            $ruta = Storage::disk('archivos')->path($this->ruta_almacenamiento);

            if (is_file($ruta)) {
                $this->hash = hash_file('sha256', $ruta);
                DB::table('archivos')->where('id', $this->id)->update(['hash' => $this->hash]);
            }
        }

        return $this->hash;
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
