<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PapeleraController extends Controller
{
    public function index(Request $request)
    {
        $archivos = $request->user()->archivos()
            ->onlyTrashed()
            ->latest('papelera')
            ->get();

        return view('papelera', ['archivos' => $archivos]);
    }

    public function restaurar(Request $request, int $id)
    {
        $archivo = $request->user()->archivos()->onlyTrashed()->findOrFail($id);

        $archivo->restore();

        return back()->with('status', 'Archivo restaurado.');
    }

    public function eliminar(Request $request, int $id)
    {
        $archivo = $request->user()->archivos()->onlyTrashed()->findOrFail($id);

        $ruta = $archivo->ruta_almacenamiento;
        $tamano = $archivo->tamano;
        $usuarioId = $request->user()->id;

        DB::transaction(function () use ($archivo, $tamano, $usuarioId) {
            $usuario = Usuario::whereKey($usuarioId)->lockForUpdate()->first();

            $archivo->forceDelete();

            $usuario->espacio_usado = max(0, $usuario->espacio_usado - $tamano);
            $usuario->save();
        });

        Storage::disk('archivos')->delete($ruta);

        return back()->with('status', 'Archivo eliminado definitivamente.');
    }
}