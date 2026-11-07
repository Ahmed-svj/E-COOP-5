/* Settings: notification switches save on flip, theme buttons switch instantly, email change asks for the password, CV file name shows. */
(function () {
  'use strict';
  const ar = document.documentElement.lang === 'ar';
  document.querySelectorAll('form[data-autosave]').forEach((f) => {
    f.addEventListener('change', () => {
      coop.fetch(f.action, { method: 'POST', body: new FormData(f) })
        .then((r) => r.json()).then((d) => coop.toast(d.message || 'Saved', 'mint'))
        .catch(() => coop.toast(ar ? 'تعذّر الحفظ.' : 'Could not save.', 'red'));
    });
  });
  document.querySelectorAll('form.set-theme-form').forEach((f) => {
    f.addEventListener('click', (e) => {
      const b = e.target.closest('button[name=theme]'); if (!b) return;
      e.preventDefault();
      document.documentElement.dataset.theme = b.value;
      f.querySelectorAll('button').forEach((x) => { x.toggleAttribute('aria-current', x === b); x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
      const data = new FormData(f); data.set('theme', b.value);
      coop.fetch(f.action, { method: 'POST', body: data }).catch(() => {});
    });
  });
  const profile = document.querySelector('form[data-original-email]');
  if (profile) {
    const email = profile.querySelector('[name=email]'), box = profile.querySelector('[data-email-confirm]'), pass = box.querySelector('input');
    const sync = () => { const changed = email.value.trim().toLowerCase() !== profile.dataset.originalEmail.toLowerCase(); box.hidden = !changed; pass.required = changed; };
    email.addEventListener('input', sync); sync();
    const cv = profile.querySelector('input[type=file][name=cv]'), label = profile.querySelector('[data-file-label]');
    if (cv && label) cv.addEventListener('change', () => { if (cv.files[0]) label.textContent = cv.files[0].name; });
  }
})();
