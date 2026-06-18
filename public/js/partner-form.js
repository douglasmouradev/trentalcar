(() => {
  document.querySelectorAll('.partner-car-check').forEach((cb) => {
    const sync = () => {
      const row = cb.closest('tr');
      const input = row?.querySelector('.quota-input');
      if (!input) return;
      input.disabled = !cb.checked;
      if (!cb.checked) {
        input.removeAttribute('required');
      }
    };
    cb.addEventListener('change', sync);
    sync();
  });
})();
