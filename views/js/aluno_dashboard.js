document.addEventListener('DOMContentLoaded', () => {
    console.log('JS do Dashboard do Aluno carregado e funcionando!');
    // Search bar interaction
    const searchInput = document.querySelector('.search-box input');
    if (searchInput) {
        searchInput.addEventListener('focus', () => {
            searchInput.parentElement.classList.add('focused');
        });
        searchInput.addEventListener('blur', () => {
            searchInput.parentElement.classList.remove('focused');
        });
    }

    // Sidebar navigation
    const menuItems = document.querySelectorAll('.menu-item');
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            menuItems.forEach(i => i.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Challenge item clicks
    const challenges = document.querySelectorAll('.challenge-item');
    challenges.forEach(challenge => {
        challenge.addEventListener('click', () => {
            const title = challenge.querySelector('.challenge-title').textContent;
            console.log(`Starting challenge: ${title}`);
            // Logic to navigate to challenge would go here
        });
    });

    // Profile dropdown toggle
    const profileDropdown = document.getElementById('profileDropdown');
    if (profileDropdown) {
        profileDropdown.addEventListener('click', () => {
            console.log('Toggling user profile menu...');
            // Logic to show/hide dropdown would go here
        });
    }

    // View all links
    const viewAllLinks = document.querySelectorAll('.view-all');
    viewAllLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            // Apenas previne o comportamento de âncora "#" para links internos
            // Para links com href real (como ../desafios/index.php), o navegador segue naturalmente
            if (link.getAttribute('href') === '#') {
                e.preventDefault();
            }
            const section = link.parentElement.querySelector('h2').textContent;
            console.log(`Navigating to all ${section}...`);
        });
    });
});
