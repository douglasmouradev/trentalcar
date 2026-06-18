(() => {

  function appBaseUrl() {

    const meta = document.querySelector('meta[name="app-base-url"]');

    return meta?.getAttribute('content') || '';

  }



  window.APP_BASE_URL = appBaseUrl();



  const shell = document.getElementById('appShell');

  const sb = document.getElementById('sidebar');

  const toggle = document.getElementById('sidebarToggle');

  const bd = document.getElementById('sidebarBackdrop');



  const syncSidebar = () => {
    if (!shell || !sb) return;
    const open = sb.classList.contains('open');
    shell.classList.toggle('sidebar-open', open);
    if (bd) {
      bd.setAttribute('aria-hidden', open ? 'false' : 'true');
      bd.tabIndex = open ? 0 : -1;
    }
    if (toggle) {
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      const label = open ? toggle.dataset.labelClose : toggle.dataset.labelOpen;
      if (label) {
        toggle.setAttribute('aria-label', label);
        toggle.setAttribute('title', label);
      }
    }
  };

  if (sb && toggle) {
    toggle.addEventListener('click', () => {
      sb.classList.toggle('open');
      syncSidebar();
    });
    document.querySelectorAll('.sidebar .nav-link').forEach((link) => {
      link.addEventListener('click', () => {
        if (!window.matchMedia('(max-width: 960px)').matches) return;
        sb.classList.remove('open');
        syncSidebar();
      });
    });
  }

  if (bd && sb) {
    bd.addEventListener('click', () => {
      sb.classList.remove('open');
      syncSidebar();
    });
  }

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && sb?.classList.contains('open')) {
      sb.classList.remove('open');
      syncSidebar();
      toggle?.focus();
    }
  });



  window.addEventListener('resize', () => {

    if (window.matchMedia('(min-width: 961px)').matches && sb) {

      sb.classList.remove('open');

      syncSidebar();

    }

  });



  const searchToggle = document.getElementById('searchToggle');

  const searchWrap = document.getElementById('globalSearchWrap');

  if (searchToggle && searchWrap) {

    searchToggle.addEventListener('click', () => {

      const open = searchWrap.classList.toggle('is-open');

      searchToggle.setAttribute('aria-expanded', open ? 'true' : 'false');

      if (open) {

        searchWrap.querySelector('.global-search-input')?.focus();

      }

    });

  }



  const path = window.location.pathname.replace(/\/$/, '') || '/';

  let basePath = '';

  try {

    const bu = appBaseUrl();

    if (bu) basePath = new URL(bu, window.location.origin).pathname.replace(/\/$/, '');

  } catch (_) {

    /* ignore */

  }

  let rel = path;

  if (basePath && path.startsWith(basePath)) {

    rel = path.slice(basePath.length) || '/';

  }



  const navLinks = [...document.querySelectorAll('.sidebar .nav-link')];

  const candidates = [];

  navLinks.forEach((a) => {

    try {

      const u = new URL(a.getAttribute('href') || '', window.location.origin);

      let p = u.pathname.replace(/\/$/, '') || '/';

      if (basePath && p.startsWith(basePath)) {

        p = p.slice(basePath.length) || '/';

      }

      if (rel === p || (p !== '/' && rel.startsWith(`${p}/`))) {

        candidates.push({ a, len: p.length });

      }

    } catch (_) {

      /* ignore */

    }

  });

  candidates.sort((x, y) => y.len - x.len);

  if (candidates[0]) {

    candidates[0].a.classList.add('active');

  }



  document.querySelectorAll('.toast').forEach((t) => {

    const dismiss = t.querySelector('.toast-dismiss');

    const remove = () => {

      t.style.opacity = '0';

      t.style.transition = 'opacity .4s ease';

      setTimeout(() => t.remove(), 450);

    };

    dismiss?.addEventListener('click', remove);

    const isError = t.classList.contains('toast-error');

    if (!isError) {

      setTimeout(remove, 4500);

    }

  });



  document.querySelectorAll('form[data-confirm]').forEach((form) => {

    form.addEventListener('submit', (e) => {

      const msg = form.getAttribute('data-confirm') || '';

      if (msg && !window.confirm(msg)) {

        e.preventDefault();

      }

    });

  });



  const userToggle = document.getElementById('userMenuToggle');

  const userPanel = document.getElementById('userMenuPanel');

  if (userToggle && userPanel) {

    userToggle.addEventListener('click', () => {

      const open = userPanel.hasAttribute('hidden');

      if (open) {

        userPanel.removeAttribute('hidden');

        userToggle.setAttribute('aria-expanded', 'true');

      } else {

        userPanel.setAttribute('hidden', '');

        userToggle.setAttribute('aria-expanded', 'false');

      }

    });

    document.addEventListener('click', (e) => {

      if (!userPanel.contains(e.target) && e.target !== userToggle && !userToggle.contains(e.target)) {

        userPanel.setAttribute('hidden', '');

        userToggle.setAttribute('aria-expanded', 'false');

      }

    });

  }

})();

