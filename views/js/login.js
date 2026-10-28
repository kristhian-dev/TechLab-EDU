document.addEventListener('DOMContentLoaded', () => {
    const profileButtons = document.querySelectorAll('.btn-profile');
    const passwordInput = document.getElementById('password');
    const togglePasswordIcon = document.querySelector('.view-icon');

    const ICON_VISIBLE = '/assets/icons-login/eye.png';
    const ICON_HIDDEN = '/assets/icons-login/eye-off.png';

    profileButtons.forEach((button) => {
        button.addEventListener('click', () => {
            profileButtons.forEach((item) => {
                const isSelected = item === button;
                item.classList.toggle('selected', isSelected);
                item.setAttribute('aria-pressed', String(isSelected));
            });

            // Envia o tipo de conta escolhido para o servidor validar no login.
            const perfilInput = document.getElementById('perfil');
            if (perfilInput) {
                perfilInput.value = button.dataset.perfil || '';
            }
        });
    });

    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', (event) => {
            const perfilInput = document.getElementById('perfil');
            const perfilSelecionado = perfilInput ? perfilInput.value.trim() : '';
            const algumSelecionado = Array.from(profileButtons).some((item) => item.classList.contains('selected'));

            if (!perfilSelecionado || !algumSelecionado) {
                event.preventDefault();
                alert('Selecione o tipo de conta (Aluno ou Professor) para entrar.');
            }
        });
    }

    if (togglePasswordIcon && passwordInput) {
        togglePasswordIcon.src = ICON_VISIBLE;
        togglePasswordIcon.alt = 'Mostrar senha';

        togglePasswordIcon.addEventListener('click', () => {
            const isPasswordVisible = passwordInput.type === 'text';

            passwordInput.type = isPasswordVisible ? 'password' : 'text';
            togglePasswordIcon.src = isPasswordVisible ? ICON_VISIBLE : ICON_HIDDEN;
            togglePasswordIcon.alt = isPasswordVisible ? 'Mostrar senha' : 'Ocultar senha';
        });
    }
});