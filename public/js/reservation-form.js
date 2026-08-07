(() => {
  const form = document.getElementById('resForm');
  if (!form) return;

  const cfg = {
    excludeId: form.dataset.excludeId ? parseInt(form.dataset.excludeId, 10) : null,
    conflictText: form.dataset.conflictText || '',
    conflictUrl: form.dataset.conflictUrl || '',
    searchUrl: form.dataset.searchUrl || '',
    quickUrl: form.dataset.quickUrl || '',
  };

  const carSel = document.getElementById('car_id');
  const daily = document.getElementById('daily_rate');
  const discount = document.getElementById('discount');
  const pickupD = document.getElementById('pickup_date');
  const returnD = document.getElementById('return_date');
  const pickupT = document.getElementById('pickup_time');
  const returnT = document.getElementById('return_time');
  const totalEl = document.getElementById('total_preview');
  const daysEl = document.getElementById('days_preview');
  const conflictEl = document.getElementById('conflict_msg');
  const preview = document.getElementById('carPreview');
  const custSearch = document.getElementById('custSearch');
  const custSuggest = document.getElementById('custSuggest');
  const custSel = document.getElementById('customer_id');
  const modal = document.getElementById('quickCustModal');

  const csrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  function fmtMoney(n) {
    const rateRaw = document.body?.dataset?.usdBrlRate;
    const rate = rateRaw ? parseFloat(rateRaw) : NaN;
    const usdBrl = Number.isFinite(rate) && rate > 0 ? rate : 5.5;
    const usd = n.toLocaleString('en-US', { style: 'currency', currency: 'USD' });
    const brl = (n * usdBrl).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    return `${usd} ≈ ${brl}`;
  }

  function refreshUsdConvert(input) {
    if (!input || !input.matches || !input.matches('[data-usd-convert]')) return;
    const wrap = input.closest('.field') || input.parentElement;
    const out = wrap ? wrap.querySelector('[data-usd-convert-out]') : null;
    if (!out) return;
    const n = parseFloat(String(input.value || '0').replace(',', '.'));
    out.textContent = fmtMoney(Number.isFinite(n) && n > 0 ? n : 0);
  }

  /** Diárias por períodos de 24h (mínimo 1). */
  function rentalDays(dateA, timeA, dateB, timeB) {
    if (!dateA || !dateB) return 1;
    const t1 = (timeA || '12:00').slice(0, 5);
    const t2 = (timeB || '12:00').slice(0, 5);
    const d1 = new Date(`${dateA}T${t1}:00`);
    const d2 = new Date(`${dateB}T${t2}:00`);
    if (Number.isNaN(d1.getTime()) || Number.isNaN(d2.getTime()) || d2 <= d1) return 1;
    const hours = (d2.getTime() - d1.getTime()) / 3600000;
    return Math.max(1, Math.ceil(hours / 24));
  }

  function recalc() {
    const rate = parseFloat(daily?.value || '0') || 0;
    const disc = parseFloat(discount?.value || '0') || 0;
    const days = rentalDays(
      pickupD?.value || '',
      pickupT?.value || '',
      returnD?.value || '',
      returnT?.value || ''
    );
    const total = Math.max(0, rate * days - disc);
    if (daysEl) {
      const tpl = form.dataset.daysLabel || ':count diária(s)';
      daysEl.textContent = String(tpl).replace(':count', String(days));
    }
    if (totalEl) totalEl.textContent = fmtMoney(total);
    refreshUsdConvert(daily);
  }

  function syncCar() {
    const opt = carSel?.selectedOptions?.[0];
    if (!opt || !daily) return;
    const rate = opt.getAttribute('data-rate');
    if (rate && !form.dataset.rateTouched) {
      daily.value = rate;
    }
    if (preview) {
      preview.textContent = opt.getAttribute('data-label') || '';
    }
    recalc();
    scheduleConflict();
  }

  let tmr;
  function scheduleConflict() {
    clearTimeout(tmr);
    tmr = setTimeout(checkConflict, 350);
  }

  let hasConflict = false;

  async function checkConflict() {
    if (!cfg.conflictUrl || !carSel || !pickupD || !returnD || !pickupT || !returnT) return;
    const params = new URLSearchParams({
      car_id: carSel.value,
      pickup_date: pickupD.value,
      pickup_time: pickupT.value,
      return_date: returnD.value,
      return_time: returnT.value,
    });
    if (cfg.excludeId) params.set('exclude_id', String(cfg.excludeId));
    try {
      const res = await fetch(`${cfg.conflictUrl}?${params.toString()}`, { headers: { Accept: 'application/json' } });
      const data = await res.json();
      if (!conflictEl) return;
      hasConflict = Boolean(data.conflict);
      if (hasConflict) {
        conflictEl.textContent = cfg.conflictText || 'Conflito';
        conflictEl.classList.remove('hidden');
      } else {
        conflictEl.classList.add('hidden');
      }
    } catch {
      hasConflict = false;
    }
  }

  form.addEventListener('submit', (ev) => {
    if (hasConflict) {
      ev.preventDefault();
      conflictEl?.classList.remove('hidden');
      conflictEl?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      return;
    }
    const btn = document.getElementById('resSubmitBtn') || form.querySelector('button[type="submit"]');
    if (btn && !btn.disabled) {
      btn.disabled = true;
      btn.classList.add('is-loading');
      btn.setAttribute('aria-busy', 'true');
      const submitting = form.dataset.labelSubmitting || 'Saving…';
      btn.dataset.originalLabel = btn.textContent || '';
      btn.textContent = submitting;
    }
  });

  carSel?.addEventListener('change', () => {
    form.dataset.rateTouched = '';
    syncCar();
  });
  daily?.addEventListener('input', () => {
    form.dataset.rateTouched = '1';
    recalc();
    scheduleConflict();
  });
  discount?.addEventListener('input', recalc);
  [pickupD, returnD, pickupT, returnT].forEach((el) => el?.addEventListener('change', () => {
    if (pickupD && returnD && pickupD.value && returnD.value && returnD.value < pickupD.value) {
      returnD.value = pickupD.value;
    }
    recalc();
    scheduleConflict();
  }));

  let stmr;
  custSearch?.addEventListener('input', () => {
    clearTimeout(stmr);
    stmr = setTimeout(async () => {
      const q = custSearch.value.trim();
      if (!cfg.searchUrl || q.length < 2) {
        custSuggest.style.display = 'none';
        return;
      }
      const res = await fetch(`${cfg.searchUrl}?q=${encodeURIComponent(q)}`);
      const json = await res.json();
      custSuggest.innerHTML = '';
      (json.data || []).forEach((c) => {
        const div = document.createElement('div');
        div.className = 'suggest-item';
        div.textContent = `${c.full_name} — ${c.document}`;
        div.addEventListener('click', () => {
          let opt = Array.from(custSel.options).find((o) => String(o.value) === String(c.id));
          if (!opt) {
            opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = `${c.full_name} — ${c.document}`;
            custSel.appendChild(opt);
          }
          custSel.value = String(c.id);
          custSuggest.style.display = 'none';
          custSearch.value = '';
        });
        custSuggest.appendChild(div);
      });
      custSuggest.style.display = json.data?.length ? 'block' : 'none';
    }, 250);
  });

  document.getElementById('openQuickCust')?.addEventListener('click', openModal);
  document.getElementById('qc_close')?.addEventListener('click', closeModal);

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
      closeModal();
    }
  });

  let modalLastFocus = null;

  function openModal() {
    if (!modal) return;
    modalLastFocus = document.activeElement;
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
    document.getElementById('qc_name')?.focus();
  }

  function closeModal() {
    if (!modal) return;
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
    if (modalLastFocus && typeof modalLastFocus.focus === 'function') {
      modalLastFocus.focus();
    }
  }

  document.getElementById('qc_save')?.addEventListener('click', async () => {
    const errEl = document.getElementById('qc_error');
    const body = {
      _csrf: csrf(),
      type: 'individual',
      full_name: document.getElementById('qc_name')?.value || '',
      document: document.getElementById('qc_doc')?.value || '',
      phone: document.getElementById('qc_phone')?.value || '',
      email: document.getElementById('qc_email')?.value || '',
    };
    if (errEl) {
      errEl.textContent = '';
      errEl.classList.add('hidden');
    }
    const res = await fetch(cfg.quickUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(body),
    });
    const json = await res.json();
    if (!json.ok) {
      if (errEl) {
        errEl.textContent = json.error || form.dataset.quickError || 'Erro ao salvar';
        errEl.classList.remove('hidden');
      }
      return;
    }
    const c = json.customer;
    const opt = document.createElement('option');
    opt.value = c.id;
    opt.textContent = `${c.full_name} — ${c.document}`;
    custSel.appendChild(opt);
    custSel.value = String(c.id);
    closeModal();
  });

  syncCar();
  recalc();
  checkConflict();

  const leadName = form.dataset.leadName;
  if (leadName) {
    const qcName = document.getElementById('qc_name');
    const qcEmail = document.getElementById('qc_email');
    const qcPhone = document.getElementById('qc_phone');
    if (qcName && !qcName.value) qcName.value = leadName;
    if (qcEmail && !qcEmail.value && form.dataset.leadEmail) qcEmail.value = form.dataset.leadEmail;
    if (qcPhone && !qcPhone.value && form.dataset.leadPhone) qcPhone.value = form.dataset.leadPhone;
  }
})();
