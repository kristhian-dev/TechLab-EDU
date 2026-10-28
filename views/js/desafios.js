document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-challenges');
    const filterTabs = document.querySelectorAll('.tab:not(.tab-add)');
    const challengesGrid = document.getElementById('challenges-grid');
    const paginationContainer = document.getElementById('pagination-container');
    const sidebarLinks = document.querySelectorAll('.sidebar-nav a:not(.exit)');
    
    const allChallenges = Array.from(document.querySelectorAll('.challenge-card')).map((card, index) => ({
        id: index,
        html: card.outerHTML,
        difficulty: card.getAttribute('data-difficulty'),
        title: card.querySelector('h3').textContent.toLowerCase(),
        description: card.querySelector('p').textContent.toLowerCase()
    }));

    let currentPage = 1;
    const cardsPerPage = 8;
    let currentFilter = 'all';
    let currentSearch = '';

    function renderChallenges() {
        const filtered = allChallenges.filter(c => {
            const matchesFilter = currentFilter === 'all' || 
                                  (currentFilter === 'pendentes' || currentFilter === 'concluidos') ||
                                  c.difficulty === currentFilter;
            const matchesSearch = c.title.includes(currentSearch) || c.description.includes(currentSearch);
            return matchesFilter && matchesSearch;
        });

        const startIndex = (currentPage - 1) * cardsPerPage;
        const endIndex = startIndex + cardsPerPage;
        const paginatedItems = filtered.slice(startIndex, endIndex);

        const htmlContent = paginatedItems.map((c, index) => {
            // Calcula o ID real baseado na lista filtrada ou na lista original
            const realId = allChallenges.findIndex(orig => orig.html === c.html);
            return c.html.replace("location.href='desafio.php'", `location.href='desafio.php?id=${realId}'`);
        }).join('');

        challengesGrid.innerHTML = htmlContent;
        updatePagination(filtered.length);
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
