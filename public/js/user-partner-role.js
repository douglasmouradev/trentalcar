(() => {
  const role = document.getElementById('user-role-select');
  const wrap = document.getElementById('partner-cars-wrap');
  if (!role || !wrap) return;

  function sync() {
    wrap.hidden = role.value !== 'partner';
  }

  role.addEventListener('change', sync);
  sync();
})();
