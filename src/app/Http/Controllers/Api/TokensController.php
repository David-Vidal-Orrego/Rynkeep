<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TokenApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TokensController extends Controller
{
    /**
     * Lista los tokens personales del usuario, sin el texto del token (que solo se ve al crearlo).
     */
    public function index(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokensApi()
            ->where('tipo', TokenApi::TIPO_PERSONAL)
            ->orderBy('id')
            ->get()
            ->map(fn (TokenApi $token) => $this->describir($token));

        return response()->json(['tokens' => $tokens]);
    }

    /**
     * Crea un token personal, que no caduca solo, para que un programa deje de usar la contraseña.
     * Hay que hacer la petición con un token de sesión (el del login): con un token personal responde 403,
     * para que un token personal robado no pueda fabricar más. El texto del token solo se devuelve aquí, una vez.
     */
    public function store(Request $request): JsonResponse
    {
        abort_unless(
            $request->attributes->get('token_api')->tipo === TokenApi::TIPO_SESION,
            403,
            __('Solo se puede crear un token personal con un token de sesión.')
        );

        $datos = $request->validate(['nombre' => ['required', 'string', 'max:100']]);

        [$token, $texto] = TokenApi::generar($request->user(), trim($datos['nombre']), TokenApi::TIPO_PERSONAL, null);

        return response()->json(array_merge($this->describir($token), ['token' => $texto, 'tipo' => 'Bearer']), 201);
    }

    /**
     * Revoca un token personal del usuario por su id. El programa que lo usaba deja de poder entrar al instante.
     * Si no existe o es de otro usuario, responde 404.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $token = $request->user()->tokensApi()->where('tipo', TokenApi::TIPO_PERSONAL)->findOrFail($id);

        $token->delete();

        return response()->json(['mensaje' => __('Token eliminado.')]);
    }

    /**
     * Datos públicos de un token: id, nombre, fecha de creación y de último uso, en UTC.
     */
    private function describir(TokenApi $token): array
    {
        $fecha = fn (?Carbon $fecha) => $fecha?->copy()->utc()->format('Y-m-d\TH:i:s\Z');

        return [
            'id' => $token->id,
            'nombre' => $token->nombre,
            'creado' => $fecha($token->created_at),
            'ultimo_uso' => $fecha($token->ultimo_uso),
        ];
    }
}
