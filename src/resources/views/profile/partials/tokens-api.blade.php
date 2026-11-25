@php
    $tokensPersonales = auth()->user()->tokensApi()->where('tipo', App\Models\TokenApi::TIPO_PERSONAL)->latest()->get();
    $claseCampo = 'block w-full bg-superficie text-texto border-borde focus:border-primario focus:ring-primario rounded-md shadow-sm text-sm';
@endphp

<section>
    <header>
        <h2 class="text-lg font-medium text-texto">{{ __('Tokens de acceso') }}</h2>
        <p class="mt-1 text-sm text-suave">
            {{ __('Un token deja que un programa, como la aplicación de escritorio, entre en tu cuenta sin saber tu contraseña. No caduca hasta que lo borres.') }}
        </p>
    </header>

    @if (session('token_nuevo'))
        <div class="mt-6 rounded-md border border-primario p-4" x-data="{ copiado: false }">
            <p class="text-sm font-medium text-texto">{{ __('Copia tu token ahora. No se volverá a mostrar.') }}</p>
            <div class="mt-2 flex gap-2">
                <input id="token-nuevo" type="text" readonly value="{{ session('token_nuevo') }}" onclick="this.select()" class="{{ $claseCampo }}">
                <x-secondary-button type="button" class="shrink-0"
                    x-on:click="navigator.clipboard.writeText(document.getElementById('token-nuevo').value); copiado = true">
                    <span x-text="copiado ? @js(__('Copiado')) : @js(__('Copiar'))"></span>
                </x-secondary-button>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('profile.tokens.store') }}" class="mt-6 space-y-2">
        @csrf
        <x-input-label for="nombre_token" :value="__('Nombre del token')" />
        <div class="flex gap-2">
            <x-text-input id="nombre_token" name="nombre" type="text" class="block w-full" :placeholder="__('Por ejemplo: Portátil RynOS')" required />
            <x-primary-button class="shrink-0">{{ __('Crear') }}</x-primary-button>
        </div>
        <x-input-error :messages="$errors->tokenApi->get('nombre')" class="mt-2" />
    </form>

    @if (session('status') === 'token-eliminado')
        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="mt-4 text-sm text-suave">{{ __('Token eliminado.') }}</p>
    @endif

    @if ($tokensPersonales->isNotEmpty())
        <ul class="mt-6 divide-y divide-borde rounded-md border border-borde">
            @foreach ($tokensPersonales as $token)
                <li class="flex items-center justify-between gap-3 p-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-texto">{{ $token->nombre }}</p>
                        <p class="text-xs text-suave">
                            {{ __('Creado el :fecha', ['fecha' => $token->created_at->format('d/m/Y')]) }}
                            ·
                            {{ $token->ultimo_uso ? __('Último uso: :fecha', ['fecha' => $token->ultimo_uso->format('d/m/Y H:i')]) : __('Sin usar') }}
                        </p>
                    </div>
                    <form method="POST" action="{{ route('profile.tokens.destroy', $token) }}" onsubmit="return confirm(@js(__('Los programas que usan este token dejarán de poder entrar. ¿Seguro?')))">
                        @csrf
                        @method('DELETE')
                        <x-danger-button class="shrink-0">{{ __('Borrar') }}</x-danger-button>
                    </form>
                </li>
            @endforeach
        </ul>
    @endif
</section>
