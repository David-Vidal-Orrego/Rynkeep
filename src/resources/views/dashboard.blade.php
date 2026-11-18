<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-texto leading-tight">{{ $carpetaActual?->nombre ?? __('Mis archivos') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if ($carpetaActual)
            <nav class="text-sm text-suave flex flex-wrap items-center gap-1">
                <a href="{{ route('dashboard') }}" class="underline">{{ __('Mis archivos') }}</a>
                @foreach ($ruta as $paso)
                <span>/</span>
                @if ($loop->last)
                <span class="text-texto font-medium">{{ $paso->nombre }}</span>
                @else
                <a href="{{ route('dashboard', $paso->id) }}" class="underline">{{ $paso->nombre }}</a>
                @endif
                @endforeach
            </nav>
            @endif

            <form method="POST" action="{{ route('carpetas.store') }}" class="flex items-center gap-3">
                @csrf
                <input type="hidden" name="carpeta_padre_id" value="{{ $carpetaActual?->id }}">
                <x-text-input name="nombre" type="text" :placeholder="__('Nueva carpeta')" required />
                <x-primary-button>{{ __('Crear carpeta') }}</x-primary-button>
            </form>
            <x-input-error :messages="$errors->get('nombre')" />

            <div class="bg-superficie shadow-sm sm:rounded-lg p-4">
                <div class="flex justify-between text-sm text-suave mb-2">
                    <span>{{ __('Espacio usado') }}</span>
                    <span>{{ __(':usado de :limite', ['usado' => App\Support\Formato::bytes($usuario->espacio_usado), 'limite' => App\Support\Formato::bytes($usuario->espacio_limite)]) }}</span>
                </div>
                <div class="w-full bg-borde rounded-full h-2.5">
                    <div class="{{ $usuario->porcentajeUsado() >= 90 ? 'bg-peligro' : 'bg-primario' }} h-2.5 rounded-full"
                        style="width: {{ $usuario->porcentajeUsado() }}%"></div>
                </div>
            </div>

            <form id="form-subida" method="POST" action="{{ route('archivos.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="carpeta_id" value="{{ $carpetaActual?->id }}">
                <input type="file" name="archivo" id="archivo" class="hidden" data-max="{{ config('rynkeep.tamano_maximo_mb') * 1024 * 1024 }}">
                <label for="archivo"
                    class="inline-flex items-center px-4 py-2 bg-primario border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primario-hover cursor-pointer transition ease-in-out duration-150">
                    <span id="texto-subir">{{ __('Subir archivo') }}</span>
                </label>
            </form>

            <x-input-error :messages="$errors->get('archivo')" />

            @if (session('status'))
            <p class="text-sm text-primario">{{ session('status') }}</p>
            @endif

            <div class="bg-superficie shadow-sm sm:rounded-lg divide-y divide-borde">
                @foreach ($carpetas as $carpeta)
                <a href="{{ route('dashboard', $carpeta->id) }}" class="block px-6 py-3 hover:bg-fondo">
                    {{ $carpeta->nombre }}
                </a>
                @endforeach
            </div>

            <div class="bg-superficie shadow-sm sm:rounded-lg divide-y divide-borde">
                @forelse ($archivos as $archivo)
                <div class="flex items-center justify-between px-6 py-3">
                    <div>
                        <p class="text-texto">{{ $archivo->nombre_original }}</p>
                        <p class="text-sm text-suave">
                            {{ $archivo->tamanoLegible() }} · {{ $archivo->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-4">
                        <button type="button" x-data=""
                            x-on:click.prevent="$dispatch('open-modal', 'compartir-{{ $archivo->id }}')"
                            class="text-sm underline {{ $archivo->enlace?->haCaducado() ? 'text-peligro' : 'text-suave' }}">{{ $archivo->enlace ? ($archivo->enlace->haCaducado() ? __('Caducado') : __('Compartido')) : __('Compartir') }}</button>

                        <a class="text-sm underline text-suave"
                            href="{{ route('archivos.descargar', $archivo) }}">{{ __('Descargar') }}</a>

                        <form method="POST" action="{{ route('archivos.destroy', $archivo) }}"
                            onsubmit="return confirm(@js(__('¿Enviar este archivo a la papelera?')))">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm underline text-peligro">{{ __('Eliminar') }}</button>
                        </form>

                        @include('compartir-modal', ['archivo' => $archivo])
                    </div>
                </div>
                @empty
                <p class="px-6 py-4 text-suave">{{ __('Todavía no has subido ningún archivo.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    function copiarEnlace(id, boton) {
        var campo = document.getElementById(id);
        var texto = campo.value;
        var hecho = function() {
            var original = boton.textContent;
            boton.textContent = @js(__('Copiado'));
            setTimeout(function() { boton.textContent = original; }, 1500);
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(texto).then(hecho);
        } else {
            campo.select();
            document.execCommand('copy');
            hecho();
        }
    }

    document.getElementById('archivo').addEventListener('change', function() {
        var fichero = this.files[0];
        if (!fichero) return;

        if (fichero.size > Number(this.dataset.max)) {
            alert(@js(__('El archivo supera el límite de :mb MB.', ['mb' => config('rynkeep.tamano_maximo_mb')])));
            this.value = '';
            return;
        }

        document.getElementById('texto-subir').textContent = @js(__('Subiendo...'));
        document.getElementById('form-subida').submit();
    });
</script>