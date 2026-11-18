<x-guest-layout>
    @if ($estado === 'caducado')
        <h1 class="text-lg font-medium text-texto">{{ __('Este enlace ha caducado') }}</h1>
        <p class="mt-2 text-sm text-suave">{{ __('Pide a quien te lo envió que cree uno nuevo.') }}</p>
    @elseif ($estado === 'contrasena')
        <h1 class="text-lg font-medium text-texto">{{ __('Archivo protegido') }}</h1>
        <p class="mt-2 text-sm text-suave">{{ __('Para descargarlo necesitas la contraseña.') }}</p>

        <form method="POST" action="{{ route('enlace.acceso', $token) }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <x-input-label for="contrasena" :value="__('Contraseña')" />
                <x-text-input id="contrasena" name="contrasena" type="password" class="mt-1 block w-full" required autofocus autocomplete="off" />
                <x-input-error class="mt-2" :messages="$errors->get('contrasena')" />
            </div>
            <x-primary-button>{{ __('Continuar') }}</x-primary-button>
        </form>
    @else
        <h1 class="text-lg font-medium text-texto break-words">{{ $archivo->nombre_original }}</h1>
        <p class="mt-1 text-sm text-suave">{{ $archivo->tamanoLegible() }}</p>

        <a href="{{ route('enlace.descarga', $token) }}"
            class="mt-6 inline-flex items-center px-4 py-2 bg-primario border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primario-hover transition ease-in-out duration-150">
            {{ __('Descargar') }}
        </a>
    @endif
</x-guest-layout>
