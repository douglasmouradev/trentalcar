(function () {
  'use strict';

  var root = document.documentElement;
  var appOrigin = (root.getAttribute('data-app-origin') || '').replace(/\/$/, '');
  var devBase = (root.getAttribute('data-dev-login-base') || '').replace(/\/$/, '');

  document.querySelectorAll('[data-href-app]').forEach(function (el) {
    var path = el.getAttribute('data-href-app') || '/login';
    if (!path.startsWith('/')) {
      path = '/' + path;
    }
    var base = appOrigin || devBase;
    if (base) {
      el.setAttribute('href', base + path);
      return;
    }
    if (window.location.protocol === 'file:') {
      el.setAttribute('href', 'login.html');
      return;
    }
    el.setAttribute('href', path);
  });

  var nav = document.querySelector('[data-site-nav]');
  var toggle = document.querySelector('[data-nav-toggle]');
  if (nav && toggle) {
    toggle.addEventListener('click', function () {
      var open = nav.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    nav.querySelectorAll('a[href^="#"]').forEach(function (a) {
      a.addEventListener('click', function () {
        nav.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
      });
    });
  }

  document.querySelectorAll('.lp-filter').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var cat = btn.getAttribute('data-filter') || 'all';
      document.querySelectorAll('.lp-filter').forEach(function (b) {
        var active = b === btn;
        b.classList.toggle('is-active', active);
        b.setAttribute('aria-pressed', active ? 'true' : 'false');
      });
      var visible = 0;
      document.querySelectorAll('.lp-car').forEach(function (card) {
        var c = card.getAttribute('data-category') || '';
        if (cat === 'all' || cat === c) {
          card.classList.remove('is-hidden');
          visible += 1;
        } else {
          card.classList.add('is-hidden');
        }
      });
      var emptyMsg = document.getElementById('lp-fleet-filter-empty');
      if (emptyMsg) {
        emptyMsg.hidden = visible > 0;
      }
    });
  });

  var y = document.getElementById('lp-year');
  if (y) {
    y.textContent = String(new Date().getFullYear());
  }

  // Local de devolução diferente
  var sameReturn = document.querySelector('input[name="mesmo_local"]');
  var returnBox = document.getElementById('lp-return-location');
  if (sameReturn && returnBox) {
    var syncReturn = function () {
      var show = !sameReturn.checked;
      returnBox.classList.toggle('lp-return-location--visible', show);
      if (!show) {
        var input = returnBox.querySelector('input');
        if (input) {
          input.value = '';
        }
      }
    };
    sameReturn.addEventListener('change', syncReturn);
    syncReturn();
  }

  // Nome do hotel quando entrega no hotel
  var localSelect = document.getElementById('lead-local');
  var hotelBox = document.getElementById('lp-hotel-name');
  var hotelInput = document.getElementById('lead-hotel-nome');
  var formBusca = document.getElementById('form-busca');
  var hotelValue = (formBusca && formBusca.dataset.hotelValue) || 'Entrega no hotel';
  if (localSelect && hotelBox && hotelInput) {
    var syncHotel = function () {
      var show = localSelect.value === hotelValue;
      hotelBox.classList.toggle('lp-hotel-name--visible', show);
      hotelBox.hidden = !show;
      hotelInput.required = show;
      if (!show) {
        hotelInput.value = '';
      }
    };
    localSelect.addEventListener('change', syncHotel);
    syncHotel();
  }

  var header = document.getElementById('lp-header');
  if (header) {
    var onScroll = function () {
      header.classList.toggle('is-scrolled', window.scrollY > 12);
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  document.querySelectorAll('[data-car-id]').forEach(function (link) {
    link.addEventListener('click', function () {
      var id = link.getAttribute('data-car-id');
      var label = link.getAttribute('data-car-label') || link.closest('.lp-car')?.querySelector('h3')?.textContent || '';
      setSelectedCar(id, label.trim());
      document.querySelectorAll('.lp-car--selected').forEach(function (c) {
        c.classList.remove('lp-car--selected');
      });
      var card = link.closest('.lp-car');
      if (card) {
        card.classList.add('lp-car--selected');
      }
    });
  });

  function setSelectedCar(id, label) {
    var input = document.getElementById('lead-car-id');
    if (input && id) {
      input.value = id;
    }
    var banner = document.getElementById('lead-car-selected');
    if (!label) {
      if (banner) {
        banner.remove();
      }
      if (input) {
        input.value = '0';
      }
      return;
    }
    if (!banner) {
      banner = document.createElement('p');
      banner.className = 'lp-car-selected';
      banner.id = 'lead-car-selected';
      banner.setAttribute('role', 'status');
      var form = document.getElementById('form-busca');
      if (form) {
        form.parentNode.insertBefore(banner, form);
      }
    }
    var clearLabel = document.documentElement.lang.startsWith('en') ? 'Clear vehicle' : 'Remover veículo';
    banner.innerHTML =
      (document.documentElement.lang.startsWith('en') ? 'Selected vehicle: ' : 'Veículo selecionado: ') +
      '<strong></strong> <button type="button" class="lp-car-selected-clear" id="lead-car-clear" aria-label="' +
      clearLabel +
      '">&times;</button>';
    var strong = banner.querySelector('strong');
    if (strong) {
      strong.textContent = label;
    }
    var clearBtn = banner.querySelector('#lead-car-clear');
    if (clearBtn) {
      clearBtn.addEventListener('click', function () {
        setSelectedCar('', '');
        document.querySelectorAll('.lp-car--selected').forEach(function (c) {
          c.classList.remove('lp-car--selected');
        });
      });
    }
  }

  var existingClear = document.getElementById('lead-car-clear');
  if (existingClear) {
    existingClear.addEventListener('click', function () {
      setSelectedCar('', '');
      document.querySelectorAll('.lp-car--selected').forEach(function (c) {
        c.classList.remove('lp-car--selected');
      });
    });
  }

  if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (e) {
          if (e.isIntersecting) {
            e.target.classList.add('is-inview');
          }
        });
      },
      { rootMargin: '0px 0px -8% 0px', threshold: 0.08 }
    );
    document.querySelectorAll('[data-reveal]').forEach(function (el) {
      io.observe(el);
    });
  } else {
    document.querySelectorAll('[data-reveal]').forEach(function (el) {
      el.classList.add('is-inview');
    });
  }
})();
