<section>
    <header>
        <h2 class="text-lg font-medium text-texto">{{ __('Apariencia') }}</h2>
        <p class="mt-1 text-sm text-suave">
            {{ __('Elige el tema y la letra.') }}
        </p>
    </header>

    <div class="mt-6 space-y-6">
        <div>
            <x-input-label for="tema" :value="__('Tema')" />
            <select id="tema" class="mt-1 block w-full bg-superficie text-texto border-borde focus:border-primario focus:ring-primario rounded-md shadow-sm">
                <option value="sistema">{{ __('Seguir el del sistema') }}</option>
                <option value="claro">{{ __('Claro') }}</option>
                <option value="oscuro">{{ __('Oscuro') }}</option>
            </select>
        </div>

        <form method="POST" action="{{ route('profile.idioma') }}">
            @csrf
            @method('PATCH')
            <x-input-label for="idioma" :value="__('Idioma')" />
            <select id="idioma" name="idioma" onchange="this.form.submit()" class="mt-1 block w-full bg-superficie text-texto border-borde focus:border-primario focus:ring-primario rounded-md shadow-sm">
                <option value="">{{ __('Idioma del sistema') }}</option>
                @foreach (config('rynkeep.idiomas') as $codigo => $nombre)
                    <option value="{{ $codigo }}" @selected(auth()->user()->idioma === $codigo)>{{ $nombre }}</option>
                @endforeach
            </select>
        </form>

        <div>
            <x-input-label for="fuente" :value="__('Letra')" />
            <select id="fuente" class="mt-1 block w-full bg-superficie text-texto border-borde focus:border-primario focus:ring-primario rounded-md shadow-sm">
                <option value="nunito">Nunito</option>
                <option value="inter">Inter</option>
                <option value="atkinson">Atkinson Hyperlegible</option>
                <option value="comic">Comic</option>
                <option value="sistema">{{ __('La del sistema') }}</option>
            </select>
        </div>

        <div>
            <x-input-label for="tamano" :value="__('Tamaño de letra')" />
            <select id="tamano" class="mt-1 block w-full bg-superficie text-texto border-borde focus:border-primario focus:ring-primario rounded-md shadow-sm">
                <option value="normal">{{ __('Normal') }}</option>
                <option value="grande">{{ __('Grande') }}</option>
                <option value="muy_grande">{{ __('Muy grande') }}</option>
            </select>
        </div>
    </div>

    <script>
        (function () {
            var selTema = document.getElementById('tema');
            var selFuente = document.getElementById('fuente');
            var selTamano = document.getElementById('tamano');
            var html = document.documentElement;

            function aplicarTema(tema) {
                var oscuro = tema === 'oscuro' ||
                    (tema === 'sistema' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                html.classList.toggle('oscuro', oscuro);
            }

            function guardar(campos) {
                fetch(@js(route('profile.apariencia')), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': @js(csrf_token())
                    },
                    body: JSON.stringify(campos)
                }).catch(function () {});
            }

            selTema.value = localStorage.getItem('rynkeep_tema') || 'sistema';
            selFuente.value = localStorage.getItem('rynkeep_fuente') || 'nunito';
            selTamano.value = localStorage.getItem('rynkeep_tamano') || 'normal';

            selTema.addEventListener('change', function () {
                localStorage.setItem('rynkeep_tema', selTema.value);
                aplicarTema(selTema.value);
                guardar({ tema: selTema.value });
            });

            selFuente.addEventListener('change', function () {
                localStorage.setItem('rynkeep_fuente', selFuente.value);
                html.dataset.fuente = selFuente.value;
                guardar({ letra: selFuente.value });
            });

            selTamano.addEventListener('change', function () {
                localStorage.setItem('rynkeep_tamano', selTamano.value);
                html.dataset.tamano = selTamano.value;
                guardar({ tamano: selTamano.value });
            });
        })();
    </script>
</section>