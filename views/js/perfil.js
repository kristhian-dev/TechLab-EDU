document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.profile-tabs [role="tab"]');
    const navigationItems = document.querySelectorAll('.profile-sidebar nav a');
    const detailsGrid = document.querySelector('.profile-details-grid');
    const personalCard = document.querySelector('.profile-personal-card');
    const sideCards = document.querySelector('.profile-side-cards');
    const progressPanel = document.querySelector('[data-panel="progress"]');

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            tabs.forEach((item) => {
                const isActive = item === tab;
                item.classList.toggle('active', isActive);
                item.setAttribute('aria-selected', String(isActive));

            });

            const showProgress = tab.dataset.tab === 'progress';
            personalCard.hidden = showProgress;
            sideCards.hidden = showProgress;
            progressPanel.hidden = !showProgress;
            detailsGrid.classList.toggle('is-progress', showProgress);
            detailsGrid.setAttribute('aria-label', showProgress ? 'Progresso do aluno' : 'Informações pessoais');
        });
    });

    navigationItems.forEach((item) => {
        item.addEventListener('click', (event) => {
            // O link "Sair" precisa navegar de verdade até logout.php; os
            // demais itens do menu são só destaque visual (href="#").
            if (item.classList.contains('profile-logout')) {
                return;
            }

            if (item.getAttribute('href') === '#') {
                event.preventDefault();
            }

            navigationItems.forEach((navigationItem) => {
                navigationItem.classList.remove('active');
                navigationItem.removeAttribute('aria-current');
            });

            item.classList.add('active');
            item.setAttribute('aria-current', 'page');
        });
    });

    // ------------------------------------------------------------------
    // Alteração da foto de perfil: abre o seletor de arquivos do device,
    // mostra a prévia no modal e só envia após confirmação do usuário.
    // A validação de tipo/tamanho de verdade é feita no servidor.
    // ------------------------------------------------------------------
    const photoForm = document.getElementById('profile-photo-form');
    const photoInput = document.getElementById('profile-photo-input');
    const photoModal = document.getElementById('profile-photo-modal');
    const photoPreview = document.getElementById('profile-photo-modal-preview');
    const photoError = document.getElementById('profile-photo-modal-error');
    const photoCancel = document.getElementById('profile-photo-cancel');

    if (photoForm && photoInput && photoModal) {
        const TAMANHO_MAXIMO = 5 * 1024 * 1024; // 5 MB, espelhando o limite do servidor
        const FORMATOS_ACEITOS = ['image/jpeg', 'image/png', 'image/webp'];
        let objectUrl = null;

        const fecharModal = () => {
            photoModal.hidden = true;
            photoError.hidden = true;
            photoError.textContent = '';
            photoInput.value = '';

            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }
        };

        document.querySelectorAll('[data-change-photo]').forEach((botao) => {
            botao.addEventListener('click', () => {
                photoInput.click();
            });
        });

        photoInput.addEventListener('change', () => {
            const arquivo = photoInput.files && photoInput.files[0];
            if (!arquivo) {
                return;
            }

            if (!FORMATOS_ACEITOS.includes(arquivo.type)) {
                photoInput.value = '';
                window.alert('Formato não suportado. Escolha uma imagem JPG, JPEG, PNG ou WebP.');
                return;
            }

            if (arquivo.size > TAMANHO_MAXIMO) {
                photoInput.value = '';
                window.alert('A imagem excede o limite de 5 MB.');
                return;
            }

            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
            }

            objectUrl = URL.createObjectURL(arquivo);
            photoPreview.src = objectUrl;
            photoError.hidden = true;
            photoModal.hidden = false;
        });

        if (photoCancel) {
            photoCancel.addEventListener('click', fecharModal);
        }

        photoModal.addEventListener('click', (event) => {
            if (event.target === photoModal) {
                fecharModal();
            }
        });

        const photoConfirm = document.getElementById('profile-photo-confirm');

        if (photoConfirm) {
            photoConfirm.addEventListener('click', (event) => {
                // So valida: se tem arquivo, deixa o submit nativo enviar.
                // Nao chama preventDefault nem requestSubmit aqui — era isso
                // que travava a tela no "Enviando..." sem postar a foto.
                if (!photoInput.files || !photoInput.files[0]) {
                    event.preventDefault();
                    photoError.textContent = 'Escolha uma imagem antes de confirmar.';
                    photoError.hidden = false;
                }
            });
        }

        let enviandoFoto = false;
        photoForm.addEventListener('submit', (event) => {
            if (enviandoFoto) {
                event.preventDefault();
                return;
            }
            enviandoFoto = true;
            if (photoConfirm) {
                photoConfirm.textContent = 'Enviando...';
            }
        });
    }
});
