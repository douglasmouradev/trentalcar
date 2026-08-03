(() => {
  const names = [
    'monthly_insurance',
    'monthly_document',
    'monthly_ipva',
    'monthly_wash',
    'monthly_site_rent',
    'monthly_internet',
    'monthly_water',
    'monthly_electricity',
    'monthly_phone',
    'monthly_staff',
    'monthly_tag_annual',
    'monthly_fuel',
    'monthly_toll',
    'monthly_maintenance',
    'monthly_extra',
  ];
  const out = document.getElementById('monthlyTotalLive');
  if (!out) return;

  const usdBrlRate = (() => {
    const raw = document.body?.dataset?.usdBrlRate;
    const n = raw ? parseFloat(raw) : NaN;
    return Number.isFinite(n) && n > 0 ? n : 5.5;
  })();

  const parseVal = (el) => {
    if (!el || el.value === '' || el.value == null) return 0;
    const n = parseFloat(String(el.value).trim().replace(',', '.'));
    if (!Number.isFinite(n)) return 0;
    return Math.max(0, n);
  };

  const fmtUsd = (n) =>
    n.toLocaleString('en-US', { style: 'currency', currency: 'USD' });
  const fmtBrl = (n) =>
    n.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

  const refresh = () => {
    let total = 0;
    names.forEach((n) => {
      const el = document.querySelector(`input[name="${n}"]`);
      total += parseVal(el);
    });
    const brl = total * usdBrlRate;
    out.textContent = `${fmtUsd(total)} ≈ ${fmtBrl(brl)}`;
  };

  names.forEach((n) => {
    document.querySelector(`input[name="${n}"]`)?.addEventListener('input', refresh);
  });
  refresh();
})();
