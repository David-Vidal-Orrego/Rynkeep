<script>
    (function () {
        try {
            var tema = localStorage.getItem('rynkeep_tema') || 'sistema';
            var oscuro = tema === 'oscuro' ||
                (tema === 'sistema' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (oscuro) document.documentElement.classList.add('oscuro');
            document.documentElement.dataset.fuente = localStorage.getItem('rynkeep_fuente') || 'nunito';
        } catch (e) {}
    })();
</script>