(() => {
  const root = document.getElementById('calendarRoot');
  const eventsUrl = root?.dataset.eventsUrl || '';
  if (!root || !eventsUrl) return;
  const cfg = { eventsUrl };
  const loading = document.getElementById('calLoading');
  const spinner = loading?.querySelector('.cal-loading-spinner');
  const monthInput = document.getElementById('calMonth');
  const dayInput = document.getElementById('calDay');
  const fCar = document.getElementById('fCar');
  const fOp = document.getElementById('fOp');
  const fStatus = document.getElementById('fStatus');
  const tabs = document.getElementById('calTabs');
  const calPrev = document.getElementById('calPrev');
  const calNext = document.getElementById('calNext');
  const calToday = document.getElementById('calToday');
  const locale = window.__calLocale || undefined;
  let view = 'month';

  const ALLOWED_STATUS = new Set(['pending', 'confirmed', 'active', 'completed', 'cancelled']);

  function escapeHtml(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function safeStatus(status) {
    const s = String(status ?? '');
    return ALLOWED_STATUS.has(s) ? s : 'pending';
  }

  function safeHex(hex) {
    return /^#[0-9A-Fa-f]{6}$/.test(String(hex ?? '')) ? hex : '#4f9eff';
  }

  function monthBounds(ym) {
    const [y, m] = ym.split('-').map(Number);
    const start = new Date(y, m - 1, 1);
    const end = new Date(y, m, 0);
    const pad = (n) => String(n).padStart(2, '0');
    return {
      start: `${y}-${pad(m)}-01`,
      end: `${y}-${pad(m)}-${pad(end.getDate())}`,
      jsStart: start,
      firstDow: start.getDay(),
      daysInMonth: end.getDate(),
    };
  }

  function weekdayLabels() {
    const base = new Date(2024, 0, 7);
    const labels = [];
    for (let i = 0; i < 7; i += 1) {
      const d = new Date(base);
      d.setDate(base.getDate() + i);
      labels.push(d.toLocaleDateString(locale, { weekday: 'short' }));
    }
    return labels;
  }

  function isToday(ym, day) {
    const now = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    const today = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
    const [y, m] = ym.split('-').map(Number);
    return today === `${y}-${pad(m)}-${pad(day)}`;
  }

  function setLoading(on) {
    root.setAttribute('aria-busy', on ? 'true' : 'false');
    if (spinner) spinner.hidden = !on;
    if (loading) loading.style.opacity = on ? '1' : '0.35';
  }

  function syncDayPicker() {
    if (!dayInput || !monthInput) return;
    const ym = monthInput.value;
    if (!dayInput.value.startsWith(`${ym}-`)) {
      const today = new Date();
      const pad = (n) => String(n).padStart(2, '0');
      const curYm = `${today.getFullYear()}-${pad(today.getMonth() + 1)}`;
      dayInput.value = ym === curYm
        ? `${ym}-${pad(today.getDate())}`
        : `${ym}-01`;
    }
  }

  function toggleDayInput() {
    if (!dayInput) return;
    dayInput.classList.toggle('hidden', view !== 'day');
    if (view === 'day') syncDayPicker();
  }

  function shiftMonth(delta) {
    if (!monthInput?.value) return;
    const [y, m] = monthInput.value.split('-').map(Number);
    const d = new Date(y, m - 1 + delta, 1);
    const pad = (n) => String(n).padStart(2, '0');
    monthInput.value = `${d.getFullYear()}-${pad(d.getMonth() + 1)}`;
    syncDayPicker();
    render();
  }

  function goToday() {
    const now = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    if (monthInput) monthInput.value = `${now.getFullYear()}-${pad(now.getMonth() + 1)}`;
    if (dayInput) dayInput.value = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
    syncDayPicker();
    render();
  }

  async function loadEvents() {
    const ym = monthInput?.value || '';
    const { start, end } = monthBounds(ym);
    const params = new URLSearchParams({ start, end });
    if (fCar?.value) params.set('car_id', fCar.value);
    if (fOp?.value) params.set('operator_id', fOp.value);
    if (fStatus?.value) params.set('status', fStatus.value);
    setLoading(true);
    try {
      const res = await fetch(`${cfg.eventsUrl}?${params.toString()}`);
      const json = await res.json();
      return json.data || [];
    } finally {
      setLoading(false);
    }
  }

  function bindEventClicks(selector) {
    root.querySelectorAll(selector).forEach((el) => {
      el.addEventListener('click', () => {
        const id = el.getAttribute('data-id');
        if (id) window.location.href = `${window.APP_BASE_URL}/reservations/${id}`;
      });
    });
  }

  function eventsByDay(events, ym) {
    const map = {};
    const { daysInMonth } = monthBounds(ym);
    for (let d = 1; d <= daysInMonth; d += 1) map[d] = [];
    events.forEach((ev) => {
      const ps = new Date(`${ev.pickup_date}T00:00:00`);
      const rs = new Date(`${ev.return_date}T00:00:00`);
      for (let d = 1; d <= daysInMonth; d += 1) {
        const cur = new Date(monthBounds(ym).jsStart);
        cur.setDate(d);
        if (cur >= ps && cur <= rs) map[d].push(ev);
      }
    });
    return map;
  }

  function renderMonth(events) {
    const ym = monthInput.value;
    const { firstDow, daysInMonth } = monthBounds(ym);
    const byDay = eventsByDay(events, ym);
    const rows = ['<div class="cal-month">'];
    weekdayLabels().forEach((d) => rows.push(`<div class="cal-dow">${escapeHtml(d)}</div>`));
    for (let i = 0; i < firstDow; i += 1) rows.push('<div class="cal-cell"></div>');
    for (let day = 1; day <= daysInMonth; day += 1) {
      const todayCls = isToday(ym, day) ? ' cal-cell--today' : '';
      rows.push(`<div class="cal-cell${todayCls}">`);
      rows.push(`<div class="cal-daynum">${day}</div>`);
      (byDay[day] || []).slice(0, 3).forEach((ev) => {
        const label = `${ev.brand} ${ev.model} — ${(ev.customer_name || '').slice(0, 18)}`;
        const tip = `${ev.code} · ${ev.pickup_date} → ${ev.return_date}`;
        const st = safeStatus(ev.status);
        const hex = safeHex(ev.color_hex);
        const id = parseInt(ev.id, 10) || 0;
        rows.push(
          `<div class="cal-event st-${st}" data-id="${id}" title="${escapeHtml(tip)}" style="border-left-color:${hex}">${escapeHtml(label)}</div>`,
        );
      });
      rows.push('</div>');
    }
    rows.push('</div>');
    root.innerHTML = rows.join('');
    bindEventClicks('.cal-event');
  }

  function renderWeek(events) {
    const ym = monthInput.value;
    const b = monthBounds(ym);
    const start = new Date(b.jsStart);
    const dow = start.getDay();
    start.setDate(start.getDate() - dow);
    const days = [];
    for (let i = 0; i < 7; i += 1) {
      const d = new Date(start);
      d.setDate(start.getDate() + i);
      days.push(d);
    }
    const pad = (n) => String(n).padStart(2, '0');
    const dayKey = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
    let html = '<div class="week-grid week-grid-full"><div class="week-corner"></div>';
    days.forEach((d) => {
      html += `<div class="cal-dow">${escapeHtml(d.toLocaleDateString(locale, { weekday: 'short', day: 'numeric' }))}</div>`;
    });
    for (let h = 8; h <= 18; h += 1) {
      html += `<div class="week-hour">${h}h</div>`;
      days.forEach((d) => {
        const key = dayKey(d);
        const dayEvents = events.filter((ev) => key >= ev.pickup_date && key <= ev.return_date);
        const inHour = dayEvents.filter((ev) => {
          const ph = parseInt(String(ev.pickup_time).slice(0, 2), 10);
          return ph === h || (ph <= h && h <= parseInt(String(ev.return_time).slice(0, 2), 10));
        });
        html += '<div class="week-cell">';
        inHour.slice(0, 2).forEach((ev) => {
          const st = safeStatus(ev.status);
          const hex = safeHex(ev.color_hex);
          const id = parseInt(ev.id, 10) || 0;
          html += `<div class="cal-event st-${st} week-event" data-id="${id}" title="${escapeHtml(ev.code)}" style="border-left-color:${hex}">${escapeHtml(ev.code)}</div>`;
        });
        if (inHour.length > 2) html += `<span class="muted week-more">+${inHour.length - 2}</span>`;
        html += '</div>';
      });
    }
    html += '</div>';
    root.innerHTML = html;
    bindEventClicks('.week-event');
  }

  function renderDay(events) {
    const day = dayInput?.value || `${monthInput.value}-01`;
    const list = events.filter((e) => day >= e.pickup_date && day <= e.return_date);
    root.innerHTML = `<div class="day-list">${list.length ? list.map((e) => {
      const st = safeStatus(e.status);
      const id = parseInt(e.id, 10) || 0;
      return `<div class="day-item st-${st} day-item-click" data-id="${id}"><strong>${escapeHtml(e.code)}</strong> — ${escapeHtml(e.brand)} ${escapeHtml(e.model)} — ${escapeHtml(e.customer_name)}<br>
        <span class="muted">${escapeHtml(e.pickup_date)} ${escapeHtml(String(e.pickup_time).slice(0, 5))} → ${escapeHtml(e.return_date)} ${escapeHtml(String(e.return_time).slice(0, 5))} · ${escapeHtml(e.operator_name)}</span></div>`;
    }).join('') : `<p class="muted day-empty">${escapeHtml(day)}</p>`}</div>`;
    bindEventClicks('.day-item-click');
  }

  function renderVehicle(events) {
    const ym = monthInput.value;
    const b = monthBounds(ym);
    const start = b.jsStart.getTime();
    const end = new Date(b.jsStart.getFullYear(), b.jsStart.getMonth() + 1, 0).getTime();
    const span = end - start || 1;
    const carsMap = {};
    events.forEach((e) => {
      if (!carsMap[e.car_id]) {
        carsMap[e.car_id] = { label: `${e.brand} ${e.model}`, plate: e.license_plate, hex: safeHex(e.color_hex), items: [] };
      }
      carsMap[e.car_id].items.push(e);
    });
    let html = '<div class="gantt">';
    Object.values(carsMap).forEach((row) => {
      html += '<div class="gantt-row"><div>';
      html += `<span class="swatch" style="background:${row.hex}"></span><strong>${escapeHtml(row.label)}</strong><div class="mono muted">${escapeHtml(row.plate)}</div></div>`;
      html += '<div class="gantt-track">';
      row.items.forEach((e) => {
        const ps = new Date(`${e.pickup_date}T${String(e.pickup_time).padEnd(8, '0')}`).getTime();
        const rs = new Date(`${e.return_date}T${String(e.return_time).padEnd(8, '0')}`).getTime();
        const left = Math.max(0, ((ps - start) / span) * 100);
        const width = Math.max(2, ((rs - ps) / span) * 100);
        const st = safeStatus(e.status);
        const id = parseInt(e.id, 10) || 0;
        html += `<div class="gantt-bar st-${st} gantt-bar-click" data-id="${id}" title="${escapeHtml(e.code)}" style="left:${left}%;width:${width}%">${escapeHtml(e.code)}</div>`;
      });
      html += '</div></div>';
    });
    html += '</div>';
    root.innerHTML = html;
    bindEventClicks('.gantt-bar-click');
  }

  async function render() {
    const events = await loadEvents();
    if (view === 'month') renderMonth(events);
    else if (view === 'week') renderWeek(events);
    else if (view === 'day') renderDay(events);
    else renderVehicle(events);
  }

  tabs?.querySelectorAll('.tab').forEach((btn) => {
    btn.addEventListener('click', () => {
      tabs.querySelectorAll('.tab').forEach((b) => b.classList.remove('active'));
      btn.classList.add('active');
      view = btn.getAttribute('data-view') || 'month';
      toggleDayInput();
      render();
    });
  });

  calPrev?.addEventListener('click', () => shiftMonth(-1));
  calNext?.addEventListener('click', () => shiftMonth(1));
  calToday?.addEventListener('click', goToday);

  monthInput?.addEventListener('change', () => { syncDayPicker(); render(); });
  dayInput?.addEventListener('change', render);
  [fCar, fOp, fStatus].forEach((el) => el?.addEventListener('change', render));

  toggleDayInput();
  render();
})();
