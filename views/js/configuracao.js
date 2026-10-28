document.addEventListener('DOMContentLoaded', () => {
    const eyeOpenIcon = '../../assets/icons-login/eye.png';
    const eyeClosedIcon = '../../assets/icons-login/eye-off.png';

    document.querySelectorAll('.password-toggle').forEach((button) => {
        button.addEventListener('click', () => {
            const input = button.parentElement.querySelector('input');
            const icon = button.querySelector('img');
            const visible = input.type === 'text';

            input.type = visible ? 'password' : 'text';
            button.setAttribute('aria-label', visible ? 'Mostrar senha' : 'Ocultar senha');
            button.setAttribute('aria-pressed', String(!visible));
            icon.src = visible ? eyeOpenIcon : eyeClosedIcon;
            icon.alt = visible ? 'Mostrar senha' : 'Ocultar senha';
        });
    });

    const passwordForm = document.querySelector('#password-form');
    const status = document.querySelector('#password-status');
    passwordForm.addEventListener('submit', (event) => {
        event.preventDefault();
        status.textContent = 'Pronto para integração com o backend.';
    });
});
