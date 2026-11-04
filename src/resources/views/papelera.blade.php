<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl texto-texto leading-tight">Papelera</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <p class="text-sm text-green-600">{{ session('status') }}</p>
            @endif

            <div class="bg-superficie shadow-sm sm:rounded-lg divide-y divide-borde">
                @forelse ($archivos as $archivo)
                    <div class="flex items-center justify-between px-6 py-3">
                        <div>
                            <p class="text-texto">{{ $archivo->nombre_original }}</p>
                            <p class="text-sm text-suave">
                                {{ $archivo->tamanoLegible() }} · eliminado el {{ $archivo->papelera->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-4">
                            <form method="POST" action="{{ route('papelera.restaurar', $archivo->id) }}">
                                @csrf
                                <button type="submit" class="text-sm underline text-suave">Restaurar</button>
                            </form>

                            <form method="POST" action="{{ route('papelera.eliminar', $archivo->id) }}"
                                  onsubmit="return confirm('Se borrará para siempre. ¿Continuar?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm underline text-peligro">Eliminar definitivamente</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="px-6 py-4 text-suave">La papelera está vacía.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>