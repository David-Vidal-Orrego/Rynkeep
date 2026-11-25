<?php

namespace App\Http\Controllers;

use App\Models\TokenApi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TokenApiController extends Controller
{
    /**
     * Crea un token personal para la API, sin fecha de caducidad, pensado para programas como el cliente de escritorio
     * que no deben guardar la contraseña. El texto del token se enseña una sola vez, justo después de crearlo.
     */
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validateWithBag('tokenApi', [
            'nombre' => ['required', 'string', 'max:100'],
        ]);

        [, $texto] = TokenApi::generar($request->user(), trim($datos['nombre']), TokenApi::TIPO_PERSONAL, null);

        return redirect()->route('profile.edit')->with('token_nuevo', $texto);
    }

    /**
     * Borra un token personal del usuario. Los programas que lo usaban dejan de poder entrar al instante.
     * Si el token es de otro usuario, responde como si no existiera.
     */
    public function destroy(Request $request, TokenApi $token): RedirectResponse
    {
        abort_if($token->usuario_id !== $request->user()->id, 404);

        $token->delete();

        return redirect()->route('profile.edit')->with('status', 'token-eliminado');
    }
}
