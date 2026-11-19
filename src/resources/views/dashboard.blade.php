<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-texto leading-tight">{{ $carpetaActual?->nombre ?? __('Mis archivos') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl sm:px-6 lg:px-8 space-y-6">

            <div class="px-4 sm:px-0 space-y-6">
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

                <x-input-error :messages="$errors->get('archivo')" />

                @if (session('status'))
                <p class="text-sm text-primario">{{ session('status') }}</p>
                @endif
            </div>

            <div class="bg-superficie shadow-sm sm:rounded-lg">
                <div class="hidden sm:flex items-center gap-4 px-4 py-2 text-xs uppercase tracking-wide text-suave border-b border-borde">
                    <span class="flex-1">{{ __('Nombre') }}</span>
                    <span class="w-40">{{ __('Fecha') }}</span>
                    <span class="w-24">{{ __('Tamaño') }}</span>
                    <span class="w-8"></span>
                </div>

                <div class="divide-y divide-borde">
                    @foreach ($carpetas as $carpeta)
                    <div>
                    <div class="flex items-center gap-3 sm:gap-4 px-4 py-3 hover:bg-fondo">
                        <a href="{{ route('dashboard', $carpeta->id) }}" class="flex flex-1 min-w-0 items-center gap-3">
                            <x-icono nombre="carpeta" class="h-6 w-6 shrink-0 text-primario" />
                            <span class="min-w-0">
                                <span class="block truncate text-texto">{{ $carpeta->nombre }}</span>
                                <span class="block sm:hidden text-xs text-suave">{{ $carpeta->created_at?->format('d/m/Y') }}</span>
                            </span>
                            @if ($carpeta->enlace)
                            <span class="shrink-0 text-xs {{ $carpeta->enlace->haCaducado() ? 'text-peligro' : 'text-primario' }}">{{ $carpeta->enlace->haCaducado() ? __('Caducado') : __('Compartido') }}</span>
                            @endif
                        </a>
                        <span class="hidden sm:block w-40 text-sm text-suave">{{ $carpeta->created_at?->format('d/m/Y H:i') }}</span>
                        <span class="hidden sm:block w-24 text-sm text-suave">—</span>

                        <div class="w-8 shrink-0">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button type="button" class="p-1 rounded-full text-suave hover:bg-borde focus:outline-none" aria-label="{{ __('Opciones') }}">
                                        <x-icono nombre="puntos" class="h-6 w-6" />
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link :href="route('dashboard', $carpeta->id)">{{ __('Abrir') }}</x-dropdown-link>
                                    <x-dropdown-link href="#" x-data="" x-on:click.prevent="$dispatch('open-modal', 'renombrar-{{ $carpeta->id }}')">{{ __('Renombrar') }}</x-dropdown-link>
                                    <x-dropdown-link href="#" x-data="" x-on:click.prevent="$dispatch('open-modal', 'compartir-carpeta-{{ $carpeta->id }}')">{{ __('Compartir') }}</x-dropdown-link>
                                    <form method="POST" action="{{ route('carpetas.destroy', $carpeta) }}"
                                        onsubmit="return confirm(@js(__('Se enviará a la papelera la carpeta «:nombre» con todo lo que contiene (subcarpetas y archivos). ¿Continuar?', ['nombre' => $carpeta->nombre])))">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="block w-full px-4 py-2 text-start text-sm leading-5 text-peligro hover:bg-fondo focus:outline-none">{{ __('Eliminar') }}</button>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>

                    <x-modal :name="'renombrar-'.$carpeta->id" :show="$errors->hasBag('renombrar-'.$carpeta->id)" maxWidth="md">
                        <form method="POST" action="{{ route('carpetas.update', $carpeta) }}" class="p-6 space-y-4">
                            @csrf
                            @method('PATCH')
                            <h2 class="text-lg font-medium text-texto">{{ __('Renombrar carpeta') }}</h2>
                            <x-text-input name="nombre" type="text" class="block w-full" :value="$carpeta->nombre" required />
                            <x-input-error :messages="$errors->getBag('renombrar-'.$carpeta->id)->get('nombre')" />
                            <div class="flex justify-end gap-3">
                                <x-secondary-button type="button" x-on:click="$dispatch('close')">{{ __('Cancelar') }}</x-secondary-button>
                                <x-primary-button>{{ __('Guardar') }}</x-primary-button>
                            </div>
                        </form>
                    </x-modal>

                    @include('compartir-modal', ['elemento' => $carpeta])
                    </div>
                    @endforeach

                    @foreach ($archivos as $archivo)
                    <div>
                        <div class="flex items-center gap-3 sm:gap-4 px-4 py-3 hover:bg-fondo">
                            <div class="flex flex-1 min-w-0 items-center gap-3">
                                <x-icono nombre="archivo" class="h-6 w-6 shrink-0 text-suave" />
                                <span class="min-w-0">
                                    <span class="block truncate text-texto">{{ $archivo->nombre_original }}</span>
                                    <span class="block sm:hidden text-xs text-suave">{{ $archivo->tamanoLegible() }} · {{ $archivo->created_at->format('d/m/Y') }}</span>
                                </span>
                                @if ($archivo->enlace)
                                <span class="shrink-0 text-xs {{ $archivo->enlace->haCaducado() ? 'text-peligro' : 'text-primario' }}">{{ $archivo->enlace->haCaducado() ? __('Caducado') : __('Compartido') }}</span>
                                @endif
                            </div>
                            <span class="hidden sm:block w-40 text-sm text-suave">{{ $archivo->created_at->format('d/m/Y H:i') }}</span>
                            <span class="hidden sm:block w-24 text-sm text-suave">{{ $archivo->tamanoLegible() }}</span>

                            <div class="w-8 shrink-0">
                                <x-dropdown align="right" width="48">
                                    <x-slot name="trigger">
                                        <button type="button" class="p-1 rounded-full text-suave hover:bg-borde focus:outline-none" aria-label="{{ __('Opciones') }}">
                                            <x-icono nombre="puntos" class="h-6 w-6" />
                                        </button>
                                    </x-slot>
                                    <x-slot name="content">
                                        <x-dropdown-link href="#" x-data="" x-on:click.prevent="$dispatch('open-modal', 'compartir-archivo-{{ $archivo->id }}')">{{ __('Compartir') }}</x-dropdown-link>
                                        <x-dropdown-link :href="route('archivos.descargar', $archivo)">{{ __('Descargar') }}</x-dropdown-link>
                                        <form method="POST" action="{{ route('archivos.destroy', $archivo) }}"
                                            onsubmit="return confirm(@js(__('¿Enviar este archivo a la papelera?')))">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="block w-full px-4 py-2 text-start text-sm leading-5 text-peligro hover:bg-fondo focus:outline-none">{{ __('Eliminar') }}</button>
                                        </form>
                                    </x-slot>
                                </x-dropdown>
                            </div>
                        </div>

                        @include('compartir-modal', ['elemento' => $archivo])
                    </div>
                    @endforeach

                    @if ($carpetas->isEmpty() && $archivos->isEmpty())
                    <p class="px-4 py-4 text-suave">{{ __('Todavía no has subido ningún archivo.') }}</p>
                    @endif
                </div>
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

</script>