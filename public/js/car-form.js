(() => {
  const picker = document.getElementById('color_picker');
  const hex = document.getElementById('color_hex');
  if (!picker || !hex) return;

  const normalize = (v) => {
    const s = String(v || '').trim();
    return /^#[0-9A-Fa-f]{6}$/.test(s) ? s : '#CCCCCC';
  };

  picker.addEventListener('input', () => {
    hex.value = picker.value.toUpperCase();
  });

  hex.addEventListener('input', () => {
    const v = normalize(hex.value);
    if (/^#[0-9A-Fa-f]{6}$/.test(v)) {
      picker.value = v;
    }
  });
})();
