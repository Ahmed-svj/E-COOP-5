/* Light / dark switch: flips instantly, then saves (account preference, or this visit for guests). */
(function () {
  'use strict';
  document.addEventListener('submit', (e) => {
    const form = e.target;
    if (!form.classList.contains('app-theme-form')) return;
    e.preventDefault();
    e.stopImmediatePropagation();
    const root = document.documentElement;
    root.dataset.theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
    window.dispatchEvent(new CustomEvent('coop:theme', { detail: root.dataset.theme }));
    coop.fetch(form.action, { method: 'POST', body: new FormData(form) }).catch(() => {});
    form.querySelector('button')?.removeAttribute('disabled');
  }, true);
})();
