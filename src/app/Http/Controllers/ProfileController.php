<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Support\Apariencia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Cambia el idioma del usuario. Vacio = usar el del sistema.
     */
    public function actualizarIdioma(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'idioma' => ['nullable', Rule::in(array_keys(config('rynkeep.idiomas')))],
        ]);

        $usuario = $request->user();
        $usuario->idioma = $datos['idioma'] ?? null;
        $usuario->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Guarda las preferencias de apariencia del usuario (tema, letra y tamaño) cuando las cambia en el Perfil.
     * La página lo llama en segundo plano al cambiar un selector. Acepta solo valores permitidos y devuelve las preferencias completas.
     */
    public function actualizarApariencia(Request $request): JsonResponse
    {
        $datos = $request->validate(Apariencia::reglas());

        return response()->json(Apariencia::guardar($request->user(), $datos));
    }
}
