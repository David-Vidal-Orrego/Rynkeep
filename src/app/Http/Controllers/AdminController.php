<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Support\Formato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class AdminController extends Controller
{
    /**
     * Lista todos los usuarios, ordenados por nombre.
     */
    public function usuarios()
    {
        return view('admin.usuarios', [
            'usuarios' => Usuario::orderBy('nombre')->get(),
        ]);
    }

    /**
     * Muestra el formulario para crear un usuario.
     */
    public function crearUsuario()
    {
        return view('admin.usuario-crear');
    }

    /**
     * Valida y crea un usuario nuevo.
     * Los campos es_admin y espacio_limite no son rellenables a propósito: los asigna el servidor.
     * Si no se indica límite, el modelo usa el del .env.
     */
    public function guardarUsuario(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:usuarios,email'],
            'contrasena' => ['required', 'string', 'min:8', 'confirmed'],
            'limite_gb' => ['nullable', 'numeric', 'min:0.1', 'max:100000'],
        ]);

        $usuario = new Usuario([
            'nombre' => $datos['nombre'],
            'email' => $datos['email'],
            'contrasena' => $datos['contrasena'],
        ]);

        $usuario->es_admin = $request->boolean('es_admin');

        if (! empty($datos['limite_gb'])) {
            $usuario->espacio_limite = (int) round($datos['limite_gb'] * 1024 ** 3);
        }

        $usuario->save();

        return redirect()->route('admin.usuarios.index')->with('status', __('Usuario creado.'));
    }

    /**
     * Muestra el formulario para editar un usuario.
     */
    public function editarUsuario(Usuario $usuario)
    {
        return view('admin.usuario-editar', ['usuario' => $usuario]);
    }

    /**
     * Actualiza nombre, correo, límite de espacio y permiso de administrador de un usuario.
     * La contraseña solo se cambia si se escribe una nueva, el límite no puede quedar por debajo del espacio que ya usa
     * y un administrador no puede quitarse a sí mismo el permiso (así el sistema nunca se queda sin ninguno).
     */
    public function actualizarUsuario(Request $request, Usuario $usuario)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', Rule::unique('usuarios', 'email')->ignore($usuario->id)],
            'contrasena' => ['nullable', 'string', 'min:8', 'confirmed'],
            'limite_gb' => ['required', 'numeric', 'min:0.1', 'max:100000'],
        ]);

        $entrada = $request->except(['contrasena', 'contrasena_confirmation']);

        $nuevoLimite = (int) round($datos['limite_gb'] * 1024 ** 3);
        if ($nuevoLimite < $usuario->espacio_usado) {
            return back()->withInput($entrada)->withErrors([
                'limite_gb' => __('El límite no puede ser menor que el espacio que ya usa (:tamano).', ['tamano' => Formato::bytes($usuario->espacio_usado)]),
            ]);
        }

        $esAdmin = $request->boolean('es_admin');

        if ($usuario->is($request->user()) && ! $esAdmin) {
            return back()->withInput($entrada)->withErrors([
                'es_admin' => __('No puedes quitarte a ti mismo el permiso de administrador.'),
            ]);
        }

        $usuario->nombre = $datos['nombre'];
        $usuario->email = $datos['email'];

        if (! empty($datos['contrasena'])) {
            $usuario->contrasena = $datos['contrasena'];
        }

        $usuario->espacio_limite = $nuevoLimite;
        $usuario->es_admin = $esAdmin;
        $usuario->save();

        return redirect()->route('admin.usuarios.index')->with('status', __('Usuario actualizado.'));
    }

    /**
     * Elimina un usuario y todo lo suyo. Hay que escribir su correo para confirmar y nadie puede borrarse a sí mismo.
     * Primero se borra la base de datos (las claves foráneas en cascada se llevan carpetas, archivos, etiquetas y enlaces)
     * y después los ficheros del disco, incluidos los de la papelera.
     */
    public function eliminarUsuario(Request $request, Usuario $usuario)
    {
        if ($usuario->is($request->user())) {
            return back()->withErrors(['confirmar_email' => __('No puedes eliminar tu propia cuenta.')]);
        }

        $request->validate(['confirmar_email' => ['required', 'string']]);

        if (mb_strtolower(trim($request->input('confirmar_email'))) !== mb_strtolower($usuario->email)) {
            return back()->withErrors(['confirmar_email' => __('El correo no coincide. No se ha eliminado nada.')]);
        }

        $id = $usuario->id;

        $rutas = $usuario->archivos()->withTrashed()->pluck('ruta_almacenamiento')->all();

        DB::transaction(function () use ($usuario) {
            DB::table('sessions')->where('user_id', $usuario->id)->delete();
            $usuario->delete();
        });

        try {
            Storage::disk('archivos')->delete($rutas);
            Storage::disk('archivos')->deleteDirectory('u'.$id);
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('admin.usuarios.index')
                ->with('status', __('Usuario eliminado, pero no se pudieron borrar todos sus archivos del disco (carpeta :carpeta).', ['carpeta' => 'u'.$id]));
        }

        return redirect()->route('admin.usuarios.index')->with('status', __('Usuario eliminado.'));
    }
}
