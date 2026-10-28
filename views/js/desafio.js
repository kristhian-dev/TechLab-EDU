document.addEventListener('DOMContentLoaded', () => {
    const sidebarLinks = document.querySelectorAll('.sidebar a:not(.exit)');

    sidebarLinks.forEach(link => {
        link.addEventListener('click', event => {
            if (link.getAttribute('href') === '#') {
                event.preventDefault();
            }

            sidebarLinks.forEach(item => item.classList.remove('active'));
            link.classList.add('active');
        });
    });
});
