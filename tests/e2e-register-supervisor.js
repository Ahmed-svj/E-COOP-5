// Supervisor self-registration: college email, waits for admin approval, not assignable until approved; rejection turns the account off.
const { chromium } = require('playwright');
const { execSync } = require('child_process');
const B = process.env.BASE || 'http://127.0.0.1:8080';
let fails = 0; const ok = (c, m) => { console.log((c ? 'ok   ' : 'FAIL ') + m); if (!c) fails++; };
const sql = (q) => execSync(`mysql -uroot -N coop_db -e "${q}"`).toString().trim();
const ctx = async (b) => (await b.newContext({ viewport: { width: 1440, height: 900 } })).newPage();
async function login(b, email, pass = 'Coop2026!') { const pg = await ctx(b); await pg.goto(B + '/login'); await pg.fill('#email', email); await pg.fill('#password', pass); await Promise.all([pg.waitForNavigation(), pg.click('main button[type=submit]')]); return pg; }
async function register(b, name, email) {
  const pg = await ctx(b); pg.on('dialog', (d) => d.accept());
  await pg.goto(B + '/login'); await pg.waitForLoadState('networkidle');
  const href = await pg.locator('a[href$="/register/supervisor"]').getAttribute('href');
  await pg.goto(B + href);
  await pg.fill('#full_name', name); await pg.fill('#email', email); await pg.fill('#phone', '0553000001');
  await pg.selectOption('#department', 'Computer Science'); await pg.fill('#password', 'Super2026x'); await pg.fill('#password_confirmation', 'Super2026x');
  await pg.check('input[name=privacy]'); return pg;
}
(async () => {
  const b = await chromium.launch();
  const errs = [];
  let pg = await register(b, 'Dr. Sara Test', 'sara.test@gmail.com'); pg.on('pageerror', (e) => errs.push(String(e)));
  await pg.click('main button[type=submit]'); await pg.waitForTimeout(300);
  ok(pg.url().endsWith('/register/supervisor'), 'login page links to supervisor sign-up; a non-college email is refused');
  await pg.fill('#email', 'sara.test@jic.edu.sa');
  await Promise.all([pg.waitForNavigation(), pg.click('main button[type=submit]')]);
  ok(pg.url().endsWith('/supervisor/dashboard') && (await pg.locator('main').textContent()).includes('Waiting for approval'), 'registered and sees "Waiting for approval"');
  const supId = sql("SELECT SupervisorID FROM academic_supervisors sp JOIN users u USING(UserID) WHERE u.Email='sara.test@jic.edu.sa'");
  ok(sql(`SELECT ApprovalStatus FROM academic_supervisors WHERE SupervisorID=${supId}`) === 'Pending', 'account stored as Pending');

  const admin = await login(b, 'admin@ecoop.test');
  ok((await admin.locator('main').textContent()).includes('waiting for approval'), 'admin dashboard shows the waiting supervisor');
  await admin.goto(B + '/admin/assign');
  ok(!(await admin.locator('main').textContent()).includes('Dr. Sara Test'), 'pending supervisor cannot be assigned students');
  await admin.goto(B + '/admin/users?role=Supervisor');
  await Promise.all([admin.waitForNavigation(), admin.click(`form[action$="/admin/supervisors/${supId}/approve"] button`)]);
  ok(sql(`SELECT ApprovalStatus FROM academic_supervisors WHERE SupervisorID=${supId}`) === 'Approved', 'admin approves from Users');
  await admin.goto(B + '/admin/assign');
  ok((await admin.locator('main').textContent()).includes('Dr. Sara Test'), 'approved supervisor can now be assigned');
  await pg.goto(B + '/notifications');
  ok((await pg.locator('main').textContent()).includes('supervisor account was approved'), 'supervisor is notified of approval');
  await pg.goto(B + '/supervisor/dashboard');
  ok(!(await pg.locator('main').textContent()).includes('Waiting for approval'), 'banner gone after approval');

  const pg2 = await register(b, 'Dr. Fake Person', 'fake.person@jic.edu.sa');
  await Promise.all([pg2.waitForNavigation(), pg2.click('main button[type=submit]')]);
  const sup2 = sql("SELECT SupervisorID FROM academic_supervisors sp JOIN users u USING(UserID) WHERE u.Email='fake.person@jic.edu.sa'");
  await admin.goto(B + '/admin/users?role=Supervisor');
  await admin.click(`button[data-bs-target="#rejsup-${sup2}"]`); await admin.waitForTimeout(400);
  await admin.fill('.modal.show textarea', 'Not a member of staff.');
  await Promise.all([admin.waitForNavigation(), admin.click('.modal.show button[type=submit]')]);
  ok(sql(`SELECT CONCAT(sp.ApprovalStatus, '/', u.AccountStatus) FROM academic_supervisors sp JOIN users u USING(UserID) WHERE sp.SupervisorID=${sup2}`) === 'Rejected/Suspended', 'admin rejects with a reason; account turned off');
  await pg2.goto(B + '/supervisor/dashboard');
  ok(pg2.url().endsWith('/login'), 'rejected supervisor is logged out');
  const again = await ctx(b); await again.goto(B + '/login'); await again.fill('#email', 'fake.person@jic.edu.sa'); await again.fill('#password', 'Super2026x');
  await Promise.all([again.waitForNavigation(), again.click('main button[type=submit]')]);
  ok((await again.locator('.app-flash').textContent()).includes('suspended'), 'and cannot log in again');
  ok(Number(sql("SELECT COUNT(*) FROM audit_logs WHERE Action IN ('REGISTER_SUPERVISOR','SUPERVISOR_APPROVED','SUPERVISOR_REJECTED')")) === 4, 'sign-ups and decisions are in the audit log');
  ok(errs.length === 0, 'no JavaScript errors');
  await b.close(); process.exit(fails ? 1 : 0);
})();
