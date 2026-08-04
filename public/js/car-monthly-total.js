(() => {
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
  const fmtPair = (usd) => `${fmtUsd(usd)} ≈ ${fmtBrl(usd * usdBrlRate)}`;

  const convertInputs = document.querySelectorAll('input[data-usd-convert]');
  const refreshField = (input) => {
    const out = input.parentElement?.querySelector('[data-usd-convert-out]');
    if (!out) return;
    out.textContent = fmtPair(parseVal(input));
  };

  convertInputs.forEach((input) => {
    input.addEventListener('input', () => refreshField(input));
    refreshField(input);
  });

  const out = document.getElementById('monthlyTotalLive');
  if (!out) return;

  const refreshTotal = () => {
    let total = 0;
    document.querySelectorAll('input[data-monthly-expense]').forEach((el) => {
      total += parseVal(el);
    });
    out.textContent = fmtPair(total);
  };

  document.querySelectorAll('input[data-monthly-expense]').forEach((el) => {
    el.addEventListener('input', refreshTotal);
  });
  refreshTotal();
})();
