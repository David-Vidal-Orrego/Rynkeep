<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Cambia la contraseña del usuario y cierra todas sus demás sesiones.
     *
     * Valida la contraseña actual y la nueva, guarda la nueva cifrada y borra
     * de la tabla de sesiones todas las del usuario excepto la actual. Así,
     * si alguien tenía la sesión abierta en otro dispositivo, se le echa.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'contrasena' => Hash::make($validated['password']),
        ]);

        DB::table(config('session.table'))
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->where('id', '!=', $request->session()->getId())
            ->delete();

        return back()->with('status', 'password-updated');
    }
}
