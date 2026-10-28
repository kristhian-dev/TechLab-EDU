const searchInput = document.querySelector('#student-search');
const rows = [...document.querySelectorAll('#ranking-rows tr')];

searchInput.addEventListener('input', () => {
    const query = searchInput.value.trim().toLocaleLowerCase('pt-BR');

    rows.forEach((row) => {
        row.hidden = query && !row.textContent.toLocaleLowerCase('pt-BR').includes(query);
    });
});
