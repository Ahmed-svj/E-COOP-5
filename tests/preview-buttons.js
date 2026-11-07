// The static preview (docs/index.html): every link lands on a page and the main buttons act like the real site.
const { chromium } = require('playwright');
const path = require('path');
const FILE = 'file://' + path.resolve(__dirname, '../docs/index.html');
let fails = 0; const ok = (c, m) => { console.log((c ? 'ok   ' : 'FAIL ') + m); if (!c) fails++; };
(async () => {
  const b = await chromium.launch(); const pg = await b.newPage({ viewport: { width: 1440, height: 900 } });
  const errs = []; pg.on('pageerror', (e) => errs.push(String(e))); pg.on('dialog', (d) => d.accept());
  await pg.goto(FILE); await pg.waitForTimeout(800);
  const go = async (id) => { await pg.evaluate((h) => { location.hash = h; }, id); await pg.waitForTimeout(250); return pg.locator('#' + id); };
  const flash = async () => (await pg.locator('section.page:not([hidden]) .preview-flash').allTextContents()).join(' ');
  const info = await pg.evaluate(() => { const ids = new Set([...document.querySelectorAll('section.page')].map((s) => s.id));
    const links = [...document.querySelectorAll('section.page a[href^="#"]')].filter((a) => !a.dataset.file && a.getAttribute('href').length > 1);
    return { pages: ids.size, links: links.length, broken: links.filter((a) => !ids.has(a.getAttribute('href').slice(1))).map((a) => a.getAttribute('href')) }; });
  ok(info.broken.length === 0, `all ${info.links} links land on one of ${info.pages} pages`);

  let s = await go('company-company-applicants');
  await s.locator('button[data-bs-target*="--rej-"]').first().click(); await pg.waitForTimeout(400);
  await pg.fill('.modal.show textarea', 'Not a match this term.'); await pg.click('.modal.show button[type=submit]'); await pg.waitForTimeout(400);
  ok((await flash()).includes('Applicant rejected'), 'company: Reject (with reason) works');
  s = await go('company-company-daily-code');
  await s.locator('form[data-action$="/company/daily-code"] button').first().click(); await pg.waitForTimeout(200);
  ok(/^\d{6}$/.test((await s.locator('.co-code--display').first().textContent()).trim()), 'company: Generate code shows a 6-digit code');
  s = await go('company-company-applicants-status-accepted');
  ok((await s.locator('.co-badge').allTextContents()).some((x) => x.includes('Accepted')), 'company: Accepted filter tab opens its own page');
  s = await go('company-company-applicants');
  await s.locator('select[data-autosubmit-select]').selectOption({ index: 1 }); await pg.waitForTimeout(250);
  ok(/opportunity-\d+/.test(await pg.evaluate(() => location.hash)), 'company: opportunity drop-down switches page');

  s = await go('admin-admin-companies');
  await s.locator('form[data-action$="/approve"] button').first().click(); await pg.waitForTimeout(250);
  ok((await flash()).includes('Decision saved'), 'admin: Approve company works');
  s = await go('admin-admin-users');
  const btn = s.locator('form[data-action$="/toggle"] button').first(); const before = await btn.textContent();
  await btn.click(); await pg.waitForTimeout(250);
  ok((await btn.textContent()) !== before, 'admin: Suspend/Activate flips');
  s = await go('admin-admin-assign');
  await s.locator('form[data-action$="/admin/assign"] select').first().selectOption({ index: 1 });
  await s.locator('form[data-action$="/admin/assign"] button').first().click(); await pg.waitForTimeout(250);
  ok((await flash()).includes('Supervisor assigned'), 'admin: Assign supervisor works');
  s = await go('admin-admin-report');
  ok(await s.locator('.app-report-grid .co-card').count() >= 9, 'admin: System report page is in the preview');
  await s.locator('.app-topbar input[type=search]').fill('Mohammed'); await s.locator('.app-topbar form[role=search]').evaluate((f) => f.requestSubmit()); await pg.waitForTimeout(300);
  ok((await pg.evaluate(() => location.hash)) === '#admin-admin-users' && (await pg.locator('#admin-admin-users tbody tr:not([hidden])').count()) === 1, 'admin: top-bar search finds the user');

  s = await go('supervisor-supervisor-attendance');
  await s.locator('form[data-action$="/approve-all"] button').first().click(); await pg.waitForTimeout(250);
  ok((await flash()).includes('check-ins approved'), 'supervisor: Approve all works');

  s = await go('supervisor-supervisor-attendance-status-approved');
  await s.locator('button[data-bs-target*="--chg-"]').first().click(); await pg.waitForTimeout(400);
  await pg.locator('.modal.show textarea').fill('Not on site that day.'); await pg.click('.modal.show button[type=submit]'); await pg.waitForTimeout(300);
  ok((await flash()).includes('Attendance changed to Rejected'), 'supervisor: Change attendance status works');

  s = await go('student-student-dashboard');
  const boxes = s.locator('form[data-code-form] .co-code input'); for (let i = 0; i < 6; i++) await boxes.nth(i).fill(String(i + 1));
  await s.locator('form[data-code-form] button[type=submit]').click(); await pg.waitForTimeout(250);
  ok((await flash()).includes('Checked in at'), 'student: Check in works');
  s = await go('student-student-opportunities');
  await s.locator('.app-filters input[type=search]').fill('cyber'); await pg.waitForTimeout(250);
  ok(await s.locator('.app-oppcard:visible').count() === 1, 'student: opportunity search filters as you type');
  s = await go('student-messages-1');
  await s.locator('#chat-body').fill('Hello from the preview'); await s.locator('#chat-form button[type=submit]').click(); await pg.waitForTimeout(250);
  ok((await s.locator('.app-msg--mine').last().textContent()).includes('Hello from the preview'), 'student: sending a message adds it to the chat');
  s = await go('student-notifications');
  await s.locator('form[data-action$="/read-all"] button').click(); await pg.waitForTimeout(250);
  ok(await s.locator('.app-unread').count() === 0, 'student: Mark all as read works');
  s = await go('student-settings');
  await s.locator('form[data-action$="/settings/profile"] .set-actions button').click(); await pg.waitForTimeout(250);
  ok((await flash()).includes('Profile saved'), 'settings: Save Profile works');
  await s.locator('input[name=weekly_summary]').click(); await pg.waitForTimeout(200);
  ok((await flash()).includes('Settings saved'), 'settings: notification switch saves');
  await s.locator('.set-theme-form button[value=light]').click(); await pg.waitForTimeout(200);
  ok((await s.getAttribute('data-theme')) === 'light', 'settings: Theme Light works');
  await s.locator('.set-theme-form button[value=dark]').click(); await pg.waitForTimeout(200);
  await s.locator('main .co-chips a[lang=ar]').click(); await pg.waitForTimeout(250);
  ok((await pg.evaluate(() => location.hash)) === '#student-settings-ar', 'settings: Language العربية opens the Arabic page');

  s = await go('faq'); ok(await s.locator('details').count() >= 10, 'public: FAQ page is in the preview');
  s = await go('contact');
  await s.locator('#c-name').fill('Ali'); await s.locator('#c-email').fill('ali@example.com'); await s.locator('#c-topic').fill('Hi'); await s.locator('#c-body').fill('A question about co-op.');
  await s.locator('form[data-action$="/contact"] button').click(); await pg.waitForTimeout(250);
  ok((await flash()).includes('message was sent'), 'public: Contact form works');
  ok(errs.length === 0, 'no JavaScript errors ' + errs.join(' | '));
  await b.close(); process.exit(fails ? 1 : 0);
})();
