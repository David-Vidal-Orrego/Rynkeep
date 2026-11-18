<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="nombre" :value="__('Nombre')" />
            <x-text-input id="nombre" class="block mt-1 w-full" type="text" name="nombre" :value="old('nombre')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="contrasena" :value="__('Contraseña (mínimo 8 caracteres)')" />
            <x-text-input id="contrasena" class="block mt-1 w-full" type="password" name="contrasena" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('contrasena')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="contrasena_confirmation" :value="__('Repite la contraseña')" />
            <x-text-input id="contrasena_confirmation" class="block mt-1 w-full" type="password" name="contrasena_confirmation" required autocomplete="new-password" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <a href="{{ route('login') }}" class="text-sm underline text-suave hover:text-texto">{{ __('¿Ya tienes cuenta?') }}</a>
            <x-primary-button>{{ __('Crear cuenta') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
