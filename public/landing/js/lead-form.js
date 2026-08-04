(function () {
  'use strict';

  var form = document.getElementById('form-busca');
  if (!form) return;

  var i18n = {
    dateOrder: form.dataset.errorDateOrder || 'Return date must be on or after pick-up.',
    dateRequired: form.dataset.errorDateRequired || 'Select pick-up and return dates.',
    hotelRequired: form.dataset.errorHotel || 'Enter the hotel name for delivery.',
    submitting: form.dataset.labelSubmitting || 'Sending…',
    confirm: form.dataset.labelConfirm || 'Confirm request',
    submit: form.dataset.labelSubmit || 'Search vehicles',
    daysTpl: form.dataset.summaryDays || ':count day(s)',
    needDates: form.dataset.summaryNeedDates || 'Enter the dates',
  };

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
    refreshLeadSummary();
  }

  window.refreshLeadSummary = refreshLeadSummary;
  window.setLeadCarSelection = setLeadCarSelection;

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

  function validateForm() {
    var msgs = [];
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
    if (!validateForm()) {
      ev.preventDefault();
      return;
    }
    if (submitBtn && !submitBtn.disabled) {
      submitBtn.disabled = true;
      submitBtn.classList.add('is-loading');
      submitBtn.setAttribute('aria-busy', 'true');
      submitBtn.textContent = i18n.submitting;
    }
  });

  refreshLeadSummary();

  if (window.location.hash === '#reserva' || document.querySelector('.lp-lead-banner--ok')) {
    var anchor = document.getElementById('reserva');
    if (anchor) {
      setTimeout(function () {
        anchor.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }, 120);
    }
  }

  var filterForm = document.getElementById('booking-date-filter');
  if (filterForm) {
    var fi = filterForm.querySelector('input[name="inicio"]');
    var ff = filterForm.querySelector('input[name="fim"]');
    var minToday = new Date().toISOString().slice(0, 10);
    if (fi && !fi.min) fi.min = minToday;
    if (fi) {
      fi.addEventListener('change', function () {
        if (ff && fi.value) {
          ff.min = fi.value;
          if (ff.value && ff.value < fi.value) ff.value = fi.value;
        }
      });
    }
    filterForm.addEventListener('submit', function (ev) {
      if (fi && ff && fi.value && ff.value && ff.value < fi.value) {
        ev.preventDefault();
        ff.value = fi.value;
      }
    });
  }
})();
