document.addEventListener('DOMContentLoaded', () => {
    // --- Seleção de Elementos ---
    const profileButtons = document.querySelectorAll('.btn-profile');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const passwordToggleIcon = document.querySelector('#password')?.parentElement.querySelector('.view-icon');
    const confirmPasswordToggleIcon = document.querySelector('#confirm_password')?.parentElement.querySelector('.view-icon');
    const registrationForm = document.querySelector('form');

    // Caminhos dos ícones locais do projeto
    const ICON_VISIBLE = '/assets/icons-login/eye.png';
    const ICON_HIDDEN = '/assets/icons-login/eye-off.png';

    const perfilHiddenInput = document.getElementById('perfil');

    // --- 1. Seleção de Tipo de Conta ---
    profileButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            profileButtons.forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
            if (perfilHiddenInput) {
                perfilHiddenInput.value = btn.dataset.perfil || 'aluno';
            }
        });
    });

    // --- 2. Funcionalidade de Mostrar/Ocultar Senha ---
    const setupPasswordToggle = (input, icon) => {
        if (!input || !icon) return;

        input.type = 'password';
        icon.src = ICON_HIDDEN;
        icon.alt = 'Mostrar senha';

        icon.addEventListener('click', () => {
            const isVisible = input.type === 'text';

            if (isVisible) {
                input.type = 'password';
                icon.src = ICON_HIDDEN;
                icon.alt = 'Mostrar senha';
            } else {
                input.type = 'text';
                icon.src = ICON_VISIBLE;
                icon.alt = 'Ocultar senha';
            }
        });
    };

    setupPasswordToggle(passwordInput, passwordToggleIcon);
    setupPasswordToggle(confirmPasswordInput, confirmPasswordToggleIcon);

    // --- 3. Validação do Formulário ---
    registrationForm.addEventListener('submit', (e) => {
        let isValid = true;
        let errorMessage = '';

        // Validação de Senhas Iguais
        if (passwordInput.value !== confirmPasswordInput.value) {
            isValid = false;
            errorMessage = 'As senhas não coincidem.';
        }

        // Verificação de Tipo de Conta Selecionada
        const selectedProfile = document.querySelector('.btn-profile.selected');
        if (!selectedProfile) {
            isValid = false;
            errorMessage = 'Por favor, selecione um tipo de conta (Aluno ou Professor).';
        }

        if (!isValid) {
            e.preventDefault();
            alert(errorMessage);
        }
    });
});
