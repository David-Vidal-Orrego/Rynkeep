<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\SoloAdmin::class,
            'api.token' => \App\Http\Middleware\AutenticarApi::class,
        ]);

        $middleware->web(append: [\App\Http\Middleware\AplicarIdioma::class]);

        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash(['contrasena', 'contrasena_confirmation']);

        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*') || $request->expectsJson());

        /**
         * Hace que todos los errores de la API tengan la misma forma: {"error": "mensaje"}.
         * Los fallos de validación usan el código 422 y añaden "campos" con el detalle de cada campo.
         * Fuera de la API no hace nada y siguen las páginas de error normales.
         */
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return response()->json(['error' => $e->validator->errors()->first(), 'campos' => $e->errors()], 422);
            }

            if ($e instanceof AuthenticationException) {
                return response()->json(['error' => __('No autenticado. Envía un token válido en la cabecera Authorization.')], 401);
            }

            if ($e instanceof ModelNotFoundException) {
                return response()->json(['error' => __('No encontrado.')], 404);
            }

            if ($e instanceof HttpExceptionInterface) {
                $estado = $e->getStatusCode();

                if ($estado === 404) {
                    $mensaje = __('No encontrado.');
                } else {
                    $mensaje = $e->getMessage() !== '' ? $e->getMessage() : (Response::$statusTexts[$estado] ?? 'Error');
                }

                return response()->json(['error' => $mensaje], $estado, $e->getHeaders());
            }

            if (config('app.debug')) {
                return null;
            }

            return response()->json(['error' => __('Error interno del servidor.')], 500);
        });
    })->create();
