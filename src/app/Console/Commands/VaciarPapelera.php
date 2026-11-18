<?php

namespace App\Console\Commands;

use App\Models\Archivo;
use App\Support\Papelera;
use Illuminate\Console\Command;
use Throwable;

class VaciarPapelera extends Command
{
    protected $signature = 'rynkeep:vaciar-papelera';

    protected $description = 'Borra para siempre los archivos que llevan mas de RYNKEEP_DIAS_PAPELERA dias en la papelera';

    /**
     * Borra para siempre los archivos que llevan más de RYNKEEP_DIAS_PAPELERA días en la papelera.
     * Con 0 días no hace nada. Si un archivo falla, sigue con los demás y lo cuenta como fallo.
     */
    public function handle(): int
    {
        $dias = (int) config('rynkeep.dias_papelera');

        if ($dias <= 0) {
            $this->info('La papelera no se vacia sola (RYNKEEP_DIAS_PAPELERA=0).');

            return self::SUCCESS;
        }

        $limite = now()->subDays($dias);
        $borrados = 0;
        $fallos = 0;

        Archivo::onlyTrashed()
            ->where('papelera', '<', $limite)
            ->chunkById(100, function ($archivos) use (&$borrados, &$fallos) {
                foreach ($archivos as $archivo) {
                    try {
                        Papelera::eliminarDefinitivamente($archivo);
                        $borrados++;
                    } catch (Throwable $e) {
                        report($e);
                        $fallos++;
                    }
                }
            });

        $this->info("Archivos borrados definitivamente: {$borrados}. Fallos: {$fallos}.");

        return $fallos > 0 ? self::FAILURE : self::SUCCESS;
    }
}
