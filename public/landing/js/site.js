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

  // Local de devolução diferente + nome do hotel
  var sameReturn = document.querySelector('input[name="mesmo_local"]');
  var returnBox = document.getElementById('lp-return-location');
  var formBusca = document.getElementById('form-busca');
  var hotelValue = (formBusca && formBusca.dataset.hotelValue) || 'Entrega no hotel';
  var localSelect = document.getElementById('lead-local');
  var hotelBox = document.getElementById('lp-hotel-name');
  var hotelInput = document.getElementById('lead-hotel-nome');
  var returnSelect = document.getElementById('lead-local-devolucao');
  var hotelReturnBox = document.getElementById('lp-hotel-name-return');
  var hotelReturnInput = document.getElementById('lead-hotel-nome-devolucao');

  function syncHotelField(selectEl, boxEl, inputEl, forceHide) {
    if (!selectEl || !boxEl || !inputEl) return;
    var show = !forceHide && selectEl.value === hotelValue;
    boxEl.classList.toggle('lp-hotel-name--visible', show);
    inputEl.disabled = !show;
    inputEl.required = show;
    if (!show) {
      inputEl.value = '';
    }
  }

  if (sameReturn && returnBox) {
    var syncReturn = function () {
      var show = !sameReturn.checked;
      returnBox.classList.toggle('lp-return-location--visible', show);
      syncHotelField(returnSelect, hotelReturnBox, hotelReturnInput, !show);
      if (!show && returnSelect) {
        returnSelect.value = '';
      }
    };
    sameReturn.addEventListener('change', syncReturn);
    syncReturn();
  }

  if (localSelect) {
    localSelect.addEventListener('change', function () {
      syncHotelField(localSelect, hotelBox, hotelInput, false);
    });
    syncHotelField(localSelect, hotelBox, hotelInput, false);
  }

  if (returnSelect) {
    returnSelect.addEventListener('change', function () {
      var returnVisible = returnBox && returnBox.classList.contains('lp-return-location--visible');
      syncHotelField(returnSelect, hotelReturnBox, hotelReturnInput, !returnVisible);
    });
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
    link.addEventListener('click', function (ev) {
      var id = link.getAttribute('data-car-id');
      var label =
        link.getAttribute('data-car-label') ||
        (link.closest('.lp-car') && link.closest('.lp-car').querySelector('h3')
          ? link.closest('.lp-car').querySelector('h3').textContent
          : '') ||
        '';
      var rate = link.getAttribute('data-daily-rate') || '0';
      if (typeof window.setLeadCarSelection === 'function') {
        window.setLeadCarSelection(id, String(label).trim(), rate);
      } else {
        setSelectedCar(id, String(label).trim(), rate);
      }
      if (typeof window.syncLeadFilterToForm === 'function') {
        window.syncLeadFilterToForm();
      }
      document.querySelectorAll('.lp-car--selected').forEach(function (c) {
        c.classList.remove('lp-car--selected');
      });
      var card = link.closest('.lp-car');
      if (card) {
        card.classList.add('lp-car--selected');
      }
      var target = document.getElementById('reserva') || document.getElementById('form-busca');
      if (target) {
        ev.preventDefault();
        if (history.replaceState) {
          history.replaceState(null, '', '#reserva');
        } else {
          window.location.hash = 'reserva';
        }
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        var focusEl = document.getElementById('lead-local') || target.querySelector('input, select, textarea, button');
        if (focusEl && typeof focusEl.focus === 'function') {
          setTimeout(function () {
            try {
              focusEl.focus({ preventScroll: true });
            } catch (e) {
              focusEl.focus();
            }
          }, 350);
        }
      }
    });
  });

  function setSelectedCar(id, label, rate) {
    if (typeof window.setLeadCarSelection === 'function') {
      window.setLeadCarSelection(id, label, rate);
      return;
    }
    var input = document.getElementById('lead-car-id');
    var rateInput = document.getElementById('lead-car-rate');
    if (input) {
      input.value = label ? id || '0' : '0';
    }
    if (rateInput) {
      rateInput.value = label ? String(rate != null ? rate : '0') : '0';
      rateInput.setAttribute('data-car-label', label || '');
    }
    if (typeof window.refreshLeadSummary === 'function') {
      window.refreshLeadSummary();
    }
  }

  var existingClear = document.getElementById('lead-car-clear');
  if (existingClear && !existingClear.dataset.boundClear) {
    existingClear.dataset.boundClear = '1';
    existingClear.addEventListener('click', function () {
      setSelectedCar('', '', '0');
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
