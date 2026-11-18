<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SoloAdmin
{
    /**
     * Deja pasar solo a administradores. A cualquier otro le responde 404, como si la página no existiera.
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->es_admin, 404);

        return $next($request);
    }
}