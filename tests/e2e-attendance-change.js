// A supervisor can change an attendance decision (Approved <-> Rejected); rejecting needs a comment.
const { chromium } = require('playwright');
const { execSync } = require('child_process');
const B = process.env.BASE || 'http://127.0.0.1:8080';
let fails = 0; const ok = (c, m) => { console.log((c ? 'ok   ' : 'FAIL ') + m); if (!c) fails++; };
const sql = (q) => execSync(`mysql -uroot -N coop_db -e "${q}"`).toString().trim();
async function login(b, email) {
  const pg = await (await b.newContext({ viewport: { width: 1440, height: 900 } })).newPage();
  await pg.goto(B + '/login'); await pg.fill('#email', email); await pg.fill('#password', 'Coop2026!');
  await Promise.all([pg.waitForNavigation(), pg.click('main button[type=submit]')]); return pg;
}
(async () => {
  const b = await chromium.launch();
  const sup = await login(b, 'supervisor@ecoop.test');
  const errs = []; sup.on('pageerror', (e) => errs.push(String(e)));
  const id = sql("SELECT ar.AttendanceID FROM attendance_records ar JOIN students s USING(StudentID) JOIN users u ON u.UserID=s.UserID WHERE u.Email='student@ecoop.test' AND ar.ApprovalStatus='Approved' ORDER BY ar.AttendanceID DESC LIMIT 1");
  const status = () => sql(`SELECT ApprovalStatus FROM attendance_records WHERE AttendanceID=${id}`);
  await sup.goto(B + '/supervisor/attendance?status=Approved');
  const btn = sup.locator(`button[data-bs-target="#chg-${id}"]`);
  ok(await btn.count() === 1, 'Approved records show a Change button');
  await btn.click(); await sup.waitForTimeout(400);
  const modal = sup.locator(`#chg-${id}`);
  ok(await modal.locator('input[value=Rejected]').isChecked(), 'dialog suggests the opposite status (Rejected)');
  await modal.locator('textarea').fill('');
  await modal.locator('button[type=submit]').click(); await sup.waitForTimeout(400);
  ok(status() === 'Approved', 'rejecting without a comment is blocked');
  // server also refuses (no browser validation)
  const token = await sup.locator('meta[name=csrf-token]').getAttribute('content');
  await sup.context().request.post(B + `/supervisor/attendance/${id}/status`, { form: { _csrf: token, status: 'Rejected', comment: '' } });
  ok(status() === 'Approved', 'and the server refuses it too');
  await modal.locator('textarea').fill('The company says you were not on site that day.');
  await Promise.all([sup.waitForNavigation(), modal.locator('button[type=submit]').click()]);
  ok(status() === 'Rejected' && (await sup.locator('.app-flash').textContent()).includes('Attendance changed to Rejected'), 'Approved -> Rejected with a comment');
  const sid = sql("SELECT StudentID FROM students s JOIN users u USING(UserID) WHERE u.Email='student@ecoop.test'");
  await sup.goto(B + `/supervisor/students/${sid}`);
  await sup.locator(`button[data-bs-target="#chg-${id}"]`).click(); await sup.waitForTimeout(400);
  ok(await sup.locator(`#chg-${id} input[value=Approved]`).isChecked(), 'student details page has Change too (suggests Approved)');
  await Promise.all([sup.waitForNavigation(), sup.locator(`#chg-${id} button[type=submit]`).click()]);
  ok(status() === 'Approved', 'Rejected -> Approved from the student page');
  ok(Number(sql(`SELECT COUNT(*) FROM audit_logs WHERE Action='ATTENDANCE_CHANGED' AND EntityID=${id}`)) === 2, 'both changes are in the audit log (old -> new)');
  const stu = await login(b, 'student@ecoop.test');
  await stu.goto(B + '/notifications');
  ok((await stu.locator('main').textContent()).includes('Your supervisor changed your check-in'), 'student is notified');
  const other = await login(b, 'noura@ecoop.test');
  const t2 = await other.locator('meta[name=csrf-token]').getAttribute('content');
  const r = await other.context().request.post(B + `/supervisor/attendance/${id}/status`, { form: { _csrf: t2, status: 'Rejected', comment: 'x' }, maxRedirects: 0 });
  ok(r.status() === 404 && status() === 'Approved', "another supervisor cannot change this student's attendance");
  ok(errs.length === 0, 'no JavaScript errors');
  await b.close(); process.exit(fails ? 1 : 0);
})();
