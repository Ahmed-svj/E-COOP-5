// Pressing the avatar changes the profile photo; the language button switches EN <-> AR.
const { chromium } = require('playwright');
const path = require('path');
const B = process.env.BASE || 'http://127.0.0.1:8080';
let fails = 0; const ok = (c, m) => { console.log((c ? 'ok   ' : 'FAIL ') + m); if (!c) fails++; };
(async () => {
  const b = await chromium.launch();
  const pg = await (await b.newContext({ viewport: { width: 1440, height: 900 } })).newPage();
  const errs = []; pg.on('pageerror', (e) => errs.push(String(e)));
  await pg.goto(B + '/login'); await pg.fill('#email', 'newstudent@ecoop.test'); await pg.fill('#password', 'Coop2026!');
  await Promise.all([pg.waitForNavigation(), pg.click('main button[type=submit]')]);
  ok(await pg.locator('.app-userchip span.co-avatar[data-me]').count() === 1, 'starts with initials avatar');
  const [chooser] = await Promise.all([pg.waitForEvent('filechooser'), pg.click('.app-userchip [data-avatar-pick]')]);
  ok(true, 'pressing the avatar opens the photo picker');
  const resp = pg.waitForResponse((r) => r.url().endsWith('/settings/avatar'));
  await chooser.setFiles(path.join(__dirname, 'sample-avatar.png'));
  const r = await resp; ok(r.status() === 200, 'photo uploaded in the background (200)');
  await pg.waitForTimeout(500);
  ok(await pg.locator('.app-userchip img.co-avatar[data-me]').count() === 1, 'top-bar avatar now shows the photo without reloading');
  await pg.reload();
  const loaded = await pg.locator('.app-userchip img.co-avatar').evaluate((i) => i.complete && i.naturalWidth > 0);
  ok(loaded, 'photo is still there after reload (saved)');
  // language button
  await Promise.all([pg.waitForNavigation(), pg.click('.app-langbtn')]);
  ok(await pg.evaluate(() => document.documentElement.dir) === 'rtl', 'language button switches to Arabic');
  ok((await pg.locator('.app-langbtn').textContent()).includes('English'), 'button now offers English');
  await pg.screenshot({ path: process.argv[2] || '/tmp/ar.png' });
  await Promise.all([pg.waitForNavigation(), pg.click('.app-langbtn')]);
  ok(await pg.evaluate(() => document.documentElement.lang) === 'en', 'and back to English');
  // settings page large avatar uses the same picker
  await pg.goto(B + '/settings');
  const [c2] = await Promise.all([pg.waitForEvent('filechooser'), pg.click('.app-avatar-btn--lg')]);
  ok(!!c2, 'settings photo also opens the picker');
  ok(errs.length === 0, 'no JavaScript errors ' + errs.join(' | '));
  await b.close(); process.exit(fails ? 1 : 0);
})();
