// Every link and button on every page, for every role: links must open (no 404/403/500),
// and every button must do something (submit a real form, open a modal/menu, or have a handler).
const { chromium } = require('playwright');
const B = process.env.BASE || 'http://127.0.0.1:8080';
const ROLES = { public: null, student: 'student@ecoop.test', company: 'company@ecoop.test', supervisor: 'supervisor@ecoop.test', admin: 'admin@ecoop.test' };
const START = { public: ['/', '/about', '/faq', '/privacy', '/contact', '/login', '/register/student', '/register/company', '/register/supervisor', '/forgot-password'],
  student: ['/student/dashboard'], company: ['/company/dashboard'], supervisor: ['/supervisor/dashboard'], admin: ['/admin/dashboard'] };
const HANDLED = ['data-bs-toggle', 'data-bs-dismiss', 'data-avatar-pick', 'data-dismiss-alert', 'data-print', 'data-theme-toggle'];
(async () => {
  const b = await chromium.launch();
  let fails = 0, pagesSeen = 0, linksSeen = 0, buttonsSeen = 0;
  for (const [role, email] of Object.entries(ROLES)) {
    const ctx = await b.newContext(); const pg = await ctx.newPage();
    if (email) { await pg.goto(B + '/login'); await pg.fill('#email', email); await pg.fill('#password', 'Coop2026!'); await Promise.all([pg.waitForNavigation(), pg.click('main button[type=submit]')]); }
    const queue = [...START[role]], seen = new Set(), checked = new Map();
    while (queue.length) {
      const path = queue.shift(); if (seen.has(path)) continue; seen.add(path);
      const r = await pg.goto(B + path); pagesSeen++;
      if (r.status() >= 400) { console.log(`FAIL ${role} page ${path} -> ${r.status()}`); fails++; continue; }
      const info = await pg.evaluate((HANDLED) => {
        const links = [...document.querySelectorAll('a[href]')].map((a) => a.getAttribute('href')).filter((h) => h.startsWith('/') && !h.startsWith('//'));
        const bad = [];
        document.querySelectorAll('button').forEach((btn) => {
          const form = btn.closest('form');
          const ok = HANDLED.some((a) => btn.hasAttribute(a)) || btn.classList.contains('btn-close') || btn.classList.contains('app-flash__close')
            || (form && (btn.type === 'submit') && form.getAttribute('action') !== null);
          if (!ok) bad.push(btn.outerHTML.slice(0, 120));
        });
        return { links: [...new Set(links)], bad, buttons: document.querySelectorAll('button').length };
      }, HANDLED);
      buttonsSeen += info.buttons;
      info.bad.forEach((h) => { console.log(`FAIL ${role} ${path} button does nothing: ${h}`); fails++; });
      for (const href of info.links) {
        const clean = href.split('#')[0];
        if (/\/logout$/.test(clean)) continue;
        if (!checked.has(clean)) {
          const res = await pg.request.get(B + clean, { maxRedirects: 0 }); linksSeen++;
          checked.set(clean, res.status());
          if (res.status() >= 400) { console.log(`FAIL ${role} ${path} -> link ${clean} returns ${res.status()}`); fails++; }
        }
        const p = clean.split('?')[0];
        const own = role === 'public' ? !/^\/(student|company|supervisor|admin|messages|notifications|settings|files|avatar|lang|api)/.test(p) : !/^\/(files|avatar|lang|api|notifications\/\d+)/.test(p) && !/\/csv$/.test(p);
        if (own && checked.get(clean) === 200 && !seen.has(clean) && seen.size < 120) queue.push(clean);
      }
    }
    console.log(`ok   ${role}: ${seen.size} pages crawled`);
    await ctx.close();
  }
  await b.close();
  console.log(fails ? `\n${fails} FAILURES` : `\nAll links and buttons work (${pagesSeen} pages, ${linksSeen} distinct links, ${buttonsSeen} buttons)`);
  process.exit(fails ? 1 : 0);
})();
