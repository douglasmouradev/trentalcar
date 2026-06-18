(() => {
  const input = document.getElementById('globalSearch');
  const box = document.getElementById('globalSearchResults');
  if (!input || !box) return;
  const base = document.querySelector('meta[name="app-base-url"]')?.content || '';
  let timer = null;

  function hide() {
    box.innerHTML = '';
    box.hidden = true;
  }

  function escapeHtml(v) {
    return String(v ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  async function search(q) {
    if (q.length < 2) {
      hide();
      return;
    }
    const res = await fetch(`${base}/api/search?q=${encodeURIComponent(q)}`);
    const json = await res.json();
    const rows = json.data || [];
    if (rows.length === 0) {
      box.innerHTML = `<div class="search-empty muted">${escapeHtml(window.__i18n?.searchNoResults || 'Sem resultados')}</div>`;
      box.hidden = false;
      return;
    }
    box.innerHTML = rows
      .map(
        (r) =>
          `<a class="search-item" href="${escapeHtml(r.url)}"><span class="search-type">${escapeHtml(r.type)}</span>${escapeHtml(r.label)}</a>`,
      )
      .join('');
    box.hidden = false;
  }

  input.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(() => search(input.value.trim()), 220);
  });
  input.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') hide();
  });
  document.addEventListener('click', (e) => {
    if (!box.contains(e.target) && e.target !== input) hide();
  });
})();
