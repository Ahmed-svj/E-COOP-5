/* COOP — common helpers: fetch with CSRF, toasts, confirmations, small UI behaviours. */
(function () {
  'use strict';
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

  window.coop = {
    csrf,
    fetch(url, opts = {}) {
      opts.headers = Object.assign({ 'X-Requested-With': 'fetch', 'X-CSRF-Token': csrf }, opts.headers || {});
      opts.credentials = 'same-origin';
      return fetch(url, opts);
    },
    toast(message, tone = 'mint') {
      const box = document.getElementById('toasts');
      if (!box) return;
      const el = document.createElement('div');
      el.className = 'co-alert co-toast' + (tone ? ' co-alert--' + tone : '');
      el.setAttribute('role', 'status');
      el.textContent = message;
      box.appendChild(el);
      setTimeout(() => el.remove(), 5000);
    },
  };

  // Ask before destructive or final actions: <form data-confirm="...">
  document.addEventListener('submit', (e) => {
    const form = e.target;
    if (form.matches('[data-confirm]') && !window.confirm(form.dataset.confirm)) {
      e.preventDefault();
      return;
    }
    // Prevent double submits (e.g. two Accept clicks).
    if (!e.defaultPrevented) {
      form.querySelectorAll('button[type="submit"], button:not([type])').forEach((b) => {
        setTimeout(() => { b.disabled = true; }, 0);
      });
    }
  });

  // Dismiss flash messages
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-dismiss-alert]');
    if (btn) btn.closest('.co-alert')?.remove();
    if (e.target.closest('[data-print]')) window.print();
  });

  // Toggle forms that save on change (dashboard settings card)
  document.querySelectorAll('form[data-autosubmit]').forEach((f) => {
    f.addEventListener('change', () => f.requestSubmit ? f.requestSubmit() : f.submit());
  });
  document.querySelectorAll('select[data-autosubmit-select]').forEach((s) => {
    s.addEventListener('change', () => s.form.submit());
  });

  // Range slider <-> number input
  document.querySelectorAll('input[type=range][data-sync]').forEach((r) => {
    const num = document.getElementById(r.dataset.sync);
    if (!num) return;
    r.addEventListener('input', () => { num.value = r.value; });
    num.addEventListener('input', () => { r.value = num.value; });
  });

  // Re-open a modal that had server-side validation errors.
  window.addEventListener('load', () => {
    document.querySelectorAll('.modal.app-open-on-load').forEach((m) => {
      if (window.bootstrap) new bootstrap.Modal(m).show();
    });
  });

  // Auto-hide success flashes after a while.
  setTimeout(() => document.querySelectorAll('.app-flash.co-alert--mint').forEach((el) => el.remove()), 8000);
})();

/* Attendance change dialog: a comment is required when the new status is Rejected. */
(function () {
  'use strict';
  function sync(form) {
    const rejected = form.querySelector('input[name=status][value=Rejected]')?.checked;
    const ta = form.querySelector('textarea[name=comment]');
    if (ta) ta.required = !!rejected;
  }
  document.querySelectorAll('form[data-att-change]').forEach((f) => { sync(f); f.addEventListener('change', () => sync(f)); });
})();
