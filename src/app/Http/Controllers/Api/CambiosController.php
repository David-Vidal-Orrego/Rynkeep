<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Archivo;
use App\Models\Carpeta;
use App\Support\Rutas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Throwable;

class CambiosController extends Controller
{
    /**
     * Dice qué ha cambiado desde una fecha, para que un cliente se sincronice sin descargarlo todo cada vez.
     *
     * El parámetro since es una fecha en formato ISO 8601 (por ejemplo 2026-11-25T10:00:00Z). Sin él, devuelve todo lo que
     * hay, que sirve para la primera sincronización. La respuesta trae:
     * - modificados: archivos y carpetas creados, cambiados, renombrados, movidos o restaurados desde esa fecha, en el mismo
     *   formato que el listado. Los que están dentro de una carpeta de la papelera no salen, porque no se pueden alcanzar.
     * - borrados: archivos y carpetas enviados a la papelera desde esa fecha, con su última ruta conocida.
     * - desde y hasta: las fechas usadas. El cliente debe guardar hasta y usarla como since la próxima vez.
     *
     * Detalles que el cliente debe tener en cuenta: si una carpeta aparece como modificada, su contenido ha cambiado de ruta
     * con ella (o ha vuelto de la papelera), y puede listarla para ver qué contiene; si pasaron más días desde la última
     * sincronización que los que dura la papelera, conviene repetir la sincronización completa sin since.
     * La fecha se compara con "mayor o igual", así que algo puede salir dos veces; el cliente debe tratarlo como repetido.
     */
    public function index(Request $request): JsonResponse
    {
        $usuario = $request->user();
        $ahora = now();
        $desde = null;

        if ($request->filled('since')) {
            try {
                $desde = Carbon::parse((string) $request->query('since'))->setTimezone(config('app.timezone'));
            } catch (Throwable) {
                abort(400, __('La fecha de since no es válida. Usa el formato 2026-11-25T10:00:00Z.'));
            }
        }

        $carpetas = $usuario->carpetas()->withTrashed()->get()->keyBy('id');

        $rutaDe = function (string $nombre, ?int $padreId) use ($carpetas): string {
            $partes = [$nombre];

            for ($i = 0; $i < 200 && $padreId !== null; $i++) {
                $padre = $carpetas->get($padreId);

                if ($padre === null) {
                    break;
                }

                array_unshift($partes, $padre->nombre);
                $padreId = $padre->carpeta_padre_id;
            }

            return Rutas::texto($partes);
        };

        $alcanzable = function (?int $carpetaId) use ($carpetas): bool {
            for ($i = 0; $i < 200 && $carpetaId !== null; $i++) {
                $carpeta = $carpetas->get($carpetaId);

                if ($carpeta === null || $carpeta->trashed()) {
                    return false;
                }

                $carpetaId = $carpeta->carpeta_padre_id;
            }

            return true;
        };

        $modificados = $carpetas
            ->filter(fn (Carpeta $carpeta) => ! $carpeta->trashed()
                && $alcanzable($carpeta->carpeta_padre_id)
                && ($desde === null || $carpeta->updated_at->gte($desde)))
            ->map(fn (Carpeta $carpeta) => Rutas::describir($carpeta, $rutaDe($carpeta->nombre, $carpeta->carpeta_padre_id)));

        $archivos = $usuario->archivos()
            ->when($desde, fn ($consulta) => $consulta->where('updated_at', '>=', $desde))
            ->get()
            ->filter(fn (Archivo $archivo) => $alcanzable($archivo->carpeta_id))
            ->map(fn (Archivo $archivo) => Rutas::describir($archivo, $rutaDe($archivo->nombre_original, $archivo->carpeta_id)));

        $borrados = collect();

        if ($desde !== null) {
            $borrados = $carpetas
                ->filter(fn (Carpeta $carpeta) => $carpeta->trashed() && $carpeta->papelera->gte($desde))
                ->map(fn (Carpeta $carpeta) => [
                    'id' => $carpeta->id,
                    'tipo' => 'carpeta',
                    'nombre' => $carpeta->nombre,
                    'ruta' => $rutaDe($carpeta->nombre, $carpeta->carpeta_padre_id),
                ])
                ->concat(
                    $usuario->archivos()->onlyTrashed()->where('papelera', '>=', $desde)->get()
                        ->map(fn (Archivo $archivo) => [
                            'id' => $archivo->id,
                            'tipo' => 'archivo',
                            'nombre' => $archivo->nombre_original,
                            'ruta' => $rutaDe($archivo->nombre_original, $archivo->carpeta_id),
                        ])
                );
        }

        return response()->json([
            'desde' => $desde?->copy()->utc()->format('Y-m-d\TH:i:s\Z'),
            'hasta' => $ahora->copy()->utc()->format('Y-m-d\TH:i:s\Z'),
            'modificados' => $modificados->concat($archivos)->sortBy('ruta')->values(),
            'borrados' => $borrados->sortBy('ruta')->values(),
        ]);
    }
}
