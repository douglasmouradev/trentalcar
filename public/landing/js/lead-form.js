(function () {
  'use strict';

  var DRAFT_KEY = 'titanium_lead_draft';
  var form = document.getElementById('form-busca');
  if (!form) return;

  var i18n = {
    dateOrder: form.dataset.errorDateOrder || 'Return date must be on or after pick-up.',
    dateRequired: form.dataset.errorDateRequired || 'Select pick-up and return dates.',
    hotelRequired: form.dataset.errorHotel || 'Enter the hotel name for delivery.',
    localRequired: form.dataset.errorLocal || 'Select a pick-up location.',
    submitting: form.dataset.labelSubmitting || 'Sending…',
    confirm: form.dataset.labelConfirm || 'Confirm request',
    submit: form.dataset.labelSubmit || 'Search vehicles',
    daysTpl: form.dataset.summaryDays || ':count day(s)',
    needDates: form.dataset.summaryNeedDates || 'Enter the dates',
  };
  var searchUrl = form.dataset.searchUrl || '/reservar';

  var inicio = form.querySelector('input[name="inicio"]');
  var fim = form.querySelector('input[name="fim"]');
  var localSelect = form.querySelector('#lead-local');
  var hotelInput = form.querySelector('#lead-hotel-nome');
  var hotelBox = form.querySelector('#lp-hotel-name');
  var returnSelect = form.querySelector('#lead-local-devolucao');
  var hotelReturnInput = form.querySelector('#lead-hotel-nome-devolucao');
  var hotelReturnBox = form.querySelector('#lp-hotel-name-return');
  var sameReturn = form.querySelector('input[name="mesmo_local"]');
  var returnBox = document.getElementById('lp-return-location');
  var hotelValue = form.dataset.hotelValue || 'Entrega no hotel';
  var submitBtn = document.getElementById('lead-submit-btn') || form.querySelector('button[type="submit"]');
  var errorBox = document.getElementById('lead-form-errors');
  var carIdInput = document.getElementById('lead-car-id');
  var carRateInput = document.getElementById('lead-car-rate');
  var summary = document.getElementById('lead-summary');
  var summaryCar = document.getElementById('lead-summary-car');
  var summaryDaily = document.getElementById('lead-summary-daily');
  var summaryDays = document.getElementById('lead-summary-days');
  var summaryTotal = document.getElementById('lead-summary-total');
  var clearBtn = document.getElementById('lead-car-clear');
  var filterForm = document.getElementById('booking-date-filter');

  var today = new Date();
  var minStr = today.toISOString().slice(0, 10);
  if (inicio) inicio.min = minStr;
  if (fim) fim.min = minStr;

  function usdBrlRate() {
    var raw = document.body && document.body.dataset ? document.body.dataset.usdBrlRate : null;
    var n = raw ? parseFloat(raw) : NaN;
    return Number.isFinite(n) && n > 0 ? n : 5.5;
  }

  function fmtPair(usd) {
    var rate = usdBrlRate();
    var u = Number(usd) || 0;
    var usdStr = u.toLocaleString('en-US', { style: 'currency', currency: 'USD' });
    var brlStr = (u * rate).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    return usdStr + ' ≈ ' + brlStr;
  }

  function daysInclusive(a, b) {
    if (!a || !b) return 0;
    var d1 = new Date(a + 'T00:00:00');
    var d2 = new Date(b + 'T00:00:00');
    if (Number.isNaN(d1.getTime()) || Number.isNaN(d2.getTime()) || d2 < d1) return 0;
    return Math.max(1, Math.round((d2 - d1) / 86400000) + 1);
  }

  function selectedRate() {
    if (!carRateInput) return 0;
    var n = parseFloat(String(carRateInput.value || '0').replace(',', '.'));
    return Number.isFinite(n) && n > 0 ? n : Math.max(0, n || 0);
  }

  function selectedLabel() {
    return carRateInput ? String(carRateInput.getAttribute('data-car-label') || '').trim() : '';
  }

  function hasCar() {
    return carIdInput && parseInt(carIdInput.value || '0', 10) > 0 && selectedLabel() !== '';
  }

  function fieldValue(selector) {
    var el = form.querySelector(selector);
    if (!el) return '';
    if (el.type === 'checkbox') return el.checked ? '1' : '0';
    return String(el.value || '').trim();
  }

  function setFieldValue(selector, value) {
    var el = form.querySelector(selector);
    if (!el || value == null || value === '') return;
    if (el.type === 'checkbox') {
      el.checked = value === '1' || value === true;
      return;
    }
    if (el.value) return;
    el.value = value;
  }

  function setFieldValueForce(selector, value) {
    var el = form.querySelector(selector);
    if (!el || value == null || value === '') return;
    if (el.type === 'checkbox') {
      el.checked = value === '1' || value === true;
      return;
    }
    el.value = value;
  }

  function saveDraft() {
    try {
      var draft = {
        nome: fieldValue('input[name="nome"]'),
        email: fieldValue('input[name="email"]'),
        telefone: fieldValue('input[name="telefone"]'),
        phone_country: fieldValue('#lead-phone-country') || fieldValue('input[name="phone_country"]'),
        local: fieldValue('#lead-local'),
        hotel_nome: fieldValue('#lead-hotel-nome'),
        inicio: fieldValue('input[name="inicio"]'),
        fim: fieldValue('input[name="fim"]'),
        mesmo_local: fieldValue('input[name="mesmo_local"]'),
        local_devolucao: fieldValue('#lead-local-devolucao'),
        hotel_nome_devolucao: fieldValue('#lead-hotel-nome-devolucao'),
      };
      sessionStorage.setItem(DRAFT_KEY, JSON.stringify(draft));
    } catch (e) {
      /* ignore */
    }
  }

  function restoreDraft() {
    try {
      var raw = sessionStorage.getItem(DRAFT_KEY);
      if (!raw) return;
      var draft = JSON.parse(raw);
      if (!draft || typeof draft !== 'object') return;
      setFieldValue('input[name="nome"]', draft.nome);
      setFieldValue('input[name="email"]', draft.email);
      setFieldValue('input[name="telefone"]', draft.telefone);
      setFieldValue('#lead-phone-country', draft.phone_country);
      setFieldValue('input[name="phone_country"]', draft.phone_country);
      if (draft.phone_country) {
        var opt = form.querySelector('.lp-phone-option[data-iso="' + draft.phone_country + '"]');
        if (opt) {
          var flagEl = form.querySelector('#lead-phone-flag');
          var dialEl = form.querySelector('#lead-phone-dial');
          var phoneInput = form.querySelector('#lead-telefone');
          if (flagEl) flagEl.textContent = opt.getAttribute('data-flag') || '';
          if (dialEl) dialEl.textContent = '+' + (opt.getAttribute('data-dial') || '');
          if (phoneInput) {
            var mask = opt.getAttribute('data-mask') || '';
            phoneInput.setAttribute('data-mask', mask);
            phoneInput.placeholder = mask;
          }
          form.querySelectorAll('.lp-phone-option').forEach(function (o) {
            o.classList.toggle('is-selected', o === opt);
          });
        }
      }
      setFieldValue('#lead-local', draft.local);
      setFieldValue('#lead-hotel-nome', draft.hotel_nome);
      setFieldValue('input[name="inicio"]', draft.inicio);
      setFieldValue('input[name="fim"]', draft.fim);
      if (draft.mesmo_local != null) {
        setFieldValueForce('input[name="mesmo_local"]', draft.mesmo_local);
      }
      setFieldValue('#lead-local-devolucao', draft.local_devolucao);
      setFieldValue('#lead-hotel-nome-devolucao', draft.hotel_nome_devolucao);
    } catch (e) {
      /* ignore */
    }
  }

  function clearDraft() {
    try {
      sessionStorage.removeItem(DRAFT_KEY);
    } catch (e) {
      /* ignore */
    }
  }

  function syncFilterToForm(force) {
    if (!filterForm) return;
    var setVal = force ? setFieldValueForce : setFieldValue;
    var fi = filterForm.querySelector('input[name="inicio"]');
    var ff = filterForm.querySelector('input[name="fim"]');
    var fl = filterForm.querySelector('select[name="local"]');
    var fh = filterForm.querySelector('input[name="hotel_nome"]');
    if (fi && fi.value) setVal('input[name="inicio"]', fi.value);
    if (ff && ff.value) setVal('input[name="fim"]', ff.value);
    if (fl && fl.value) setVal('#lead-local', fl.value);
    if (fh && fh.value && !fh.disabled) setVal('#lead-hotel-nome', fh.value);
    if (inicio && fim && inicio.value) {
      fim.min = inicio.value;
    }
    syncHotelField(localSelect, hotelBox, hotelInput, false);
    refreshLeadSummary();
  }

  function syncFormToFilter() {
    if (!filterForm) return;
    var fi = filterForm.querySelector('input[name="inicio"]');
    var ff = filterForm.querySelector('input[name="fim"]');
    var fl = filterForm.querySelector('select[name="local"]');
    var fh = filterForm.querySelector('input[name="hotel_nome"]');
    if (fi && inicio && inicio.value) fi.value = inicio.value;
    if (ff && fim && fim.value) ff.value = fim.value;
    if (fl && localSelect && localSelect.value) fl.value = localSelect.value;
    if (fh && hotelInput && !hotelInput.disabled) {
      fh.disabled = false;
      fh.value = hotelInput.value;
    }
    syncFilterHotel();
  }

  function syncFilterHotel() {
    if (!filterForm) return;
    var fl = filterForm.querySelector('#filter-local');
    var box = document.getElementById('lp-filter-hotel-name');
    var input = filterForm.querySelector('#filter-hotel-nome');
    if (!fl || !box || !input) return;
    var show = fl.value === hotelValue;
    box.classList.toggle('lp-hotel-name--visible', show);
    input.disabled = !show;
    if (!show) input.value = '';
  }

  function buildSearchUrl() {
    var url = new URL(searchUrl, window.location.origin);
    if (inicio && inicio.value) url.searchParams.set('inicio', inicio.value);
    if (fim && fim.value) url.searchParams.set('fim', fim.value);
    if (localSelect && localSelect.value) url.searchParams.set('local', localSelect.value);
    if (hotelInput && !hotelInput.disabled && hotelInput.value.trim()) {
      url.searchParams.set('hotel_nome', hotelInput.value.trim());
    }
    url.hash = 'frota';
    return url.pathname + url.search + url.hash;
  }

  function refreshLeadSummary() {
    var carOk = hasCar();
    if (summary) {
      summary.hidden = !carOk;
    }
    if (clearBtn) {
      clearBtn.hidden = !carOk;
    }
    if (submitBtn && !submitBtn.classList.contains('is-loading')) {
      submitBtn.textContent = carOk ? i18n.confirm : i18n.submit;
    }
    if (!carOk) {
      if (summaryCar) summaryCar.textContent = '—';
      if (summaryDaily) summaryDaily.textContent = '—';
      if (summaryDays) summaryDays.textContent = i18n.needDates;
      if (summaryTotal) summaryTotal.textContent = '—';
      return;
    }

    var rate = selectedRate();
    var label = selectedLabel();
    if (summaryCar) summaryCar.textContent = label;
    if (summaryDaily) summaryDaily.textContent = fmtPair(rate);

    var days = daysInclusive(inicio && inicio.value, fim && fim.value);
    if (!days) {
      if (summaryDays) summaryDays.textContent = i18n.needDates;
      if (summaryTotal) summaryTotal.textContent = '—';
      return;
    }
    if (summaryDays) {
      summaryDays.textContent = String(i18n.daysTpl).replace(':count', String(days));
    }
    if (summaryTotal) {
      summaryTotal.textContent = fmtPair(rate * days);
    }
  }

  function setLeadCarSelection(id, label, rate) {
    var carId = id ? String(id) : '0';
    var carLabel = label ? String(label).trim() : '';
    var carRate = rate != null && rate !== '' ? String(rate) : '0';
    if (carIdInput) {
      carIdInput.value = carLabel ? carId : '0';
    }
    if (carRateInput) {
      carRateInput.value = carLabel ? carRate : '0';
      carRateInput.setAttribute('data-car-label', carLabel);
    }
    syncFilterToForm(true);
    refreshLeadSummary();
  }

  window.refreshLeadSummary = refreshLeadSummary;
  window.setLeadCarSelection = setLeadCarSelection;
  window.syncLeadFilterToForm = function () {
    syncFilterToForm(true);
  };

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

  function syncReturnVisibility() {
    if (!sameReturn || !returnBox) return;
    var show = !sameReturn.checked;
    returnBox.classList.toggle('lp-return-location--visible', show);
    syncHotelField(returnSelect, hotelReturnBox, hotelReturnInput, !show);
    if (!show && returnSelect) {
      returnSelect.value = '';
    }
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
  if (sameReturn) {
    sameReturn.addEventListener('change', syncReturnVisibility);
  }
  syncReturnVisibility();

  function showErrors(messages) {
    if (!errorBox) return;
    errorBox.innerHTML = '';
    if (!messages.length) {
      errorBox.hidden = true;
      return;
    }
    errorBox.hidden = false;
    var ul = document.createElement('ul');
    ul.className = 'lp-form-errors-list';
    messages.forEach(function (msg) {
      var li = document.createElement('li');
      li.textContent = msg;
      ul.appendChild(li);
    });
    errorBox.appendChild(ul);
    errorBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  function validateTrip(messages) {
    var msgs = messages || [];
    if (!inicio || !fim || !inicio.value || !fim.value) {
      msgs.push(i18n.dateRequired);
    } else if (fim.value < inicio.value) {
      msgs.push(i18n.dateOrder);
    }
    if (localSelect && hotelInput && localSelect.value === hotelValue && !hotelInput.disabled && !hotelInput.value.trim()) {
      msgs.push(i18n.hotelRequired);
    }
    if (
      sameReturn &&
      !sameReturn.checked &&
      returnSelect &&
      hotelReturnInput &&
      returnSelect.value === hotelValue &&
      !hotelReturnInput.disabled &&
      !hotelReturnInput.value.trim()
    ) {
      msgs.push(i18n.hotelRequired);
    }
    return msgs;
  }

  function validateForm() {
    var msgs = validateTrip([]);
    showErrors(msgs);
    return msgs.length === 0;
  }

  if (inicio) {
    inicio.addEventListener('change', function () {
      if (fim && inicio.value) {
        fim.min = inicio.value;
        if (fim.value && fim.value < inicio.value) {
          fim.value = inicio.value;
        }
      }
      refreshLeadSummary();
      validateForm();
    });
    inicio.addEventListener('input', refreshLeadSummary);
  }
  if (fim) {
    fim.addEventListener('change', function () {
      refreshLeadSummary();
      validateForm();
    });
    fim.addEventListener('input', refreshLeadSummary);
  }

  if (clearBtn) {
    clearBtn.addEventListener('click', function () {
      setLeadCarSelection('', '', '0');
      document.querySelectorAll('.lp-car--selected').forEach(function (c) {
        c.classList.remove('lp-car--selected');
      });
    });
  }

  form.addEventListener('submit', function (ev) {
    // Sem veículo: só busca disponibilidade (não cria lead).
    if (!hasCar()) {
      ev.preventDefault();
      var tripErrors = validateTrip([]);
      if (localSelect && !localSelect.value) {
        tripErrors.push(i18n.localRequired);
        showErrors(tripErrors);
        localSelect.focus();
        return;
      }
      showErrors(tripErrors);
      if (tripErrors.length) return;

      saveDraft();
      if (filterForm) {
        syncFormToFilter();
        filterForm.submit();
        return;
      }
      window.location.assign(buildSearchUrl());
      return;
    }

    syncFilterToForm(true);
    if (!validateForm()) {
      ev.preventDefault();
      return;
    }
    clearDraft();
    if (submitBtn && !submitBtn.disabled) {
      submitBtn.disabled = true;
      submitBtn.classList.add('is-loading');
      submitBtn.setAttribute('aria-busy', 'true');
      submitBtn.textContent = i18n.submitting;
    }
  });

  restoreDraft();
  syncFilterToForm(true);
  syncFilterHotel();
  refreshLeadSummary();

  if (window.location.hash === '#lead-success' || document.getElementById('lead-success')) {
    var success = document.getElementById('lead-success') || document.getElementById('reserva');
    if (success) {
      setTimeout(function () {
        success.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }, 120);
    }
  } else if (window.location.hash === '#reserva') {
    var anchor = document.getElementById('reserva');
    if (anchor) {
      setTimeout(function () {
        anchor.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }, 120);
    }
  } else if (window.location.hash === '#frota') {
    var fleet = document.getElementById('frota');
    if (fleet) {
      setTimeout(function () {
        fleet.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }, 120);
    }
  }

  if (filterForm) {
    var fi = filterForm.querySelector('input[name="inicio"]');
    var ff = filterForm.querySelector('input[name="fim"]');
    var fl = filterForm.querySelector('#filter-local');
    var minToday = new Date().toISOString().slice(0, 10);
    if (fi && !fi.min) fi.min = minToday;
    if (fi) {
      fi.addEventListener('change', function () {
        if (ff && fi.value) {
          ff.min = fi.value;
          if (ff.value && ff.value < fi.value) ff.value = fi.value;
        }
        syncFilterToForm(true);
      });
    }
    if (ff) {
      ff.addEventListener('change', function () {
        syncFilterToForm(true);
      });
    }
    if (fl) {
      fl.addEventListener('change', function () {
        syncFilterHotel();
        syncFilterToForm(true);
      });
    }
    var fh = filterForm.querySelector('#filter-hotel-nome');
    if (fh) {
      fh.addEventListener('change', function () {
        syncFilterToForm(true);
      });
    }
    filterForm.addEventListener('submit', function (ev) {
      if (fi && ff && fi.value && ff.value && ff.value < fi.value) {
        ev.preventDefault();
        ff.value = fi.value;
      }
      syncFilterToForm(true);
      saveDraft();
    });
  }
})();
