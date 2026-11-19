@php
    $esCarpeta = $elemento instanceof \App\Models\Carpeta;
    $clave = ($esCarpeta ? 'carpeta-' : 'archivo-').$elemento->id;
    $enlace = $elemento->enlace;
    $bolsa = 'enlace-'.$clave;
    $nombre = $esCarpeta ? $elemento->nombre : $elemento->nombre_original;
    $abrir = $errors->hasBag($bolsa) || session('compartir_abierto') === $clave;
@endphp

<x-modal :name="'compartir-'.$clave" :show="$abrir" maxWidth="lg">
    <div class="p-6 space-y-6">
        <h2 class="text-lg font-medium text-texto">{{ __('Compartir «:nombre»', ['nombre' => $nombre]) }}</h2>

        @if ($enlace)
            <div class="space-y-2">
                <x-input-label :for="'url-'.$clave" :value="__('Enlace público')" />
                <div class="flex items-center gap-2">
                    <x-text-input :id="'url-'.$clave" type="text" readonly class="block w-full" :value="route('enlace.publico', $enlace->token)" />
                    <button type="button" onclick="copiarEnlace('url-{{ $clave }}', this)"
                        class="px-3 py-2 text-xs font-semibold uppercase tracking-widest rounded-md border border-borde bg-superficie text-texto hover:bg-fondo transition ease-in-out duration-150">
                        {{ __('Copiar') }}
                    </button>
                </div>
                @if ($enlace->haCaducado())
                    <p class="text-sm text-peligro">
                        {{ __('Este enlace caducó el :fecha y ya no funciona.', ['fecha' => $enlace->fecha_expiracion->format('d/m/Y H:i')]) }}
                        {{ $esCarpeta ? __('Crea uno nuevo o deja de compartir la carpeta.') : __('Crea uno nuevo o deja de compartir el archivo.') }}
                    </p>
                @else
                    <p class="text-sm text-suave">
                        {{ $esCarpeta ? __('Cualquiera con este enlace puede ver y descargar el contenido de la carpeta.') : __('Cualquiera con este enlace puede descargar el archivo.') }}
                        @if ($enlace->contrasena)
                            {{ __('Necesitará la contraseña que has puesto.') }}
                        @endif
                        @if ($enlace->fecha_expiracion)
                            {{ __('Caduca el :fecha.', ['fecha' => $enlace->fecha_expiracion->format('d/m/Y H:i')]) }}
                        @else
                            {{ __('No caduca.') }}
                        @endif
                    </p>
                @endif
            </div>

            <form method="POST" action="{{ route($esCarpeta ? 'enlaces.carpeta.destroy' : 'enlaces.destroy', $elemento) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm underline text-peligro">{{ __('Dejar de compartir') }}</button>
            </form>

            <hr class="border-borde">

            <div>
                <h3 class="text-sm font-medium text-texto">{{ __('Crear un enlace nuevo') }}</h3>
                <p class="text-sm text-suave">{{ __('El enlace actual dejará de funcionar.') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route($esCarpeta ? 'enlaces.carpeta.store' : 'enlaces.store', $elemento) }}" class="space-y-4">
            @csrf

            <div>
                <x-input-label :for="'contrasena-'.$clave" :value="__('Contraseña (opcional, mínimo 6 caracteres)')" />
                <x-text-input :id="'contrasena-'.$clave" name="contrasena" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                <x-input-error class="mt-2" :messages="$errors->getBag($bolsa)->get('contrasena')" />
            </div>

            <div>
                <x-input-label :for="'caduca-'.$clave" :value="__('Caduca el (opcional)')" />
                <x-text-input :id="'caduca-'.$clave" name="fecha_expiracion" type="datetime-local" class="mt-1 block w-full" />
                <x-input-error class="mt-2" :messages="$errors->getBag($bolsa)->get('fecha_expiracion')" />
            </div>

            <div class="flex items-center justify-between">
                <x-primary-button>{{ $enlace ? __('Crear enlace nuevo') : __('Crear enlace') }}</x-primary-button>
                <button type="button" x-on:click="$dispatch('close')" class="text-sm underline text-suave">{{ __('Cerrar') }}</button>
            </div>
        </form>
    </div>
</x-modal>
