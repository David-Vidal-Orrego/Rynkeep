<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-texto leading-tight">{{ __('Editar usuario') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.usuarios.update', $usuario) }}" class="bg-superficie shadow-sm sm:rounded-lg p-6 space-y-6">
                @csrf
                @method('PATCH')

                <p class="text-sm text-suave">
                    {{ __('Espacio usado: :tamano', ['tamano' => App\Support\Formato::bytes($usuario->espacio_usado)]) }}
                </p>

                <div>
                    <x-input-label for="nombre" :value="__('Nombre')" />
                    <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full" :value="old('nombre', $usuario->nombre)" required autofocus autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('nombre')" />
                </div>

                <div>
                    <x-input-label for="email" :value="__('Correo electrónico')" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $usuario->email)" required autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>

                <div>
                    <x-input-label for="contrasena" :value="__('Nueva contraseña (déjala vacía para no cambiarla)')" />
                    <x-text-input id="contrasena" name="contrasena" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                    <x-input-error class="mt-2" :messages="$errors->get('contrasena')" />
                </div>

                <div>
                    <x-input-label for="contrasena_confirmation" :value="__('Repite la nueva contraseña')" />
                    <x-text-input id="contrasena_confirmation" name="contrasena_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                </div>

                <div>
                    <x-input-label for="limite_gb" :value="__('Límite de espacio en GB')" />
                    <x-text-input id="limite_gb" name="limite_gb" type="number" step="any" min="0.1" class="mt-1 block w-full" :value="old('limite_gb', round($usuario->espacio_limite / 1024 ** 3, 2))" required />
                    <x-input-error class="mt-2" :messages="$errors->get('limite_gb')" />
                </div>

                <div>
                    @if ($usuario->is(auth()->user()))
                        <input type="hidden" name="es_admin" value="1">
                        <label class="flex items-center gap-2 text-sm text-suave">
                            <input type="checkbox" checked disabled class="rounded border-borde bg-superficie text-primario shadow-sm">
                            {{ __('Es administrador (no puedes quitártelo a ti mismo)') }}
                        </label>
                    @else
                        <label class="flex items-center gap-2 text-sm text-texto">
                            <input type="checkbox" name="es_admin" value="1" class="rounded border-borde bg-superficie text-primario shadow-sm focus:ring-primario" @checked($errors->any() ? old('es_admin') : $usuario->es_admin)>
                            {{ __('Es administrador') }}
                        </label>
                    @endif
                    <x-input-error class="mt-2" :messages="$errors->get('es_admin')" />
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>{{ __('Guardar cambios') }}</x-primary-button>
                    <a href="{{ route('admin.usuarios.index') }}" class="text-sm underline text-suave">{{ __('Cancelar') }}</a>
                </div>
            </form>

            @unless ($usuario->is(auth()->user()))
                <form method="POST" action="{{ route('admin.usuarios.destroy', $usuario) }}" class="mt-8 bg-superficie shadow-sm sm:rounded-lg p-6 space-y-4 border border-peligro">
                    @csrf
                    @method('DELETE')

                    <h3 class="font-semibold text-peligro">{{ __('Eliminar usuario') }}</h3>
                    <p class="text-sm text-suave">
                        {{ __('Se borrará la cuenta y todos sus archivos, carpetas y etiquetas, también los que estén en la papelera. Ahora tiene :n archivo(s). No se puede deshacer.', ['n' => $usuario->archivos()->withTrashed()->count()]) }}
                    </p>

                    <div>
                        <x-input-label for="confirmar_email" :value="__('Escribe :email para confirmar', ['email' => $usuario->email])" />
                        <x-text-input id="confirmar_email" name="confirmar_email" type="text" class="mt-1 block w-full" required autocomplete="off" />
                        <x-input-error class="mt-2" :messages="$errors->get('confirmar_email')" />
                    </div>

                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-peligro border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:opacity-90 transition ease-in-out duration-150">
                        {{ __('Eliminar usuario') }}
                    </button>
                </form>
            @endunless
        </div>
    </div>
</x-app-layout>
