<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegistroController extends Controller
{
    /**
     * Muestra el formulario de registro.
     */
    public function create(): View
    {
        return view('auth.registro');
    }

    /**
     * Crea una cuenta nueva y entra con ella. Nunca es administradora y recibe el espacio por defecto.
     */
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:usuarios,email'],
            'contrasena' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $usuario = Usuario::create([
            'nombre' => $datos['nombre'],
            'email' => $datos['email'],
            'contrasena' => $datos['contrasena'],
        ]);

        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
