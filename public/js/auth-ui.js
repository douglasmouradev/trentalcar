(() => {
  const btn = document.getElementById('togglePassword');
  const input = document.getElementById('login-password');
  const form = document.querySelector('form.auth-form, form[action*="login"]');
  if (btn && input) {
    btn.addEventListener('click', () => {
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.setAttribute('aria-pressed', show ? 'true' : 'false');
      const showLabel = btn.dataset.showLabel || 'Show';
      const hideLabel = btn.dataset.hideLabel || 'Hide';
      btn.setAttribute('aria-label', show ? hideLabel : showLabel);
    });
  }
  if (form) {
    form.addEventListener('submit', () => {
      const submit = form.querySelector('button[type="submit"]');
      if (!submit || submit.classList.contains('is-loading')) return;
      submit.classList.add('is-loading');
      submit.disabled = true;
    });
  }
})();
