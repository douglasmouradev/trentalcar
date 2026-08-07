(() => {
  const boot = () => {
    const usdBrlRate = (() => {
      const raw = document.body && document.body.dataset
        ? document.body.dataset.usdBrlRate
        : null;
      const n = raw ? parseFloat(raw) : NaN;
      return Number.isFinite(n) && n > 0 ? n : 5.1;
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

    const refreshField = (input) => {
      if (!input || !input.matches || !input.matches('[data-usd-convert]')) return;
      const wrap = input.closest('.field') || input.parentElement;
      const out = wrap ? wrap.querySelector('[data-usd-convert-out]') : null;
      if (!out) return;
      out.textContent = fmtPair(parseVal(input));
    };

    const refreshTotal = () => {
      const out = document.getElementById('monthlyTotalLive');
      if (!out) return;
      let total = 0;
      document.querySelectorAll('input[data-monthly-expense], input[name^="monthly_"]').forEach((el) => {
        total += parseVal(el);
      });
      out.textContent = fmtPair(total);
    };

    const refreshAll = () => {
      document.querySelectorAll('input[data-usd-convert], #daily_rate').forEach(refreshField);
      refreshTotal();
    };

    document.addEventListener('input', (e) => {
      const t = e.target;
      if (!(t instanceof HTMLInputElement)) return;
      refreshField(t);
      if (
        t.matches('[data-monthly-expense]') ||
        (t.name && t.name.indexOf('monthly_') === 0)
      ) {
        refreshTotal();
      }
    });
    document.addEventListener('change', (e) => {
      const t = e.target;
      if (!(t instanceof HTMLInputElement)) return;
      refreshField(t);
      if (
        t.matches('[data-monthly-expense]') ||
        (t.name && t.name.indexOf('monthly_') === 0)
      ) {
        refreshTotal();
      }
    });

    refreshAll();
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
