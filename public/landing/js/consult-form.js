(() => {
  const form = document.getElementById('consult-form');
  if (!form) return;
  form.addEventListener('submit', () => {
    const btn = form.querySelector('button[type="submit"]');
    if (!btn || btn.classList.contains('is-loading')) return;
    const label = btn.textContent || '';
    const loading = form.dataset.labelSubmitting || label;
    btn.classList.add('is-loading');
    btn.disabled = true;
    btn.textContent = loading;
    btn.dataset.originalLabel = label;
  });
})();
