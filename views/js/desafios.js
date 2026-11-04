document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-challenges');
    const filterTabs = document.querySelectorAll('.tab:not(.tab-add)');
    const challengesGrid = document.getElementById('challenges-grid');
    const paginationContainer = document.getElementById('pagination-container');
    const sortDropdown = document.querySelector('.sort-dropdown');
    const sidebarLinks = document.querySelectorAll('.sidebar-nav a:not(.exit)');
    
    const allChallenges = Array.from(document.querySelectorAll('.challenge-card')).map((card, index) => ({
        id: Number(card.dataset.id || 0),
        html: card.outerHTML,
        difficulty: card.getAttribute('data-difficulty'),
        status: (card.getAttribute('data-status') || '').toLowerCase(),
        createdAt: card.getAttribute('data-created-at') || '',
        title: card.querySelector('h3').textContent.toLowerCase(),
        description: card.querySelector('p').textContent.toLowerCase()
    }));

    let currentPage = 1;
    const cardsPerPage = 8;
    let currentFilter = 'all';
    let currentSearch = '';
    let currentSort = sortDropdown ? sortDropdown.value : 'Mais recentes';

    function sortChallenges(challenges) {
        const difficultyOrder = { facil: 1, medio: 2, dificil: 3 };

        return [...challenges].sort((first, second) => {
            if (currentSort === 'Mais difíceis' || currentSort === 'Mais fáceis') {
                const direction = currentSort === 'Mais difíceis' ? -1 : 1;
                const difficultyDifference = (difficultyOrder[first.difficulty] || 0) -
                    (difficultyOrder[second.difficulty] || 0);
                if (difficultyDifference !== 0) {
                    return difficultyDifference * direction;
                }
            }

            const firstDate = Date.parse(first.createdAt.replace(' ', 'T')) || 0;
            const secondDate = Date.parse(second.createdAt.replace(' ', 'T')) || 0;
            return secondDate - firstDate || second.id - first.id;
        });
    }

    function renderChallenges() {
        const statusFiltered = allChallenges.filter(challenge => {
            if (currentFilter === 'pendentes') {
                return ['disponivel', 'em_andamento'].includes(challenge.status);
            }
            if (currentFilter === 'concluidos') {
                return challenge.status === 'concluido';
            }
            return true;
        });
        const difficultyFiltered = statusFiltered.filter(challenge => {
            return currentFilter === 'all' ||
                currentFilter === 'pendentes' ||
                currentFilter === 'concluidos' ||
                challenge.difficulty === currentFilter;
        });
        const searched = difficultyFiltered.filter(challenge => {
            return challenge.title.includes(currentSearch) ||
                challenge.description.includes(currentSearch);
        });
        const sorted = sortChallenges(searched);

        const startIndex = (currentPage - 1) * cardsPerPage;
        const endIndex = startIndex + cardsPerPage;
        const paginatedItems = sorted.slice(startIndex, endIndex);

        const htmlContent = paginatedItems.map(challenge => challenge.html).join('');

        challengesGrid.innerHTML = htmlContent;
        updatePagination(sorted.length);
    }

    function updatePagination(totalItems) {
        const totalPages = Math.ceil(totalItems / cardsPerPage);
        paginationContainer.innerHTML = '';

        if (totalPages <= 1) return;

        const prevBtn = document.createElement('button');
        prevBtn.className = 'page-btn';
        prevBtn.textContent = '<';
        prevBtn.disabled = currentPage === 1;
        prevBtn.onclick = () => {
            if (currentPage > 1) {
                currentPage--;
                renderChallenges();
            }
        };
        paginationContainer.appendChild(prevBtn);

        for (let i = 1; i <= totalPages; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.className = `page-btn ${i === currentPage ? 'active' : ''}`;
            pageBtn.textContent = i;
            pageBtn.onclick = () => {
                currentPage = i;
                renderChallenges();
            };
            paginationContainer.appendChild(pageBtn);
        }

        const nextBtn = document.createElement('button');
        nextBtn.className = 'page-btn';
        nextBtn.textContent = '>';
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.onclick = () => {
            if (currentPage < totalPages) {
                currentPage++;
                renderChallenges();
            }
        };
        paginationContainer.appendChild(nextBtn);
    }

    searchInput.addEventListener('input', () => {
        currentSearch = searchInput.value.toLowerCase();
        currentPage = 1;
        renderChallenges();
    });

    filterTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            filterTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            currentFilter = tab.getAttribute('data-filter');
            currentPage = 1;
            renderChallenges();
        });
    });

    if (sortDropdown) {
        sortDropdown.addEventListener('change', () => {
            currentSort = sortDropdown.value;
            currentPage = 1;
            renderChallenges();
        });
    }

    sidebarLinks.forEach(link => {
        link.addEventListener('click', event => {
            if (link.getAttribute('href') === '#') {
                event.preventDefault();
            }

            sidebarLinks.forEach(item => item.classList.remove('active'));
            link.classList.add('active');
        });
    });

    renderChallenges();
});
