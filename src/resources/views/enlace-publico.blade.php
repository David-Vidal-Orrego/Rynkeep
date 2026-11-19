<x-guest-layout>
    @if ($estado === 'caducado')
        <h1 class="text-lg font-medium text-texto">{{ __('Este enlace ha caducado') }}</h1>
        <p class="mt-2 text-sm text-suave">{{ __('Pide a quien te lo envió que cree uno nuevo.') }}</p>
    @elseif ($estado === 'contrasena')
        <h1 class="text-lg font-medium text-texto">{{ $tipo === 'carpeta' ? __('Carpeta protegida') : __('Archivo protegido') }}</h1>
        <p class="mt-2 text-sm text-suave">{{ $tipo === 'carpeta' ? __('Para abrirla necesitas la contraseña.') : __('Para descargarlo necesitas la contraseña.') }}</p>

        <form method="POST" action="{{ route('enlace.acceso', $token) }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <x-input-label for="contrasena" :value="__('Contraseña')" />
                <x-text-input id="contrasena" name="contrasena" type="password" class="mt-1 block w-full" required autofocus autocomplete="off" />
                <x-input-error class="mt-2" :messages="$errors->get('contrasena')" />
            </div>
            <x-primary-button>{{ __('Continuar') }}</x-primary-button>
        </form>
    @elseif ($estado === 'carpeta')
        <h1 class="text-lg font-medium text-texto break-words">{{ $raiz->nombre }}</h1>

        @if ($ruta)
            <nav class="mt-2 text-sm text-suave flex flex-wrap items-center gap-1">
                <a href="{{ route('enlace.publico', $token) }}" class="underline">{{ $raiz->nombre }}</a>
                @foreach ($ruta as $paso)
                    <span>/</span>
                    @if ($loop->last)
                        <span class="text-texto">{{ $paso->nombre }}</span>
                    @else
                        <a href="{{ route('enlace.publico', ['token' => $token, 'c' => $paso->id]) }}" class="underline">{{ $paso->nombre }}</a>
                    @endif
                @endforeach
            </nav>
        @endif

        <div class="mt-4 divide-y divide-borde border border-borde rounded-md">
            @foreach ($subcarpetas as $subcarpeta)
                <a href="{{ route('enlace.publico', ['token' => $token, 'c' => $subcarpeta->id]) }}" class="flex items-center gap-3 px-3 py-2 hover:bg-fondo">
                    <x-icono nombre="carpeta" class="h-5 w-5 shrink-0" />
                    <span class="min-w-0 truncate text-texto">{{ $subcarpeta->nombre }}</span>
                </a>
            @endforeach

            @foreach ($archivos as $archivo)
                <div class="flex items-center gap-3 px-3 py-2">
                    <x-icono nombre="archivo" class="h-5 w-5 shrink-0" />
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-texto">{{ $archivo->nombre_original }}</p>
                        <p class="text-xs text-suave">{{ $archivo->tamanoLegible() }}</p>
                    </div>
                    <a href="{{ route('enlace.descarga.archivo', ['token' => $token, 'archivo' => $archivo->id]) }}" class="text-sm underline text-primario">{{ __('Descargar') }}</a>
                </div>
            @endforeach

            @if ($subcarpetas->isEmpty() && $archivos->isEmpty())
                <p class="px-3 py-3 text-sm text-suave">{{ __('Esta carpeta está vacía.') }}</p>
            @endif
        </div>
    @else
        <h1 class="text-lg font-medium text-texto break-words">{{ $archivo->nombre_original }}</h1>
        <p class="mt-1 text-sm text-suave">{{ $archivo->tamanoLegible() }}</p>

        <a href="{{ route('enlace.descarga', $token) }}"
            class="mt-6 inline-flex items-center px-4 py-2 bg-primario border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primario-hover transition ease-in-out duration-150">
            {{ __('Descargar') }}
        </a>
    @endif
</x-guest-layout>
