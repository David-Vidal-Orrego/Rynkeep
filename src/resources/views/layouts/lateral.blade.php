@php
    $carpetaNuevaId = request()->routeIs('dashboard') ? request()->route('carpeta')?->id : null;
    $nombresActuales = App\Support\Nombres::deCarpeta(Auth::id(), $carpetaNuevaId);
    $claseEnlace = 'flex items-center gap-3 px-3 py-2 rounded-md text-sm transition';
    $activo = 'bg-fondo text-texto font-medium';
    $inactivo = 'text-suave hover:bg-fondo hover:text-texto';
@endphp

<div x-show="lateral" x-cloak @click="lateral = false" class="fixed inset-0 top-16 z-30 bg-black/40 md:hidden"></div>

<aside :class="lateral ? 'translate-x-0' : '-translate-x-full'"
    class="fixed top-16 bottom-0 start-0 z-40 w-64 bg-superficie border-e border-borde transform transition-transform md:sticky md:top-16 md:h-[calc(100vh-4rem)] md:w-60 md:shrink-0 md:translate-x-0 flex flex-col">
    <div class="p-3 pb-0">
        <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'nueva-carpeta')"
            class="flex w-full items-center justify-center gap-2 px-5 py-3 rounded-2xl border border-borde bg-superficie font-semibold text-sm text-texto hover:bg-fondo focus:outline-none transition">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            {{ __('Nueva carpeta') }}
        </button>

        <label for="archivo"
            class="mt-2 flex w-full items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-primario font-semibold text-sm text-white shadow hover:bg-primario-hover cursor-pointer transition">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V4.5m0 0l-4.5 4.5M12 4.5L16.5 9M4.5 19.5h15" /></svg>
            <span id="texto-nuevo">{{ __('Subir archivo') }}</span>
        </label>
    </div>

    <div class="p-3 space-y-1 flex-1 overflow-y-auto">
        <a href="{{ route('dashboard') }}" class="{{ $claseEnlace }} {{ request()->routeIs('dashboard') ? $activo : $inactivo }}">
            <x-icono nombre="carpeta" class="h-5 w-5" />
            {{ __('Mis archivos') }}
        </a>

        <a href="{{ route('papelera.index') }}" class="{{ $claseEnlace }} {{ request()->routeIs('papelera.*') ? $activo : $inactivo }}">
            <x-icono nombre="papelera" class="h-5 w-5" />
            {{ __('Papelera') }}
        </a>

        @if (Auth::user()->es_admin)
        <a href="{{ route('admin.usuarios.index') }}" class="{{ $claseEnlace }} {{ request()->routeIs('admin.*') ? $activo : $inactivo }}">
            <x-icono nombre="usuario" class="h-5 w-5" />
            {{ __('Usuarios') }}
        </a>
        @endif
    </div>
    @php $usuarioActual = Auth::user(); @endphp
    <div class="p-4 border-t border-borde">
        <div class="w-full bg-borde rounded-full h-2.5">
            <div class="{{ $usuarioActual->porcentajeUsado() >= 90 ? 'bg-peligro' : 'bg-primario' }} h-2.5 rounded-full"
                style="width: {{ $usuarioActual->porcentajeUsado() }}%"></div>
        </div>
        <p class="mt-2 text-xs text-suave">{{ __(':usado de :limite', ['usado' => App\Support\Formato::bytes($usuarioActual->espacio_usado), 'limite' => App\Support\Formato::bytes($usuarioActual->espacio_limite)]) }}</p>
    </div>
</aside>

<form id="form-subida" method="POST" action="{{ route('archivos.store') }}" enctype="multipart/form-data" class="hidden">
    @csrf
    <input type="hidden" name="carpeta_id" value="{{ $carpetaNuevaId }}">
    <input type="hidden" name="conflicto" id="conflicto" value="">
    <input type="file" name="archivo" id="archivo" data-max="{{ config('rynkeep.tamano_maximo_mb') * 1024 * 1024 }}" data-nombres="{{ json_encode($nombresActuales) }}">
</form>

<x-modal name="nueva-carpeta" :show="$errors->has('nombre')" maxWidth="md">
    <form method="POST" action="{{ route('carpetas.store') }}" class="p-6 space-y-4">
        @csrf
        <input type="hidden" name="carpeta_padre_id" value="{{ $carpetaNuevaId }}">
        <h2 class="text-lg font-medium text-texto">{{ __('Nueva carpeta') }}</h2>
        <x-text-input name="nombre" type="text" class="block w-full" :placeholder="__('Nombre')" required autofocus />
        <x-input-error :messages="$errors->get('nombre')" />
        <div class="flex justify-end gap-3">
            <x-secondary-button type="button" x-on:click="$dispatch('close')">{{ __('Cancelar') }}</x-secondary-button>
            <x-primary-button>{{ __('Crear carpeta') }}</x-primary-button>
        </div>
    </form>
</x-modal>

<x-modal name="archivo-duplicado" maxWidth="md">
    <div class="p-6 space-y-4">
        <h2 class="text-lg font-medium text-texto">{{ __('Ya existe con ese nombre') }}</h2>
        <p id="texto-duplicado" class="text-sm text-suave break-words"></p>
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <x-secondary-button type="button" x-on:click="$dispatch('close')" class="justify-center">{{ __('Cancelar') }}</x-secondary-button>
            <x-secondary-button type="button" id="duplicado-conservar" class="justify-center">{{ __('Conservar ambos') }}</x-secondary-button>
            <x-primary-button type="button" id="duplicado-sobrescribir" class="justify-center">{{ __('Sobrescribir') }}</x-primary-button>
        </div>
    </div>
</x-modal>

<script>
    document.getElementById('archivo').addEventListener('click', function() {
        this.value = '';
    });

    document.getElementById('archivo').addEventListener('change', function() {
        var fichero = this.files[0];
        if (!fichero) return;

        if (fichero.size > Number(this.dataset.max)) {
            alert(@js(__('El archivo supera el límite de :mb MB.', ['mb' => config('rynkeep.tamano_maximo_mb')])));
            this.value = '';
            return;
        }

        var nombres = JSON.parse(this.dataset.nombres || '{}');
        var clave = fichero.name.toLowerCase();
        var esArchivo = (nombres.archivos || []).indexOf(clave) !== -1;
        var esCarpeta = (nombres.carpetas || []).indexOf(clave) !== -1;

        if (esArchivo || esCarpeta) {
            document.getElementById('texto-duplicado').textContent = @js(__('Ya existe «:nombre» en esta carpeta. ¿Qué quieres hacer?')).replace(':nombre', fichero.name);
            document.getElementById('duplicado-sobrescribir').style.display = esCarpeta ? 'none' : '';
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'archivo-duplicado' }));
            return;
        }

        enviarArchivo('');
    });

    function enviarArchivo(conflicto) {
        document.getElementById('conflicto').value = conflicto;
        document.getElementById('texto-nuevo').textContent = @js(__('Subiendo...'));
        document.getElementById('form-subida').submit();
    }

    document.getElementById('duplicado-conservar').addEventListener('click', function() {
        enviarArchivo('renombrar');
    });

    document.getElementById('duplicado-sobrescribir').addEventListener('click', function() {
        enviarArchivo('sobrescribir');
    });
</script>
