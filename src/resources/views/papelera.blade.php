<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-texto leading-tight">{{ __('Papelera') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <p class="text-sm text-primario">{{ session('status') }}</p>
            @endif

            <div class="bg-superficie shadow-sm sm:rounded-lg divide-y divide-borde">
                @foreach ($carpetas as $carpeta)
                    @php $resumen = App\Support\Papelera::resumenCarpeta($carpeta); @endphp
                    <div class="flex items-center justify-between px-6 py-3">
                        <div>
                            <p class="text-texto">{{ __('Carpeta') }}: {{ $carpeta->nombre }}</p>
                            <p class="text-sm text-suave">
                                {{ __(':n archivos dentro', ['n' => $resumen['archivos']]) }} · {{ App\Support\Formato::bytes($resumen['tamano']) }} · {{ __('eliminado el :fecha', ['fecha' => $carpeta->papelera->format('d/m/Y H:i')]) }}
                                @if ($borrado = App\Support\Papelera::fechaDeBorrado($carpeta))
                                    · {{ __('se borrará el :fecha', ['fecha' => $borrado->format('d/m/Y')]) }}
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-4">
                            <form method="POST" action="{{ route('papelera.carpeta.restaurar', $carpeta->id) }}">
                                @csrf
                                <button type="submit" class="text-sm underline text-suave">{{ __('Restaurar') }}</button>
                            </form>

                            <form method="POST" action="{{ route('papelera.carpeta.eliminar', $carpeta->id) }}"
                                  onsubmit="return confirm(@js(__('Se borrará para siempre la carpeta y todo lo que contiene. ¿Continuar?')))">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm underline text-peligro">{{ __('Eliminar definitivamente') }}</button>
                            </form>
                        </div>
                    </div>
                @endforeach

                @forelse ($archivos as $archivo)
                    <div class="flex items-center justify-between px-6 py-3">
                        <div>
                            <p class="text-texto">{{ $archivo->nombre_original }}</p>
                            <p class="text-sm text-suave">
                                {{ $archivo->tamanoLegible() }} · {{ __('eliminado el :fecha', ['fecha' => $archivo->papelera->format('d/m/Y H:i')]) }}
                                @if ($borrado = App\Support\Papelera::fechaDeBorrado($archivo))
                                    · {{ __('se borrará el :fecha', ['fecha' => $borrado->format('d/m/Y')]) }}
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-4">
                            <form method="POST" action="{{ route('papelera.restaurar', $archivo->id) }}">
                                @csrf
                                <button type="submit" class="text-sm underline text-suave">{{ __('Restaurar') }}</button>
                            </form>

                            <form method="POST" action="{{ route('papelera.eliminar', $archivo->id) }}"
                                  onsubmit="return confirm(@js(__('Se borrará para siempre. ¿Continuar?')))">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm underline text-peligro">{{ __('Eliminar definitivamente') }}</button>
                            </form>
                        </div>
                    </div>
                @empty
                    @if ($carpetas->isEmpty())
                        <p class="px-6 py-4 text-suave">{{ __('La papelera está vacía.') }}</p>
                    @endif
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>