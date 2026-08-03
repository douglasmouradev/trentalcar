(function () {
  'use strict';

  var form = document.getElementById('form-busca');
  if (!form) return;

  var i18n = {
    dateOrder: form.dataset.errorDateOrder || 'Return date must be on or after pick-up.',
    dateRequired: form.dataset.errorDateRequired || 'Select pick-up and return dates.',
    hotelRequired: form.dataset.errorHotel || 'Enter the hotel name for delivery.',
    submitting: form.dataset.labelSubmitting || 'Sending…',
  };

  var inicio = form.querySelector('input[name="inicio"]');
  var fim = form.querySelector('input[name="fim"]');
  var localSelect = form.querySelector('#lead-local');
  var hotelInput = form.querySelector('#lead-hotel-nome');
  var hotelValue = form.dataset.hotelValue || 'Entrega no hotel';
  var submitBtn = form.querySelector('button[type="submit"]');
  var submitLabel = submitBtn ? submitBtn.textContent : '';
  var errorBox = document.getElementById('lead-form-errors');

  var today = new Date();
  var minStr = today.toISOString().slice(0, 10);
  if (inicio) inicio.min = minStr;
  if (fim) fim.min = minStr;

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
    if (localSelect && hotelInput && localSelect.value === hotelValue && !hotelInput.value.trim()) {
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
      validateForm();
    });
  }
  if (fim) {
    fim.addEventListener('change', validateForm);
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
