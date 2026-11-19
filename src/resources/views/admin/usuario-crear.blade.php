<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-texto leading-tight">{{ __('Nuevo usuario') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.usuarios.store') }}" class="bg-superficie shadow-sm sm:rounded-lg p-6 space-y-6">
                @csrf

                <div>
                    <x-input-label for="nombre" :value="__('Nombre')" />
                    <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full" :value="old('nombre')" required autofocus autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('nombre')" />
                </div>

                <div>
                    <x-input-label for="email" :value="__('Correo electrónico')" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>

                <div>
                    <x-input-label for="contrasena" :value="__('Contraseña (mínimo 8 caracteres)')" />
                    <x-text-input id="contrasena" name="contrasena" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                    <x-input-error class="mt-2" :messages="$errors->get('contrasena')" />
                </div>

                <div>
                    <x-input-label for="contrasena_confirmation" :value="__('Repite la contraseña')" />
                    <x-text-input id="contrasena_confirmation" name="contrasena_confirmation" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                </div>

                <div>
                    <x-input-label for="limite_gb" :value="__('Límite de espacio en GB (opcional)')" />
                    <x-text-input id="limite_gb" name="limite_gb" type="number" step="any" min="0.1" class="mt-1 block w-full" :value="old('limite_gb')" :placeholder="__('Si lo dejas vacío, se usa el de por defecto')" />
                    <x-input-error class="mt-2" :messages="$errors->get('limite_gb')" />
                </div>

                <label class="flex items-center gap-2 text-sm text-texto">
                    <input type="checkbox" name="es_admin" value="1" class="rounded border-borde bg-superficie text-primario shadow-sm focus:ring-primario" @checked(old('es_admin'))>
                    {{ __('Es administrador') }}
                </label>

                <div class="flex items-center gap-4">
                    <x-primary-button>{{ __('Crear usuario') }}</x-primary-button>
                    <a href="{{ route('admin.usuarios.index') }}" class="text-sm underline text-suave">{{ __('Cancelar') }}</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
