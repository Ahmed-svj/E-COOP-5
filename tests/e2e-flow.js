// End-to-end workflow + security checks (Development Guide §3.9). Needs a fresh seed:
//   mysql -u root < database/schema.sql && php database/seed.php && node tests/e2e-flow.js
const { chromium } = require('playwright');
const { execSync } = require('child_process');
const path = require('path');
const B = process.env.BASE || 'http://127.0.0.1:8080';
let fails = 0;
const ok = (cond, msg) => { console.log((cond ? 'ok   ' : 'FAIL ') + msg); if (!cond) fails++; };
const sql = (q) => execSync(`mysql -uroot -N coop_db -e "${q.replace(/"/g, '\\"')}"`).toString().trim();
const flashText = async (pg) => (await pg.locator('.app-flash').allTextContents()).join(' | ');

async function session(browser, email, pass) {
  const ctx = await browser.newContext();
  const pg = await ctx.newPage();
  pg.on('dialog', (d) => d.accept());
  if (email) {
    await pg.goto(B + '/login');
    await pg.fill('#email', email); await pg.fill('#password', pass);
    await Promise.all([pg.waitForNavigation(), pg.click('form[action$="/login"] button[type=submit]')]);
  }
  return pg;
}
const submit = async (pg, selector) => Promise.all([pg.waitForNavigation(), pg.click(selector)]);

