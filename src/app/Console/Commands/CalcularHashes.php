<?php

namespace App\Console\Commands;

use App\Models\Archivo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CalcularHashes extends Command
{
    protected $signature = 'rynkeep:calcular-hashes';

    protected $description = 'Calcula la huella (SHA-256) de los archivos que todavia no la tienen';

    /**
     * Calcula la huella de contenido de todos los archivos que no la tienen, incluidos los de la papelera,
     * leyéndolos del disco. Sirve para los archivos subidos antes de que existieran las huellas.
     * No cambia la fecha de modificación de los archivos. Si un fichero falta en el disco, lo cuenta como fallo y sigue.
     */
    public function handle(): int
    {
        $calculados = 0;
        $fallos = 0;

        Archivo::withTrashed()->whereNull('hash')->chunkById(100, function ($archivos) use (&$calculados, &$fallos) {
            foreach ($archivos as $archivo) {
                $ruta = Storage::disk('archivos')->path($archivo->ruta_almacenamiento);

                if (! is_file($ruta)) {
                    $fallos++;

                    continue;
                }

                DB::table('archivos')->where('id', $archivo->id)->update(['hash' => hash_file('sha256', $ruta)]);
                $calculados++;
            }
        });

        $this->info('Huellas calculadas: '.$calculados.'. Archivos que faltan en el disco: '.$fallos.'.');

        return self::SUCCESS;
    }
}
