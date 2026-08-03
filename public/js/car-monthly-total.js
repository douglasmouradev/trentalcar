(() => {
  const names = [
    'monthly_fuel',
    'monthly_toll',
    'monthly_wash',
    'monthly_maintenance',
    'monthly_extra',
    'monthly_insurance',
    'monthly_document',
    'monthly_ipva',
    'monthly_site_rent',
    'monthly_internet',
    'monthly_water',
    'monthly_electricity',
    'monthly_phone',
    'monthly_staff',
    'monthly_tag_annual',
  ];
  const out = document.getElementById('monthlyTotalLive');
  if (!out) return;

  const parseVal = (el) => {
    if (!el || el.value === '' || el.value == null) return 0;
    const n = parseFloat(String(el.value).trim().replace(',', '.'));
    if (!Number.isFinite(n)) return 0;
    return Math.max(0, n);
  };

  const refresh = () => {
    let total = 0;
    names.forEach((n) => {
      const el = document.querySelector(`input[name="${n}"]`);
      total += parseVal(el);
    });
    out.textContent = total.toLocaleString('pt-BR', {
      style: 'currency',
      currency: 'BRL',
    });
  };

  names.forEach((n) => {
    document.querySelector(`input[name="${n}"]`)?.addEventListener('input', refresh);
  });
  refresh();
})();
