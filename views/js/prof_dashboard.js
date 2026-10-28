document.addEventListener('DOMContentLoaded', () => {
    console.log('Dashboard do Professor inicializado');

    // Interação do Perfil
    const profileDropdown = document.getElementById('profileDropdown');
    if (profileDropdown) {
        profileDropdown.addEventListener('click', () => {
            console.log('Abertura do menu de perfil do professor');
        });
    }

    // Troca de estado ativo no menu lateral
    const menuItems = document.querySelectorAll('.menu-item');
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            menuItems.forEach(i => i.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Interação dos cards de atividades rápidas (links de navegação)
    const activityCards = document.querySelectorAll('a.activity-card');
    activityCards.forEach(card => {
        card.addEventListener('click', () => {
            const title = card.querySelector('strong').innerText;
            console.log(`Abrindo: ${title}`);
        });
    });
});
