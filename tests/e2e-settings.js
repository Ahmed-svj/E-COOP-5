// Settings page: profile (with email change), notification switches, language/theme, CV remove, password; dark by default.
const { chromium } = require('playwright');
const { execSync } = require('child_process');
const B = process.env.BASE || 'http://127.0.0.1:8080';
let fails = 0; const ok = (c, m) => { console.log((c ? 'ok   ' : 'FAIL ') + m); if (!c) fails++; };
const sql = (q) => execSync(`mysql -uroot -N coop_db -e "${q}"`).toString().trim();
const theme = (pg) => pg.evaluate(() => document.documentElement.dataset.theme);
(async () => {
  const b = await chromium.launch();
  const pg = await (await b.newContext({ viewport: { width: 1440, height: 900 } })).newPage();
  const errs = []; pg.on('pageerror', (e) => errs.push(String(e))); pg.on('dialog', (d) => d.accept());
  await pg.goto(B + '/'); ok(await theme(pg) === 'dark', 'site opens in dark mode');
  await pg.goto(B + '/login'); await pg.fill('#email', 'student@ecoop.test'); await pg.fill('#password', 'Coop2026!');
  await Promise.all([pg.waitForNavigation(), pg.click('main button[type=submit]')]);
  ok(await theme(pg) === 'dark', 'dashboard opens in dark mode');
  await pg.click('.app-topbar [data-theme-toggle]'); await pg.waitForTimeout(400); await pg.reload();
  ok(await theme(pg) === 'light', 'top-bar theme button saves light');
  await pg.click('.app-topbar [data-theme-toggle]'); await pg.waitForTimeout(400);

  await pg.goto(B + '/settings');
  ok(await pg.locator('.set-me [data-avatar-pick]').count() >= 1, 'profile card photo opens the photo picker');
  for (const h of ['Profile', 'Notifications', 'Appearance', 'Security', 'Help']) ok((await pg.locator('h2.set-h').allTextContents()).includes(h), `section: ${h}`);
  // Appearance
  await pg.click('form.set-theme-form button[value=light]'); await pg.waitForTimeout(400); await pg.reload();
  ok(await theme(pg) === 'light' && await pg.locator('form.set-theme-form button[value=light][aria-current]').count() === 1, 'Theme: Light saved');
  await pg.click('form.set-theme-form button[value=dark]'); await pg.waitForTimeout(400); await pg.reload();
  ok(await theme(pg) === 'dark', 'Theme: Dark saved');
  await Promise.all([pg.waitForNavigation(), pg.click('main .co-chips a[lang=ar]')]);
  ok(await pg.evaluate(() => document.documentElement.lang) === 'ar' && pg.url().endsWith('/settings'), 'Language: العربية switches and stays on Settings');
  await Promise.all([pg.waitForNavigation(), pg.click('main .co-chips a[lang=en]')]);
  // Notifications autosave
  const weekly = pg.locator('input[name=weekly_summary]'); const was = await weekly.isChecked();
  const saved = pg.waitForResponse((r) => r.url().endsWith('/settings/notifications')); await weekly.click(); await saved;
  ok(sql("SELECT Enabled FROM user_preferences p JOIN users u USING(UserID) WHERE u.Email='student@ecoop.test' AND PreferenceKey='weekly_summary'") === (was ? '0' : '1'), 'Notifications: switch saves as soon as it is flipped');
  await pg.reload(); ok(await weekly.isChecked() === !was, 'Notifications: choice kept after reload');
  // Profile
  await pg.fill('#full_name', 'Mohammed Ali Al-Qahtani Jr'); await pg.fill('#gpa', '4.30');
  ok(await pg.locator('[data-email-confirm]').isHidden(), 'password box hidden while email unchanged');
  await Promise.all([pg.waitForNavigation(), pg.click('form[action$="/settings/profile"] .set-actions button')]);
  ok((await pg.locator('.app-flash').textContent()).includes('Profile saved') && sql("SELECT GPA FROM students s JOIN users u USING(UserID) WHERE u.Email='student@ecoop.test'") === '4.30', 'Save Profile updates name and GPA');
  // Email change needs password
  await pg.fill('#email', 'student2@ecoop.test');
  ok(await pg.locator('[data-email-confirm]').isVisible(), 'changing email shows the password box');
  await pg.fill('#current_password', 'wrong-pass1');
  await Promise.all([pg.waitForNavigation(), pg.click('form[action$="/settings/profile"] .set-actions button')]);
  ok(sql("SELECT COUNT(*) FROM users WHERE Email='student2@ecoop.test'") === '0', 'wrong password: email not changed');
  await pg.fill('#email', 'company@ecoop.test'); await pg.fill('#current_password', 'Coop2026!');
  await Promise.all([pg.waitForNavigation(), pg.click('form[action$="/settings/profile"] .set-actions button')]);
  ok((await pg.locator('main').textContent()).includes('already registered'), 'email already in use is refused');
  await pg.fill('#email', 'student2@ecoop.test'); await pg.fill('#current_password', 'Coop2026!');
  await Promise.all([pg.waitForNavigation(), pg.click('form[action$="/settings/profile"] .set-actions button')]);
  ok((await pg.locator('.app-flash').textContent()).includes('Email changed to student2@ecoop.test'), 'email changed with the right password');
  await pg.fill('#email', 'student@ecoop.test'); await pg.fill('#current_password', 'Coop2026!');
  await Promise.all([pg.waitForNavigation(), pg.click('form[action$="/settings/profile"] .set-actions button')]);
  // CV remove
  await Promise.all([pg.waitForNavigation(), pg.click('.set-file__remove')]);
  ok(sql("SELECT CVFilePath IS NULL FROM students s JOIN users u USING(UserID) WHERE u.Email='student@ecoop.test'") === '1', 'CV Remove works');
  // Password
  await pg.fill('#cp', 'wrong-pass1'); await pg.fill('#password', 'NewPass2026'); await pg.fill('#password_confirmation', 'NewPass2026');
  await Promise.all([pg.waitForNavigation(), pg.click('form[action$="/settings/password"] button[type=submit]')]);
  ok((await pg.locator('form[action$="/settings/password"]').textContent()).includes('incorrect') && await pg.locator('[data-email-confirm]').isHidden(), 'wrong current password shows under Security only');
  ok(errs.length === 0, 'no JavaScript errors ' + errs.join(' | '));
  await b.close(); process.exit(fails ? 1 : 0);
})();
