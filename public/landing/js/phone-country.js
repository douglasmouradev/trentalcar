(function () {
  'use strict';

  function digitsOnly(value) {
    return String(value || '').replace(/\D+/g, '');
  }

  function applyMask(value, mask) {
    var digits = digitsOnly(value);
    if (!mask) return digits;
    var out = '';
    var di = 0;
    for (var i = 0; i < mask.length && di < digits.length; i++) {
      if (mask[i] === '0') {
        out += digits[di++];
      } else {
        out += mask[i];
      }
    }
    return out;
  }

  function initWidget(root) {
    var hidden = root.querySelector('#lead-phone-country') || root.querySelector('input[name="phone_country"]');
    var btn = root.querySelector('#lead-phone-flag-btn');
    var flagEl = root.querySelector('#lead-phone-flag');
    var dialEl = root.querySelector('#lead-phone-dial');
    var menu = root.querySelector('#lead-phone-menu');
    var list = root.querySelector('#lead-phone-list');
    var search = root.querySelector('#lead-phone-search');
    var empty = root.querySelector('#lead-phone-empty');
    var input = root.querySelector('#lead-telefone') || root.querySelector('input[name="telefone"]');
    if (!hidden || !btn || !menu || !list || !input) return;

    var options = Array.prototype.slice.call(list.querySelectorAll('.lp-phone-option'));

    function setOpen(open) {
      menu.hidden = !open;
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      root.classList.toggle('is-open', open);
      if (open && search) {
        search.value = '';
        filterOptions('');
        setTimeout(function () {
          search.focus();
        }, 0);
      }
    }

    function filterOptions(query) {
      var q = String(query || '').trim().toLowerCase();
      var visible = 0;
      options.forEach(function (opt) {
        var hay = opt.getAttribute('data-search') || '';
        var show = !q || hay.indexOf(q) !== -1;
        opt.hidden = !show;
        if (show) visible++;
      });
      if (empty) empty.hidden = visible > 0;
    }

    function selectOption(opt, remask) {
      if (!opt) return;
      options.forEach(function (o) {
        o.classList.toggle('is-selected', o === opt);
        o.setAttribute('aria-selected', o === opt ? 'true' : 'false');
      });
      hidden.value = opt.getAttribute('data-iso') || '';
      if (flagEl) flagEl.textContent = opt.getAttribute('data-flag') || '';
      if (dialEl) dialEl.textContent = '+' + (opt.getAttribute('data-dial') || '');
      var mask = opt.getAttribute('data-mask') || '';
      input.setAttribute('data-mask', mask);
      input.placeholder = mask;
      if (remask !== false) {
        input.value = applyMask(input.value, mask);
      }
      setOpen(false);
      input.focus();
    }

    function currentMask() {
      return input.getAttribute('data-mask') || '';
    }

    btn.addEventListener('click', function (ev) {
      ev.preventDefault();
      setOpen(menu.hidden);
    });

    if (search) {
      search.addEventListener('input', function () {
        filterOptions(search.value);
      });
      search.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape') {
          setOpen(false);
          btn.focus();
        }
      });
    }

    list.addEventListener('click', function (ev) {
      var opt = ev.target.closest('.lp-phone-option');
      if (!opt || opt.hidden) return;
      selectOption(opt, true);
    });

    input.addEventListener('input', function () {
      var masked = applyMask(input.value, currentMask());
      if (input.value !== masked) {
        input.value = masked;
      }
    });

    document.addEventListener('click', function (ev) {
      if (!root.contains(ev.target)) {
        setOpen(false);
      }
    });

    document.addEventListener('keydown', function (ev) {
      if (ev.key === 'Escape' && !menu.hidden) {
        setOpen(false);
      }
    });

    // Apply mask to existing value on load.
    if (input.value) {
      input.value = applyMask(input.value, currentMask());
    }
  }

  document.querySelectorAll('[data-phone-widget]').forEach(initWidget);
})();
