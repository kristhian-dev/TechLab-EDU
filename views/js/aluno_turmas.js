document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('join-form');
  const input = document.getElementById('codigo');
  const button = document.getElementById('join-button');
  const feedback = document.getElementById('codigo-feedback');
  if (!form || !input || !button) return;
  const label = button.querySelector('.btn-label');
  function show(msg, type) {
    if (!feedback) return;
    feedback.textContent = msg;
    feedback.className = 'field-feedback' + (type ? ' ' + type : '');
  }
  form.addEventListener('submit', (e) => {
    const value = input.value.trim();
    if (value === '') {
      e.preventDefault();
      show('Digite o codigo da turma para continuar.', 'error');
      input.focus();
      return;
    }
    button.disabled = true;
    if (label) label.textContent = 'Entrando...';
    else button.textContent = 'Entrando...';
    show('', '');
  });
  input.addEventListener('input', () => { show('', ''); });
});
