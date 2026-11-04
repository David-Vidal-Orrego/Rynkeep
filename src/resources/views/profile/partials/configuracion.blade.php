<section>
    <header>
        <h2 class="text-lg font-medium text-texto">Apariencia</h2>
        <p class="mt-1 text-sm text-suave">
            Elige el tema y la letra.
        </p>
    </header>

    <div class="mt-6 space-y-6">
        <div>
            <x-input-label for="tema" value="Tema" />
            <select id="tema" class="mt-1 block w-full bg-superficie text-texto border-borde focus:border-primario focus:ring-primario rounded-md shadow-sm">
                <option value="sistema">Seguir el del sistema</option>
                <option value="claro">Claro</option>
                <option value="oscuro">Oscuro</option>
            </select>
        </div>

        <div>
            <x-input-label for="fuente" value="Letra" />
            <select id="fuente" class="mt-1 block w-full bg-superficie text-texto border-borde focus:border-primario focus:ring-primario rounded-md shadow-sm">
                <option value="nunito">Nunito</option>
                <option value="inter">Inter</option>
                <option value="atkinson">Atkinson Hyperlegible</option>
                <option value="comic">Comic</option>
                <option value="sistema">La del sistema</option>
            </select>
        </div>
    </div>

    <script>
        (function () {
            var selTema = document.getElementById('tema');
            var selFuente = document.getElementById('fuente');
            var html = document.documentElement;

            function aplicarTema(tema) {
                var oscuro = tema === 'oscuro' ||
                    (tema === 'sistema' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                html.classList.toggle('oscuro', oscuro);
            }

            selTema.value = localStorage.getItem('rynkeep_tema') || 'sistema';
            selFuente.value = localStorage.getItem('rynkeep_fuente') || 'nunito';

            selTema.addEventListener('change', function () {
                localStorage.setItem('rynkeep_tema', selTema.value);
                aplicarTema(selTema.value);
            });

            selFuente.addEventListener('change', function () {
                localStorage.setItem('rynkeep_fuente', selFuente.value);
                html.dataset.fuente = selFuente.value;
            });
        })();
    </script>
</section>