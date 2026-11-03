<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $carpetaActual?->nombre ?? 'Mis archivos' }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if ($carpetaActual)
            <nav class="text-sm text-gray-600 flex flex-wrap items-center gap-1">
                <a href="{{ route('dashboard') }}" class="underline">Mis archivos</a>
                @foreach ($ruta as $paso)
                <span>/</span>
                @if ($loop->last)
                <span class="text-gray-900 font-medium">{{ $paso->nombre }}</span>
                @else
                <a href="{{ route('dashboard', $paso->id) }}" class="underline">{{ $paso->nombre }}</a>
                @endif
                @endforeach
            </nav>
            @endif

            <form method="POST" action="{{ route('carpetas.store') }}" class="flex items-center gap-3">
                @csrf
                <input type="hidden" name="carpeta_padre_id" value="{{ $carpetaActual?->id }}">
                <x-text-input name="nombre" type="text" placeholder="Nueva carpeta" required />
                <x-primary-button>Crear carpeta</x-primary-button>
            </form>
            <x-input-error :messages="$errors->get('nombre')" />

            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <div class="flex justify-between text-sm text-gray-600 mb-2">
                    <span>Espacio usado</span>
                    <span>{{ App\Support\Formato::bytes($usuario->espacio_usado) }} de {{ App\Support\Formato::bytes($usuario->espacio_limite) }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="{{ $usuario->porcentajeUsado() >= 90 ? 'bg-red-600' : 'bg-indigo-600' }} h-2.5 rounded-full"
                        style="width: {{ $usuario->porcentajeUsado() }}%"></div>
                </div>
            </div>

            <form method="POST" action="{{ route('archivos.store') }}" enctype="multipart/form-data" class="flex items-center gap-3">
                @csrf
                <input type="hidden" name="carpeta_id" value="{{ $carpetaActual?->id }}">
                <input type="file" name="archivo" required>
                <x-primary-button>Subir</x-primary-button>
            </form>
            <x-input-error :messages="$errors->get('archivo')" />

            @if (session('status'))
            <p class="text-sm text-green-600">{{ session('status') }}</p>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg divide-y">
                @foreach ($carpetas as $carpeta)
                <a href="{{ route('dashboard', $carpeta->id) }}" class="block px-6 py-3 hover:bg-gray-50">
                    {{ $carpeta->nombre }}
                </a>
                @endforeach
            </div>


            <div class="bg-white shadow-sm sm:rounded-lg divide-y">
                @forelse ($archivos as $archivo)
                <div class="flex items-center justify-between px-6 py-3">
                    <div>
                        <p class="text-gray-900">{{ $archivo->nombre_original }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $archivo->tamanoLegible() }} · {{ $archivo->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-4">
                        <a class="text-sm underline text-gray-600"
                            href="{{ route('archivos.descargar', $archivo) }}">Descargar</a>

                        <form method="POST" action="{{ route('archivos.destroy', $archivo) }}"
                            onsubmit="return confirm('¿Enviar este archivo a la papelera?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm underline text-red-600">Eliminar</button>
                        </form>
                    </div>
                </div>
                @empty
                <p class="px-6 py-4 text-gray-500">Todavía no has subido ningún archivo.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>