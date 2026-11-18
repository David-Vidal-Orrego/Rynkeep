@php
    $enlace = $archivo->enlace;
    $bolsa = 'enlace-'.$archivo->id;
    $abrir = $errors->hasBag($bolsa) || session('compartir_abierto') == $archivo->id;
@endphp

<x-modal :name="'compartir-'.$archivo->id" :show="$abrir" maxWidth="lg">
    <div class="p-6 space-y-6">
        <h2 class="text-lg font-medium text-texto">{{ __('Compartir «:nombre»', ['nombre' => $archivo->nombre_original]) }}</h2>

        @if ($enlace)
            <div class="space-y-2">
                <x-input-label :for="'url-'.$archivo->id" :value="__('Enlace público')" />
                <div class="flex items-center gap-2">
                    <x-text-input :id="'url-'.$archivo->id" type="text" readonly class="block w-full" :value="route('enlace.publico', $enlace->token)" />
                    <button type="button" onclick="copiarEnlace('url-{{ $archivo->id }}', this)"
                        class="px-3 py-2 text-xs font-semibold uppercase tracking-widest rounded-md border border-borde bg-superficie text-texto hover:bg-fondo transition ease-in-out duration-150">
                        {{ __('Copiar') }}
                    </button>
                </div>
                @if ($enlace->haCaducado())
                    <p class="text-sm text-peligro">
                        {{ __('Este enlace caducó el :fecha y ya no funciona.', ['fecha' => $enlace->fecha_expiracion->format('d/m/Y H:i')]) }}
                        {{ __('Crea uno nuevo o deja de compartir el archivo.') }}
                    </p>
                @else
                    <p class="text-sm text-suave">
                        {{ __('Cualquiera con este enlace puede descargar el archivo.') }}
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

            <form method="POST" action="{{ route('enlaces.destroy', $archivo) }}">
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

        <form method="POST" action="{{ route('enlaces.store', $archivo) }}" class="space-y-4">
            @csrf

            <div>
                <x-input-label :for="'contrasena-'.$archivo->id" :value="__('Contraseña (opcional, mínimo 6 caracteres)')" />
                <x-text-input :id="'contrasena-'.$archivo->id" name="contrasena" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                <x-input-error class="mt-2" :messages="$errors->getBag($bolsa)->get('contrasena')" />
            </div>

            <div>
                <x-input-label :for="'caduca-'.$archivo->id" :value="__('Caduca el (opcional)')" />
                <x-text-input :id="'caduca-'.$archivo->id" name="fecha_expiracion" type="datetime-local" class="mt-1 block w-full" />
                <x-input-error class="mt-2" :messages="$errors->getBag($bolsa)->get('fecha_expiracion')" />
            </div>

            <div class="flex items-center justify-between">
                <x-primary-button>{{ $enlace ? __('Crear enlace nuevo') : __('Crear enlace') }}</x-primary-button>
                <button type="button" x-on:click="$dispatch('close')" class="text-sm underline text-suave">{{ __('Cerrar') }}</button>
            </div>
        </form>
    </div>
</x-modal>
