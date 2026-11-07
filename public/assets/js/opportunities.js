/* Live search / filter for opportunities (UC-02) without page reloads. */
(function () {
  'use strict';
  const form = document.querySelector('form[data-live-filter]');
  if (!form) return;
  const results = document.querySelector('[data-results]');
  const count = document.querySelector('[data-count]');
  let timer, controller;

  function run() {
    const params = new URLSearchParams(new FormData(form));
    history.replaceState(null, '', '?' + params.toString());
    params.set('format', 'json');
    controller?.abort();
    controller = new AbortController();
    results.setAttribute('aria-busy', 'true');
    coop.fetch(form.action + '?' + params.toString(), { signal: controller.signal })
      .then((r) => r.json())
      .then((d) => {
        results.innerHTML = d.html; // server-rendered and escaped
        const isAr = document.documentElement.lang === 'ar';
        count.textContent = isAr ? d.count + ' فرصة' : d.count + (d.count === 1 ? ' opportunity' : ' opportunities');
      })
      .catch(() => {})
      .finally(() => results.removeAttribute('aria-busy'));
  }

  form.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(run, 250); });
  form.addEventListener('change', run);
  form.addEventListener('submit', (e) => { e.preventDefault(); run(); });
})();
