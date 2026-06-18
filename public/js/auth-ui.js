(() => {
  const btn = document.getElementById('togglePassword');
  const input = document.getElementById('login-password');
  if (!btn || !input) return;

  btn.addEventListener('click', () => {
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.setAttribute('aria-pressed', show ? 'true' : 'false');
    const showLabel = btn.dataset.showLabel || 'Show';
    const hideLabel = btn.dataset.hideLabel || 'Hide';
    btn.setAttribute('aria-label', show ? hideLabel : showLabel);
  });
})();
