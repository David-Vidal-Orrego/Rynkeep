<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AplicarIdioma
{
    /**
     * Aplica el idioma elegido por el usuario en cada petición.
     * Si no ha elegido ninguno, o el elegido ya no existe, se queda el del sistema.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $idioma = $request->user()?->idioma;

        if ($idioma && array_key_exists($idioma, config('rynkeep.idiomas'))) {
            app()->setLocale($idioma);
        }

        return $next($request);
    }
}
