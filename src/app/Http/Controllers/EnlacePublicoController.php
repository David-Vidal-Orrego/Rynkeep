<?php

namespace App\Http\Controllers;

use App\Models\EnlaceCompartido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;

class EnlacePublicoController extends Controller
{
    /**
     * Página pública de un enlace: caducado, pide contraseña o muestra el archivo para descargar.
     * Si hay contraseña y aún no se ha puesto, no se enseña ni el nombre del archivo.
     */
    public function mostrar(Request $request, string $token)
    {
        $enlace = $this->buscar($token);

        if ($enlace->haCaducado()) {
            return response()->view('enlace-publico', ['estado' => 'caducado'], 410);
        }

        if ($this->pideContrasena($request, $enlace)) {
            return view('enlace-publico', ['estado' => 'contrasena', 'token' => $token]);
        }

        return view('enlace-publico', [
            'estado' => 'listo',
            'token' => $token,
            'archivo' => $enlace->archivo,
        ]);
    }

    /**
     * Comprueba la contraseña de un enlace. Solo cuentan los fallos: 5 por persona cada 15 minutos y 20 por enlace cada hora.
     * El tope por enlace es el que protege de verdad, porque no depende de la dirección de quien lo intenta.
     */
    public function acceso(Request $request, string $token)
    {
        $enlace = $this->buscar($token);

        if ($enlace->haCaducado()) {
            return response()->view('enlace-publico', ['estado' => 'caducado'], 410);
        }

        $request->validate(['contrasena' => ['required', 'string', 'max:100']]);

        $claveIp = 'enlace-ip|'.$token.'|'.$request->ip();
        $claveToken = 'enlace-token|'.$token;

        $espera = 0;
        if (RateLimiter::tooManyAttempts($claveIp, 5)) {
            $espera = max($espera, RateLimiter::availableIn($claveIp));
        }
        if (RateLimiter::tooManyAttempts($claveToken, 20)) {
            $espera = max($espera, RateLimiter::availableIn($claveToken));
        }

        if ($espera > 0) {
            return back()->withErrors([
                'contrasena' => __('Demasiados intentos. Vuelve a probar en :min minuto(s).', ['min' => max(1, (int) ceil($espera / 60))]),
            ]);
        }

        if ($enlace->contrasena === null || Hash::check($request->input('contrasena'), $enlace->contrasena)) {
            RateLimiter::clear($claveIp);

            $request->session()->put('enlace_aceptado.'.$enlace->id, true);

            return redirect()->route('enlace.publico', $token);
        }

        RateLimiter::hit($claveIp, 900);
        RateLimiter::hit($claveToken, 3600);

        return back()->withErrors(['contrasena' => __('La contraseña no es correcta.')]);
    }

    /**
     * Descarga el archivo de un enlace. Repite todas las comprobaciones para que no se pueda saltar
     * la contraseña escribiendo la dirección de descarga a mano.
     */
    public function descargar(Request $request, string $token)
    {
        $enlace = $this->buscar($token);

        abort_if($enlace->haCaducado(), 410);
        abort_if($this->pideContrasena($request, $enlace), 403);

        $archivo = $enlace->archivo;

        return Storage::disk('archivos')->download(
            $archivo->ruta_almacenamiento,
            $archivo->nombre_original,
            [
                'Content-Type' => $archivo->tipo_archivo,
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    /**
     * Busca el enlace por su token. Da 404 si no existe, si no apunta a un archivo o si el archivo está en la papelera.
     */
    private function buscar(string $token): EnlaceCompartido
    {
        $enlace = EnlaceCompartido::where('token', $token)->first();

        abort_if($enlace === null || $enlace->archivo === null, 404);

        return $enlace;
    }

    /**
     * Indica si el enlace tiene contraseña y esta visita todavía no la ha puesto bien.
     */
    private function pideContrasena(Request $request, EnlaceCompartido $enlace): bool
    {
        return $enlace->contrasena !== null
            && ! $request->session()->get('enlace_aceptado.'.$enlace->id, false);
    }
}
