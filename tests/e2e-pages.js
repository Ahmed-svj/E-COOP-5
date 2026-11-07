// Visits every page for every role and fails on HTTP errors, PHP warnings, or browser console errors.
// Run: node tests/e2e-pages.js  (server on http://127.0.0.1:8080)
const { chromium } = require('playwright');
const BASE = process.env.BASE || 'http://127.0.0.1:8080';
const roles = {
  Student: ['student@ecoop.test', 'Coop2026!', ['/student/dashboard', '/student/profile', '/student/opportunities', '/student/opportunities/1', '/student/applications', '/student/attendance', '/student/report', '/student/evaluations', '/messages', '/messages/1', '/notifications', '/settings']],
  Company: ['company@ecoop.test', 'Coop2026!', ['/company/dashboard', '/company/profile', '/company/opportunities', '/company/opportunities/create', '/company/opportunities/1/edit', '/company/applicants', '/company/applicants/1', '/company/daily-code', '/company/trainees', '/messages', '/notifications', '/settings']],
  Supervisor: ['supervisor@ecoop.test', 'Coop2026!', ['/supervisor/dashboard', '/supervisor/students', '/supervisor/students/1', '/supervisor/students/4', '/supervisor/attendance', '/supervisor/reports', '/supervisor/evaluate/4', '/supervisor/export', '/messages', '/messages/1', '/notifications', '/settings']],
  Admin: ['admin@ecoop.test', 'Coop2026!', ['/admin/dashboard', '/admin/users', '/admin/companies', '/admin/opportunities', '/admin/assign', '/admin/analytics', '/admin/report', '/admin/placements', '/admin/users/2', '/admin/users/5', '/admin/audit-logs', '/admin/contact-messages', '/messages', '/notifications', '/settings']],
};
const forbidden = { Student: '/admin/dashboard', Company: '/student/dashboard', Supervisor: '/company/applicants', Admin: '/student/attendance' };
(async () => {
  const browser = await chromium.launch();
  let failures = 0;
  for (const [role, [email, pass, pages]] of Object.entries(roles)) {
    const ctx = await browser.newContext();
    const page = await ctx.newPage();
    const errs = [];
    page.on('console', (m) => { if (m.type() === 'error') errs.push(m.text()); });
    page.on('pageerror', (e) => errs.push(String(e)));
    await page.goto(BASE + '/login');
    await page.fill('#email', email);
    await page.fill('#password', pass);
    await Promise.all([page.waitForNavigation(), page.click('main button[type=submit]')]);
    for (const p of pages) {
      errs.length = 0;
      const r = await page.goto(BASE + p);
      const html = await page.content();
      const php = html.match(/(Warning|Fatal error|Notice|Deprecated|Uncaught)[^<]{0,160}/);
      const ok = r.status() === 200 && !php && !errs.filter((e) => !e.includes('fonts.g')).length;
      if (!ok) failures++;
      console.log(`${ok ? 'ok  ' : 'FAIL'} ${role.padEnd(10)} ${r.status()} ${p}${php ? '  PHP: ' + php[0] : ''}${errs.length ? '  JS: ' + errs.join(' | ') : ''}`);
    }
    const r = await page.goto(BASE + forbidden[role]);
    const denied = r.status() === 403;
    if (!denied) failures++;
    console.log(`${denied ? 'ok  ' : 'FAIL'} ${role.padEnd(10)} ${r.status()} ${forbidden[role]} (must be 403)`);
    await ctx.close();
  }
  await browser.close();
  console.log(failures ? `\n${failures} FAILURES` : '\nAll pages OK');
  process.exit(failures ? 1 : 0);
})();