(async () => {
  const browser = await chromium.launch();

  // 1. Register a new student (institutional email enforced) with an XSS attempt in the name
  let st = await session(browser);
  await st.goto(B + '/register/student');
  await st.fill('#full_name', 'Test <script>alert(1)</script> Student');
  await st.fill('#email', 'tester@gmail.com');
  await st.fill('#institutional_id', '449999'); await st.selectOption('#major', 'Cybersecurity');
  await st.fill('#phone', '0559999999'); await st.fill('#password', 'Passw0rd99'); await st.fill('#password_confirmation', 'Passw0rd99');
  await st.check('input[name=privacy]');
  await st.click('main button[type=submit]');
  ok(await st.locator('.co-field--invalid').count() > 0 && st.url().includes('/register/student'), 'non-college email blocked on the client');
  await st.fill('#email', 'tester@jic.edu.sa');
  await submit(st, 'main button[type=submit]');
  ok(st.url().endsWith('/student/profile'), 'student registered and sent to profile');
  ok(!(await st.content()).includes('<script>alert(1)</script>'), 'XSS in name is escaped');

  // 2. Upload a fake .pdf (exe bytes) -> rejected; real PDF -> accepted
  await st.fill('#gpa', '4.5');
  await st.setInputFiles('#cv', path.join(__dirname, 'fake.pdf'));
  await submit(st, 'form[action$="/student/profile"] button[type=submit]');
  ok((await st.content()).includes('Only PDF, DOCX files are accepted'), '.exe renamed to .pdf is rejected (finfo)');
  await st.fill('#gpa', '4.5');
  await st.setInputFiles('#cv', path.join(__dirname, 'sample.pdf'));
  await submit(st, 'form[action$="/student/profile"] button[type=submit]');
  ok((await flashText(st)).includes('Profile saved'), 'real PDF CV accepted, profile complete');

  // 3. Apply to "Cybersecurity Analyst Trainee" (Ma'aden), then try again (duplicate)
  const oppId = sql("SELECT OpportunityID FROM coop_opportunities WHERE Title='Cybersecurity Analyst Trainee'");
  await st.goto(B + '/student/opportunities/' + oppId);
  await submit(st, 'form[action$="/apply"] button');
  ok((await flashText(st)).includes('Application sent'), 'student applied');
  const dup = await st.context().request.post(B + `/student/opportunities/${oppId}/apply`, { form: { _csrf: await st.locator('meta[name=csrf-token]').getAttribute('content') } , maxRedirects: 0 });
  ok(sql(`SELECT COUNT(*) FROM applications WHERE OpportunityID=${oppId} AND StudentID=(SELECT StudentID FROM students WHERE InstitutionalID='449999')`) === '1', 'second application to the same opportunity is blocked (UNIQUE)');

  // 4. CSRF: POST without token is refused
  const myApp = sql(`SELECT ApplicationID FROM applications WHERE OpportunityID=${oppId} AND StudentID=(SELECT StudentID FROM students WHERE InstitutionalID='449999')`);
  await st.context().request.post(B + `/student/applications/${myApp}/withdraw`, { form: {}, maxRedirects: 0 });
  await st.context().request.post(B + `/student/applications/${myApp}/withdraw`, { form: { _csrf: 'forged' }, maxRedirects: 0 });
  ok(sql(`SELECT Status FROM applications WHERE ApplicationID=${myApp}`) === 'Pending', 'POST without / with a forged CSRF token is refused (nothing changed)');

  // 5. Capacity race: Nova Systems "Backend Developer Intern" has 1 seat left; accept Lina and Reem at the same time
  sql("UPDATE coop_opportunities SET Capacity=2 WHERE Title='Backend Developer Intern'");
  const sabic = await session(browser, 'company@ecoop.test', 'Coop2026!');
  await sabic.goto(B + '/company/applicants');
  const token = await sabic.locator('meta[name=csrf-token]').getAttribute('content');
  const ids = sql("SELECT ApplicationID FROM applications a JOIN coop_opportunities o USING(OpportunityID) WHERE o.Title='Backend Developer Intern' AND a.Status='Pending'").split('\n');
  await Promise.all(ids.map((id) => sabic.context().request.post(B + `/company/applications/${id}/accept`, { form: { _csrf: token }, maxRedirects: 0 })));
  ok(sql("SELECT COUNT(*) FROM applications a JOIN coop_opportunities o USING(OpportunityID) WHERE o.Title='Backend Developer Intern' AND a.Status='Accepted'") === '2', `two simultaneous accepts never exceed capacity (accepted = 2 of 2)`);
  const errBefore = require('fs').existsSync('storage/logs/error.log') ? require('fs').readFileSync('storage/logs/error.log', 'utf8').length : 0;
  const pend = sql("SELECT ApplicationID FROM applications a JOIN coop_opportunities o USING(OpportunityID) WHERE o.Title='Backend Developer Intern' AND a.Status='Pending' LIMIT 1");
  const full = await sabic.context().request.post(B + `/company/applications/${pend}/accept`, { form: { _csrf: token }, maxRedirects: 0 });
  const errAfter = require('fs').existsSync('storage/logs/error.log') ? require('fs').readFileSync('storage/logs/error.log', 'utf8').length : 0;
  ok(full.status() === 302 && errAfter === errBefore, 'accepting when seats are full shows a message, not a server error');

  // 6. Ma'aden accepts the new student (first trainee -> sets dates, creates placement)
  const maaden = await session(browser, 'maaden@ecoop.test', 'Coop2026!');
  await maaden.goto(B + '/company/applicants?opportunity=' + oppId);
  await maaden.click('button[data-bs-target^="#acc-"]');
  await maaden.waitForTimeout(400);
  await submit(maaden, '.modal.show button[type=submit]');
  ok((await flashText(maaden)).includes('accepted'), 'company accepted applicant and created the placement');
  ok(sql("SELECT PlacementID IS NOT NULL FROM students WHERE InstitutionalID='449999'") === '1', 'student linked to placement');

  // 7. Admin assigns supervisor (Noura has max load 3)
  const admin = await session(browser, 'admin@ecoop.test', 'Coop2026!');
  await admin.goto(B + '/admin/assign');
  const row = admin.locator('.co-row', { hasText: 'Test' });
  await row.locator('select').selectOption(sql("SELECT SupervisorID FROM academic_supervisors sp JOIN users u USING(UserID) WHERE u.Email='noura@ecoop.test'"));
  await submit(admin, '.co-row:has-text("Test") form[action$="/admin/assign"] button');
  ok((await flashText(admin)).includes('Supervisor assigned'), 'admin assigned academic supervisor');

  // 8. Daily code: company generates, student enters wrong then right code
  await maaden.goto(B + '/company/daily-code');
  await submit(maaden, 'form[action$="/company/daily-code"] button');
  const code = (await maaden.locator('.co-code--display span').allTextContents()).join('');
  ok(/^\d{6}$/.test(code), 'company generated a 6-digit code: ' + code);
  const typeCode = async (pg, c) => { await pg.goto(B + '/student/attendance'); const boxes = pg.locator('.co-code input'); for (let i = 0; i < 6; i++) await boxes.nth(i).fill(c[i]); await submit(pg, 'form[data-code-form] button[type=submit]'); return flashText(pg); };
  const wrong = code === '000000' ? '111111' : '000000';
  ok((await typeCode(st, wrong)).includes('Wrong code. 4 tries left'), 'wrong code counted (4 tries left)');
  ok((await typeCode(st, code)).includes('Checked in at'), 'correct code checks in');
  await st.goto(B + '/student/attendance');
  ok((await st.content()).includes('You checked in today'), 'second check-in prevented (already checked in)');

  // 9. Wrong code x5 -> 10-minute block (student@ecoop.test; Nova Systems has no code today)
  const mo = await session(browser, 'student@ecoop.test', 'Coop2026!');
  let last = '';
  for (let i = 0; i < 5; i++) last = await typeCode(mo, '123456');
  ok(last.includes('Check-in paused until'), '5 wrong codes -> check-in paused for 10 minutes');
  await mo.goto(B + '/student/attendance');
  ok(await mo.locator('.co-code input[disabled]').count() === 6, 'code inputs disabled while locked');

  // 10. Yesterday's code is rejected
  const y = sql("SELECT Code FROM daily_codes dc JOIN students s ON s.PlacementID=dc.PlacementID WHERE s.InstitutionalID='441003' AND dc.CodeDate < CURDATE() ORDER BY CodeDate DESC LIMIT 1");
  const ab = await session(browser, 'abdullah@ecoop.test', 'Coop2026!');
  ok((await typeCode(ab, y)).includes('Wrong code'), "yesterday's code is rejected");

  // 11. Supervisor approves the check-in; reject requires a comment
  const noura = await session(browser, 'noura@ecoop.test', 'Coop2026!');
  await noura.goto(B + '/supervisor/attendance');
  await submit(noura, 'form[action*="/approve"] button');
  ok((await flashText(noura)).includes('approved'), 'supervisor approved attendance');

  // 12. Final report -> revision requested -> re-upload -> approved
  await st.goto(B + '/student/report');
  await st.setInputFiles('#report', path.join(__dirname, 'sample.pdf'));
  await submit(st, 'form[action$="/student/report"] button[type=submit]');
  ok((await flashText(st)).includes('Report uploaded'), 'student uploaded final report');
  await noura.goto(B + '/supervisor/reports');
  await noura.click('button[data-bs-target^="#rev-"]'); await noura.waitForTimeout(400);
  await noura.fill('.modal.show textarea', 'Add a section on lessons learned.');
  await submit(noura, '.modal.show button[type=submit]');
  await st.goto(B + '/student/report');
  ok((await st.content()).includes('Add a section on lessons learned.'), 'student sees revision comments');
  await st.setInputFiles('#report', path.join(__dirname, 'sample.pdf'));
  await submit(st, 'form[action$="/student/report"] button[type=submit]');
  ok(sql("SELECT Status FROM final_reports fr JOIN students s USING(StudentID) WHERE s.InstitutionalID='449999'") === 'Resubmitted', 'report resubmitted');
  await noura.goto(B + '/supervisor/reports');
  await submit(noura, 'form input[value=Approved] ~ button');
  ok(sql("SELECT ReviewStatus FROM final_reports fr JOIN students s USING(StudentID) WHERE s.InstitutionalID='449999'") === 'Approved', 'supervisor approved report');

  // 13. Academic evaluation only after placement Completed; company evaluation; score range
  const sid = sql("SELECT StudentID FROM students WHERE InstitutionalID='449999'");
  await noura.goto(B + '/supervisor/evaluate/' + sid);
  ok((await noura.content()).includes('The placement must be completed first'), 'academic evaluation blocked before placement completes');
  await admin.goto(B + '/admin/assign');
  await submit(admin, '.co-row:has-text("Test") form[action*="/complete"] button');
  await noura.goto(B + '/supervisor/evaluate/' + sid);
  await noura.fill('#score', '150');
  await noura.click('form[action*="/evaluate"] button[type=submit]');
  ok(noura.url().includes('/evaluate/'), 'score 150 rejected (0–100)');
  await noura.fill('#score', '88'); await noura.fill('#comments', 'Strong security work.');
  await submit(noura, 'form[action*="/evaluate"] button[type=submit]');
  await maaden.goto(B + '/company/evaluate/' + sid);
  await maaden.fill('#score', '93');
  await submit(maaden, 'form[action*="/evaluate"] button[type=submit]');
  await st.goto(B + '/student/evaluations');
  const ev = await st.content();
  ok(ev.includes('88%') && ev.includes('93%'), 'student sees both evaluations (88 academic, 93 company)');

  // 14. Record-level access: another supervisor cannot open this student; company cannot download a non-applicant CV
  const khalid = await session(browser, 'supervisor@ecoop.test', 'Coop2026!');
  ok((await khalid.goto(B + '/supervisor/students/' + sid)).status() === 404, "supervisor cannot open another supervisor's student");
  ok((await maaden.context().request.get(B + '/files/cv/' + sql("SELECT StudentID FROM students WHERE InstitutionalID='441001'"))).status() === 200, 'company can download CV of a student who applied to it');
  ok((await maaden.context().request.get(B + '/files/cv/' + sql("SELECT StudentID FROM students WHERE InstitutionalID='441003'"))).status() === 403, 'company cannot download CV of a student who did not apply to it');

  // 15. Admin approves pending company; cannot suspend self; suspend ends a live session
  await admin.goto(B + '/admin/companies');
  await submit(admin, 'form[action*="/approve"] button');
  ok(sql("SELECT ApprovalStatus FROM companies WHERE CompanyName='Gulf Tech Services'") === 'Approved', 'admin approved pending company');
  const adminId = sql("SELECT UserID FROM users WHERE Email='admin@ecoop.test'");
  const self = await admin.context().request.post(B + `/admin/users/${adminId}/toggle`, { form: { _csrf: await admin.locator('meta[name=csrf-token]').getAttribute('content') } });
  ok(sql(`SELECT AccountStatus FROM users WHERE UserID=${adminId}`) === 'Active', 'admin cannot suspend own account');
  const reemUser = sql("SELECT UserID FROM users WHERE Email='reem@ecoop.test'");
  const reem = await session(browser, 'reem@ecoop.test', 'Coop2026!');
  await admin.goto(B + '/admin/users?role=Student');
  await submit(admin, `form[action$="/users/${reemUser}/toggle"] button`);
  await reem.goto(B + '/student/dashboard');
  ok(reem.url().endsWith('/login'), 'suspended user is logged out on next request');

  // 16. Login lockout after 5 failures
  const lk = await session(browser);
  for (let i = 0; i < 5; i++) { await lk.goto(B + '/login'); await lk.fill('#email', 'newstudent@ecoop.test'); await lk.fill('#password', 'wrongpass1'); await submit(lk, 'main button[type=submit]'); }
  await lk.fill('#email', 'newstudent@ecoop.test'); await lk.fill('#password', 'Coop2026!'); await submit(lk, 'main button[type=submit]');
  ok((await flashText(lk)).includes('Too many failed attempts'), 'account locked for 15 minutes after 5 failed logins (even with right password)');

  // 17. Password reset link (logged to mail.log) works once
  const rs = await session(browser);
  await rs.goto(B + '/forgot-password'); await rs.fill('#email', 'abdullah@ecoop.test'); await submit(rs, 'main button[type=submit]');
  const link = execSync("grep -o 'http[^ ]*reset-password?token=[a-f0-9]*' storage/logs/mail.log | tail -1").toString().trim();
  await rs.goto(link); await rs.fill('#password', 'NewPass123'); await rs.fill('#password_confirmation', 'NewPass123');
  await submit(rs, 'main button[type=submit]');
  ok((await flashText(rs)).includes('Password changed'), 'password reset via emailed link');
  await rs.goto(link);
  ok(rs.url().includes('/forgot-password'), 'reset link cannot be reused');

  // 18. Audit log is append-only; actions were recorded
  let blocked = false;
  try { execSync('mysql -uroot coop_db -e "UPDATE audit_logs SET Action=\'X\' LIMIT 1"', { stdio: 'pipe' }); } catch (e) { blocked = String(e.stderr).includes('append-only'); }
  ok(blocked, 'UPDATE audit_logs fails (trigger)');
  ok(Number(sql("SELECT COUNT(DISTINCT Action) FROM audit_logs WHERE Action IN ('APPLICATION_ACCEPTED','CHECKIN','ATTENDANCE_APPROVED','REPORT_APPROVED','EVALUATION_ACADEMIC','COMPANY_APPROVED','USER_SUSPENDED','ACCOUNT_LOCKED','CHECKIN_LOCKED')")) === 9, 'every decision type is in the audit log');
  ok(Number(sql("SELECT COUNT(*) FROM notifications WHERE UserID=(SELECT UserID FROM users WHERE Email='tester@jic.edu.sa')")) >= 5, 'student received notifications for each status change');

  await browser.close();
  console.log(fails ? `\n${fails} FAILURES` : '\nAll workflow checks passed');
  process.exit(fails ? 1 : 0);
})();
