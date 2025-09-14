document.addEventListener('DOMContentLoaded', () => {
    const main = document.getElementById('main-content');

    // Fade in au chargement
    main.classList.add('fade-in');

    // Interception des liens internes pour fade-out
    document.querySelectorAll('a[href]').forEach(link => {
        const href = link.getAttribute('href');

        if (!href.startsWith('/') && !href.startsWith(window.location.origin))
            return;        

        link.addEventListener('click', (e) => {
            e.preventDefault();
            main.classList.remove('fade-in');
            main.classList.add('fade-out');

            setTimeout(() => {
                window.location.href = href;
            }, 400); // correspond à la durée CSS
        });
    });
});