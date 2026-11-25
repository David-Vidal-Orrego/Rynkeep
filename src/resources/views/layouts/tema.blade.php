<script>
    (function () {
        try {
            @auth
            var servidor = @json(\App\Support\Apariencia::de(auth()->user()));
            localStorage.setItem('rynkeep_tema', servidor.tema);
            localStorage.setItem('rynkeep_fuente', servidor.letra);
            localStorage.setItem('rynkeep_tamano', servidor.tamano);
            @endauth
            var tema = localStorage.getItem('rynkeep_tema') || 'sistema';
            var oscuro = tema === 'oscuro' ||
                (tema === 'sistema' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (oscuro) document.documentElement.classList.add('oscuro');
            document.documentElement.dataset.fuente = localStorage.getItem('rynkeep_fuente') || 'nunito';
            document.documentElement.dataset.tamano = localStorage.getItem('rynkeep_tamano') || 'normal';
        } catch (e) {}
    })();
</script>