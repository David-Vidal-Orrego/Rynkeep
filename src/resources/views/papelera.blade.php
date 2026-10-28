<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Papelera</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <p class="text-sm text-green-600">{{ session('status') }}</p>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg divide-y">
                @forelse ($archivos as $archivo)
                    <div class="flex items-center justify-between px-6 py-3">
                        <div>
                            <p class="text-gray-900">{{ $archivo->nombre_original }}</p>
                            <p class="text-sm text-gray-500">
                                {{ $archivo->tamanoLegible() }} · eliminado el {{ $archivo->papelera->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-4">
                            <form method="POST" action="{{ route('papelera.restaurar', $archivo->id) }}">
                                @csrf
                                <button type="submit" class="text-sm underline text-gray-600">Restaurar</button>
                            </form>

                            <form method="POST" action="{{ route('papelera.eliminar', $archivo->id) }}"
                                  onsubmit="return confirm('Se borrará para siempre. ¿Continuar?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm underline text-red-600">Eliminar definitivamente</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="px-6 py-4 text-gray-500">La papelera está vacía.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>