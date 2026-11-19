<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-texto leading-tight">{{ __('Usuarios') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl sm:px-6 lg:px-8 space-y-6">

            <div>
                <a href="{{ route('admin.usuarios.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-primario border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primario-hover transition ease-in-out duration-150">
                    {{ __('Nuevo usuario') }}
                </a>
            </div>

            @if (session('status'))
                <p class="text-sm text-primario">{{ session('status') }}</p>
            @endif

            <div class="bg-superficie shadow-sm sm:rounded-lg divide-y divide-borde">
                @foreach ($usuarios as $usuario)
                    <div class="flex items-center justify-between px-6 py-3">
                        <div>
                            <p class="text-texto">
                                {{ $usuario->nombre }}
                                @if ($usuario->es_admin)
                                    <span class="text-xs text-primario">({{ __('administrador') }})</span>
                                @endif
                            </p>
                            <p class="text-sm text-suave">{{ $usuario->email }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <p class="text-sm text-suave">
                                {{ __(':usado de :limite', ['usado' => App\Support\Formato::bytes($usuario->espacio_usado), 'limite' => App\Support\Formato::bytes($usuario->espacio_limite)]) }}
                            </p>
                            <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="text-sm underline text-suave hover:text-texto">{{ __('Editar') }}</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
