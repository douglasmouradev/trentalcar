(() => {
  const root = document.getElementById('langSwitch');
  if (!root) return;

  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const appBase = document.querySelector('meta[name="app-base-url"]')?.getAttribute('content') || '';

  root.querySelectorAll('[data-lang]').forEach((a) => {
    a.addEventListener('click', (e) => {
      e.preventDefault();
      const lang = a.getAttribute('data-lang');
      if (!lang) return;

      if (csrf) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `${appBase}/locale`;
        const fields = {
          _csrf: csrf,
          lang,
          redirect: window.location.href,
        };
        Object.entries(fields).forEach(([name, value]) => {
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = name;
          input.value = value;
          form.appendChild(input);
        });
        document.body.appendChild(form);
        form.submit();
        return;
      }

      const u = new URL(window.location.href);
      u.searchParams.set('lang', lang);
      window.location.assign(u.toString());
    });
  });
})();
