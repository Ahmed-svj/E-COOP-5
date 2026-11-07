/* COOP static preview: make every button behave like the real site (changes last until reload). */
(function () {
  'use strict';
  const L = window.COOP_LANG;                       // { en: {...}, ar: {...} }
  const cur = () => document.querySelector('section.page:not([hidden])');
  const lang = () => (cur() && cur().id.endsWith('-ar') ? 'ar' : 'en');
  const t = (k, rep) => { let s = (L[lang()][k] || L.en[k] || k); Object.entries(rep || {}).forEach(([a, b]) => { s = s.split(':' + a).join(b); }); return s; };
  const TONE = { Approved: 'mint', Accepted: 'mint', Active: 'mint', Completed: 'lavender', Submitted: 'lavender', Resubmitted: 'lavender',
    Pending: 'amber', 'Pending Approval': 'amber', 'Revision Requested': 'amber', Rejected: 'red' };
  const badge = (st) => { const s = document.createElement('span'); s.className = 'co-badge' + (TONE[st] ? ' co-badge--' + TONE[st] : ''); s.textContent = t('status.' + st); return s; };
  function flash(msg, tone) {
    const page = cur(); const main = page && (page.querySelector('.app-content') || page.querySelector('.site-main'));
    page.querySelectorAll('.preview-flash').forEach((e) => e.remove());
    const d = document.createElement('div');
    d.className = 'co-alert app-flash preview-flash' + (tone ? ' co-alert--' + tone : ' co-alert--mint'); d.setAttribute('role', 'status'); d.textContent = msg;
    if (main) { main.prepend(d); window.scrollTo({ top: 0, behavior: 'smooth' }); }
    setTimeout(() => d.remove(), 6000);
  }
  // The row a form belongs to; forms inside a modal belong to the row of the button that opened it.
  function rowOf(form) {
    const modal = form.closest('.modal');
    const from = modal ? cur().querySelector(`[data-bs-target="#${modal.id}"]`) : form;
    return (from && (from.closest('tr') || from.closest('.co-row') || from.closest('article') || from.closest('.co-card'))) || null;
  }
  function setStatus(form, st) {
    const row = rowOf(form); if (!row) return;
    const holder = row.querySelector('.co-row__actions') || row.querySelector('td.app-actions') || row.querySelector('.d-flex.gap-2') || row;
    const old = row.querySelector('.co-badge[class*="co-badge--"], .co-badge');
    holder.querySelectorAll('form, button[data-bs-toggle="modal"]').forEach((el) => el.remove());
    if (old && old.textContent.trim() in Object.fromEntries(Object.keys(TONE).map((k) => [t('status.' + k), 1]))) old.replaceWith(badge(st));
    else holder.appendChild(badge(st));
  }
  const goto = (base) => { location.hash = '#' + base + (lang() === 'ar' ? '-ar' : ''); };
  const role = () => cur().dataset.role;

  window.COOP_SIMULATE = function (f, action) {
    const a = action.replace(/\?.*$/, '');
    const val = (n) => (f.querySelector(`[name="${n}"]`) || {}).value || '';
    if (/\/applications\/\d+\/accept$/.test(a)) { setStatus(f, 'Accepted'); return flash(t('app.accepted')); }
    if (/\/applications\/\d+\/reject$/.test(a)) { setStatus(f, 'Rejected'); return flash(t('app.rejected')); }
    if (/\/applications\/\d+\/withdraw$/.test(a)) { setStatus(f, 'Withdrawn'); return flash(t('app.withdrawn')); }
    if (/\/supervisors\/\d+\/approve$/.test(a)) { setStatus(f, 'Approved'); return flash(t('admin.supervisor_approved', { name: '' }).trim()); }
    if (/\/supervisors\/\d+\/reject$/.test(a)) { setStatus(f, 'Rejected'); return flash(t('admin.supervisor_rejected', { name: '' }).trim()); }
    if (/\/(companies|opportunities)\/\d+\/approve$/.test(a)) { setStatus(f, 'Approved'); return flash(t('admin.decision_saved')); }
    if (/\/(companies|opportunities)\/\d+\/reject$/.test(a)) { setStatus(f, 'Rejected'); return flash(t('admin.decision_saved')); }
    if (/\/attendance\/\d+\/approve$/.test(a)) { setStatus(f, 'Approved'); return flash(t('att.approved')); }
    if (/\/attendance\/\d+\/reject$/.test(a)) { setStatus(f, 'Rejected'); return flash(t('att.rejected')); }
    if (/\/attendance\/\d+\/status$/.test(a)) {
      const st = (f.querySelector('input[name=status]:checked') || {}).value;
      if (!st) return;
      if (st === 'Rejected' && !val('comment').trim()) return flash(t('att.comment_required'), 'red');
      const row = rowOf(f); const old = row && row.querySelector('.co-badge');
      if (old) old.replaceWith(badge(st));
      return flash(t('att.changed', { status: t('status.' + st) }));
    }
    if (/\/attendance\/approve-all$/.test(a)) {
      let n = 0; cur().querySelectorAll('form[data-action*="/attendance/"][data-action$="/approve"]').forEach((x) => { setStatus(x, 'Approved'); n++; });
      return flash(t('att.approved_n', { n }));
    }
    if (/\/reports\/\d+\/review$/.test(a)) { setStatus(f, val('decision') || 'Approved'); return flash(t('report.reviewed')); }
    if (/\/opportunities\/\d+\/close$/.test(a)) { setStatus(f, 'Closed'); return flash(t('opp.closed')); }
    if (/\/placements\/\d+\/complete$/.test(a)) { setStatus(f, 'Completed'); return flash(t('assign.completed')); }
    if (/\/users\/\d+\/toggle$/.test(a)) {
      const row = rowOf(f) || cur(); const b = row.querySelector('.co-badge'); const btn = f.querySelector('button');
      const active = btn.textContent.trim() === t('admin.suspend');
      if (b) { b.className = 'co-badge' + (active ? '' : ' co-badge--mint'); b.textContent = t('status.' + (active ? 'Suspended' : 'Active')); }
      btn.textContent = t(active ? 'admin.activate' : 'admin.suspend');
      btn.className = 'co-btn co-btn--sm ' + (active ? 'co-btn--soft' : 'co-btn--danger-ghost');
      return flash(t(active ? 'admin.suspended' : 'admin.activated', { name: (row.querySelector('strong, h1') || {}).textContent || '' }));
    }
    if (/\/admin\/assign$/.test(a)) {
      const sel = f.querySelector('select'); const name = sel.options[sel.selectedIndex].text.replace(/\s*\(.*$/, '');
      const row = rowOf(f); const sub = row && row.querySelectorAll('.co-row__sub')[1];
      if (sub) sub.textContent = t('admin.current_sup', { name });
      const media = row && row.querySelector('.co-row__media'); if (media) media.classList.add('co-row__media--mint');
      return flash(t('assign.done'));
    }
    if (/\/admin\/users\/supervisor$/.test(a)) { bootstrap.Modal.getInstance(f.closest('.modal'))?.hide(); return flash(t('admin.supervisor_created')); }
    if (/\/opportunities\/\d+\/apply$/.test(a)) { f.replaceWith(badge('Pending')); return flash(t('apply.done')); }
    if (/\/company\/daily-code$/.test(a)) {
      const code = String(Math.floor(Math.random() * 1e6)).padStart(6, '0');
      const box = document.createElement('div'); box.className = 'co-code co-code--display';
      code.split('').forEach((d) => { const s = document.createElement('span'); s.textContent = d; box.appendChild(s); });
      const cap = document.createElement('span'); cap.className = 'co-caption'; cap.textContent = t('code.expires');
      f.replaceWith(box); box.after(cap); return flash(t('code.generated'));
    }
    if (/\/student\/attendance$/.test(a)) {
      const code = [...f.querySelectorAll('.co-code input')].map((i) => i.value).join('');
      if (!/^\d{6}$/.test(code)) return flash(t('att.six_digits'), 'amber');
      const time = new Date().toTimeString().slice(0, 5);
      const ok = document.createElement('div'); ok.className = 'co-alert co-alert--mint'; ok.setAttribute('role', 'status');
      ok.innerHTML = '<div><strong></strong></div>'; ok.querySelector('strong').textContent = t('att.ok', { time });
      f.replaceWith(ok); return flash(t('att.ok', { time }));
    }
    if (/\/messages\/\d+$/.test(a)) {
      const box = cur().querySelector('.app-chat__msgs'); const ta = f.querySelector('textarea'); const body = ta.value.trim(); if (!body) return;
      const m = document.createElement('div'); m.className = 'app-msg app-msg--mine';
      const p = document.createElement('p'); p.textContent = body; const s = document.createElement('span'); s.textContent = new Date().toTimeString().slice(0, 5);
      m.append(p, s); box.appendChild(m); box.scrollTop = box.scrollHeight; ta.value = ''; return;
    }
    if (/\/messages\/start$/.test(a)) {
      const first = cur().querySelector('.app-chat__list a.co-row, a[href*="messages-"]');
      if (first) { location.hash = first.getAttribute('href'); return; }
      return flash(t('msg.none'), 'amber');
    }
    if (/\/notifications\/read-all$/.test(a)) {
      document.querySelectorAll(`section.page[data-role="${role()}"] .app-unread`).forEach((r) => r.classList.remove('app-unread'));
      document.querySelectorAll(`section.page[data-role="${role()}"] .co-iconbtn--dot`).forEach((b) => b.classList.remove('co-iconbtn--dot'));
      cur().querySelectorAll('.app-unread, .co-badge--lavender').forEach((b) => b.closest('.app-row-link') && b.remove());
      return flash(t('notif.read_all'));
    }
    if (/\/settings\/profile$/.test(a)) {
      const em = f.querySelector('[name=email]'); const changed = em && em.value.trim().toLowerCase() !== (f.dataset.originalEmail || '').toLowerCase();
      if (changed && !(f.querySelector('[name=current_password]') || {}).value) return flash(t('settings.email_needs_password'), 'amber');
      return flash(t('profile.saved') + (changed ? ' ' + t('settings.email_changed', { email: em.value.trim() }) : ''));
    }
    if (/\/settings\/notifications$/.test(a)) return flash(t('settings.saved'));
    if (/\/settings\/cv\/remove$/.test(a)) { cur().querySelectorAll('.set-file').forEach((x) => x.remove()); return flash(t('settings.cv_removed'), 'amber'); }
    if (/\/settings$/.test(a)) { const dm = f.querySelector('[name=dark_mode]'); if (dm) window.COOP_SET_THEME(dm.checked ? 'dark' : 'light'); return flash(t('settings.saved')); }
    if (/\/settings\/password$/.test(a)) { f.reset(); return flash(t('settings.password_changed')); }
    if (/\/settings\/email$/.test(a)) { const e = val('new_email'); f.reset(); return flash(t('settings.email_changed', { email: e })); }
    if (/\/(student|company)\/profile$/.test(a)) return flash(t('profile.saved'));
    if (/\/company\/opportunities(\/\d+)?$/.test(a)) { goto('company-company-opportunities'); setTimeout(() => flash(t('opp.submitted')), 50); return; }
    if (/\/company\/evaluate\/\d+$/.test(a)) { goto('company-company-trainees'); setTimeout(() => flash(t('eval.saved')), 50); return; }
    if (/\/supervisor\/evaluate\/\d+$/.test(a)) { goto('supervisor-supervisor-students'); setTimeout(() => flash(t('eval.saved')), 50); return; }
    if (/\/student\/report$/.test(a)) return flash(t('report.uploaded'));
    if (/\/contact$/.test(a)) { f.reset(); return flash(t('contact.sent')); }
    if (/\/forgot-password$/.test(a)) return flash(t('reset.sent'));
    flash(t('settings.saved'));
  };

  // Search boxes and filters: show only matching cards, rows and list items.
  function items(page) {
    return [...page.querySelectorAll('.app-content .co-list > .co-row, .app-content tbody tr, .app-content .row > [class*="col-"]:has(> article), .app-content .row > [class*="col-"]:has(> .app-oppcard)')];
  }
  function filter(page, terms) {
    const words = terms.map((w) => w.toLowerCase().trim()).filter(Boolean);
    let shown = 0;
    items(page).forEach((el) => { const txt = el.textContent.toLowerCase(); const ok = words.every((w) => txt.includes(w)); el.hidden = !ok; if (ok) shown++; });
    const count = page.querySelector('[data-count]'); if (count) count.textContent = t('opp.count', { n: shown });
  }
  window.COOP_SEARCH = function (f, action) {
    const target = f.dataset.searchTarget;
    const terms = [...f.querySelectorAll('input[type=search], input[name=q], input[name=action], select')].map((i) => (i.value === '0' ? '' : i.value));
    if (target && target !== cur().id) { location.hash = '#' + target; setTimeout(() => { const p = document.getElementById(target); const inp = p.querySelector('.app-filters input[type=search]'); if (inp) inp.value = terms[0]; filter(p, terms); }, 30); }
    else filter(cur(), terms);
  };
  document.addEventListener('input', (e) => { const f = e.target.closest('form.app-filters'); if (f) window.COOP_SEARCH(f); });
  // Settings: notification switches save on flip; changing the email asks for the password.
  document.addEventListener('change', (e) => { if (e.target.closest('form[data-autosave]')) flash(t('settings.saved')); });
  document.addEventListener('input', (e) => {
    const f = e.target.closest('form[data-original-email]'); if (!f || e.target.name !== 'email') return;
    const box = f.querySelector('[data-email-confirm]'); if (box) box.hidden = e.target.value.trim().toLowerCase() === f.dataset.originalEmail.toLowerCase();
  });
  document.addEventListener('change', (e) => { const f = e.target.closest('form.app-filters'); if (f && e.target.tagName === 'SELECT' && !e.target.hasAttribute('data-autosubmit-select')) window.COOP_SEARCH(f); });
})();
