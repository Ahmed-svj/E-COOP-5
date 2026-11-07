/* Change profile photo by pressing the avatar: pick an image, preview it everywhere at once, upload in the background. */
(function () {
  'use strict';
  const input = document.getElementById('avatar-input');
  const form = document.getElementById('avatar-form');
  if (!input || !form) return;
  const ar = document.documentElement.lang === 'ar';

  document.addEventListener('click', (e) => {
    if (e.target.closest('[data-avatar-pick]')) { e.preventDefault(); input.click(); }
  });

  function apply(src) {
    document.querySelectorAll('[data-me]').forEach((el) => {
      if (el.tagName === 'IMG') { el.src = src; return; }
      const img = document.createElement('img');
      img.className = el.className; img.alt = ''; img.src = src; img.setAttribute('data-me', '');
      el.replaceWith(img);
    });
  }

  input.addEventListener('change', () => {
    const file = input.files[0];
    if (!file) return;
    if (!/^image\/(jpeg|png|webp)$/.test(file.type)) { coop.toast(ar ? 'استخدم صورة JPG أو PNG أو WebP.' : 'Use a JPG, PNG or WebP image.', 'red'); return; }
    if (file.size > 5 * 1024 * 1024) { coop.toast(ar ? 'الصورة أكبر من 5 ميغابايت.' : 'The photo is larger than 5 MB.', 'red'); return; }
    const preview = URL.createObjectURL(file);
    const before = [...document.querySelectorAll('[data-me]')].map((el) => el.outerHTML);
    apply(preview);
    coop.fetch(form.action, { method: 'POST', body: new FormData(form) })
      .then((r) => r.json().then((d) => ({ ok: r.ok, d })))
      .then(({ ok, d }) => {
        if (!ok || !d.ok) throw new Error(d.error || 'upload');
        apply(d.url);
        coop.toast(d.message, 'mint');
      })
      .catch((err) => {
        document.querySelectorAll('[data-me]').forEach((el, i) => { if (before[i]) el.outerHTML = before[i]; });
        coop.toast(err.message && err.message !== 'upload' ? err.message : (ar ? 'تعذّر رفع الصورة.' : 'The photo could not be uploaded.'), 'red');
      })
      .finally(() => { input.value = ''; });
  });
})();
