"""Export the whole COOP website as ONE self-contained HTML file (static preview).

Renders every page of every role from the running PHP site (default http://127.0.0.1:8080
with the demo seed), inlines CSS, images and scripts, and rewrites links so every
navigation inside the file lands on a real page.  Usage: python3 tools/export_html.py [out.html]
"""
import base64, http.cookiejar, json, mimetypes, re, subprocess, sys, urllib.parse, urllib.request
from pathlib import Path

BASE = 'http://127.0.0.1:8080'
ROOT = Path(__file__).resolve().parent.parent
PUB = ROOT / 'public'
OUT = Path(sys.argv[1]) if len(sys.argv) > 1 else ROOT / 'coop-website.html'

def q(sql):
    out = subprocess.run(['mysql', '-uroot', '-N', 'coop_db', '-e', sql], capture_output=True, text=True, check=True).stdout
    return [l for l in out.split('\n') if l]

LOGINS = {
    'student': ('student@ecoop.test', 'Coop2026!'),
    'company': ('company@ecoop.test', 'Coop2026!'),
    'supervisor': ('supervisor@ecoop.test', 'Coop2026!'),
    'admin': ('admin@ecoop.test', 'Coop2026!'),
}
uid = {r: q(f"SELECT UserID FROM users WHERE Email='{e}'")[0] for r, (e, _) in LOGINS.items()}
sabic = q("SELECT CompanyID FROM companies WHERE CompanyName='Nova Systems'")[0]
faisal = q("SELECT SupervisorID FROM academic_supervisors sp JOIN users u USING(UserID) WHERE u.Email='supervisor@ecoop.test'")[0]
convs = lambda r: q(f"SELECT ConversationID FROM conversation_members WHERE UserID={uid[r]}")

def applicant_variants():
    opps = q(f"SELECT OpportunityID FROM coop_opportunities WHERE CompanyID={sabic}")
    out = [f'opportunity={o}' for o in opps]
    for st in ('Pending', 'Accepted', 'Rejected', 'Withdrawn'):
        out.append(f'status={st}')
        out += [f'opportunity={o}&status={st}' for o in opps]
    return out

def norm(href):
    """Path plus a canonical query (empty / zero values dropped, keys sorted)."""
    u = urllib.parse.urlparse(href)
    path = u.path.rstrip('/') or '/'
    qs = sorted((k, v) for k, v in urllib.parse.parse_qsl(u.query) if v not in ('', '0') and k in ('status', 'role', 'opportunity'))
    return path + ('?' + urllib.parse.urlencode(qs) if qs else '')

PAGES = {
    'public': ['/', '/about', '/faq', '/privacy', '/contact', '/login', '/register/student', '/register/company', '/register/supervisor', '/forgot-password', '/page-not-found'],
    'student': ['/student/dashboard', '/student/profile', '/student/opportunities',
                *[f'/student/opportunities/{i}' for i in q('SELECT OpportunityID FROM v_public_opportunities')],
                '/student/applications', *[f'/student/applications?status={st}' for st in ('Pending', 'Accepted', 'Rejected', 'Withdrawn')],
                '/student/attendance', '/student/report', '/student/evaluations',
                '/messages', *[f'/messages/{c}' for c in convs('student')], '/notifications', '/settings'],
    'company': ['/company/dashboard', '/company/profile', '/company/opportunities', '/company/opportunities/create',
                *[f'/company/opportunities/{i}/edit' for i in q(f"SELECT OpportunityID FROM coop_opportunities WHERE CompanyID={sabic} AND Status<>'Closed'")],
                '/company/applicants', *[f'/company/applicants?{qs}' for qs in applicant_variants()],
                *[f'/company/applicants/{a}' for a in q(f"SELECT a.ApplicationID FROM applications a JOIN coop_opportunities o USING(OpportunityID) WHERE o.CompanyID={sabic}")],
                '/company/daily-code', '/company/trainees',
                *[f'/company/evaluate/{s}' for s in q(f"SELECT s.StudentID FROM students s JOIN training_placements p USING(PlacementID) JOIN coop_opportunities o USING(OpportunityID) WHERE o.CompanyID={sabic}")],
                '/messages', *[f'/messages/{c}' for c in convs('company')], '/notifications', '/settings'],
    'supervisor': ['/supervisor/dashboard', '/supervisor/students',
                   *[f'/supervisor/students/{s}' for s in q(f'SELECT StudentID FROM students WHERE SupervisorID={faisal}')],
                   '/supervisor/attendance', *[f'/supervisor/attendance?status={st}' for st in ('Pending', 'Approved', 'Rejected')], '/supervisor/reports',
                   *[f'/supervisor/evaluate/{s}' for s in q(f'SELECT StudentID FROM students WHERE SupervisorID={faisal}')],
                   '/supervisor/export', '/messages', *[f'/messages/{c}' for c in convs('supervisor')], '/notifications', '/settings'],
    'admin': ['/admin/dashboard', '/admin/users', *[f'/admin/users?role={r}' for r in ('Student', 'Company', 'Supervisor', 'Admin')],
              *[f'/admin/users/{u}' for u in q('SELECT UserID FROM users')],
              '/admin/companies', *[f'/admin/companies?status={st}' for st in ('Pending', 'Approved', 'Rejected')],
              '/admin/opportunities', *[f'/admin/opportunities?status={urllib.parse.quote_plus(st)}' for st in ('Pending Approval', 'Approved', 'Rejected', 'Closed')],
              '/admin/placements', '/admin/placements?status=Active', '/admin/placements?status=Completed',
              '/admin/assign', '/admin/analytics', '/admin/report',
              '/admin/audit-logs', '/admin/contact-messages', '/messages', *[f'/messages/{c}' for c in convs('admin')], '/notifications', '/settings'],
}
TITLES = {'student': 'Student', 'company': 'Company', 'supervisor': 'Supervisor', 'admin': 'Admin'}

LANG = 'en'
def sid(role, path, lang=None):
    base = (role + '-' + (re.sub(r'[^a-z0-9]+', '-', path.lower()).strip('-') or 'home')).replace('public-', '', 1)
    return base + ('-ar' if (lang or LANG) == 'ar' else '')

KNOWN = {(r, norm(p)): p for r, ps in PAGES.items() for p in ps}

def resolve(role, href):
    if href.startswith('#') or href.startswith('mailto:'):
        return None
    path = urllib.parse.urlparse(href).path.rstrip('/') or '/'
    m = re.fullmatch(r'/files/(cv|report)/(\d+)', path)
    if m:
        col = "CVFilePath FROM students WHERE StudentID" if m.group(1) == 'cv' else "FilePath FROM final_reports WHERE ReportID"
        rel = q(f"SELECT {col}={m.group(2)}")
        return ('FILE', rel[0]) if rel else ''
    if path == '/dashboard':
        return sid(role, f'/{role}/dashboard') if role != 'public' else 'login'
    if re.fullmatch(r'/notifications/\d+/open', path):
        # Follow the notification to its target page if we exported it.
        return 'NOTIF'
    full = norm(href)
    for key in (full, path):
        for r in (role, 'public'):
            if (r, key) in KNOWN:
                return sid(r, KNOWN[(r, key)])
    m = re.fullmatch(r'/(student|company|supervisor|admin)/.*', path)
    if m and (m.group(1), f'/{m.group(1)}/dashboard') in KNOWN:
        return sid(m.group(1), f'/{m.group(1)}/dashboard')
    return ''

openers = {}
def opener(role):
    key = (role, LANG)
    if key in openers:
        return openers[key]
    op = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
    if role != 'public':
        html = op.open(BASE + '/login').read().decode()
        token = re.search(r'name="csrf-token" content="([^"]+)"', html).group(1)
        e, p = LOGINS[role]
        op.open(BASE + '/login', urllib.parse.urlencode({'_csrf': token, 'email': e, 'password': p}).encode())
    op.open(BASE + '/lang/' + LANG)
    openers[key] = op
    return op

ASSETS = {}
FILES = {}
def asset_key(path):
    key = path.split('?')[0]
    if key not in ASSETS:
        f = PUB / key.lstrip('/')
        ASSETS[key] = f'data:{mimetypes.guess_type(str(f))[0]};base64,' + base64.b64encode(f.read_bytes()).decode()
    return key

notif_links = {}
for r in uid:
    rows = q(f"SELECT NotificationID, COALESCE(Link,'/notifications') FROM notifications WHERE UserID={uid[r]}")
    notif_links[r] = dict(l.split('\t', 1) for l in rows)

sections, missing = [], set()
LANG_ITEMS = [(lang, role, path) for lang in ('en', 'ar') for role, paths in PAGES.items() for path in paths]
for LANG, role, path in LANG_ITEMS:
    if True:
        page_id = sid(role, path)
        try:
            resp = opener(role).open(BASE + path)
        except urllib.error.HTTPError as err:
            resp = err  # 404 page
        html = resp.read().decode()
        attrs = re.search(r'<html([^>]*)>', html).group(1)
        body_cls = re.search(r'<body class="([^"]+)"', html).group(1)
        title = re.search(r'<title>(.*?)</title>', html, re.S).group(1)
        body = re.search(r'<body[^>]*>(.*)</body>', html, re.S).group(1)
        body = re.sub(r'<script\b[^>]*>.*?</script>', '', body, flags=re.S)
        body = re.sub(r'<a class="skip-link".*?</a>', '', body, flags=re.S)
        body = re.sub(r'src="(/assets/[^"]+)"', lambda m: f'data-asset="{asset_key(m.group(1))}"', body)

        def link(m, role=role, page_id=page_id):
            href = m.group(1)
            target = resolve(role, href)
            if isinstance(target, tuple):
                rel = target[1]
                f = ROOT / 'storage/uploads' / rel
                FILES.setdefault(rel, base64.b64encode(f.read_bytes()).decode())
                return f'href="#" data-file="{rel}" download="{rel.split("/")[0]}-{Path(rel).name[:8]}{Path(rel).suffix}"'
            if href.startswith('/lang/'):
                return f'href="#{sid(role, path, href[-2:])}"'
            if target is None:
                return m.group(0)
            if target == 'NOTIF':
                nid = re.search(r'/notifications/(\d+)/open', href).group(1)
                target = resolve(role, notif_links.get(role, {}).get(nid, '/notifications')) or sid(role, '/notifications')
            if target == '':
                missing.add(href)
                target = page_id
            return f'href="#{target}"'
        body = re.sub(r'href="(/[^"]*)"', link, body)
        def act(m, role=role):
            tgt = resolve(role, m.group(1))
            extra = f' data-search-target="{tgt}"' if isinstance(tgt, str) and tgt else ''
            return f'action="#" data-action="{m.group(1)}"{extra}'
        body = re.sub(r'\baction="([^"]*)"', act, body)
        def opt_targets(m, role=role, path=path):
            sel = m.group(0)
            base = path.split('?')[0]
            return re.sub(r'<option value="(\d+)"', lambda o: f'<option value="{o.group(1)}" data-target="{resolve(role, base + "?opportunity=" + o.group(1)) or resolve(role, base)}"', sel)
        body = re.sub(r'<select[^>]*data-autosubmit-select.*?</select>', opt_targets, body, flags=re.S)
        body = body.replace(' id="main"', '')
        # The same dialog appears on several pages: give modal ids (and their references) a per-page prefix.
        for mid in set(re.findall(r'<div class="modal[^"]*" id="([^"]+)"', body)) | {'cv-remove'}:
            body = re.sub(r'(?<=["#])' + re.escape(mid) + r'(?=[-"])', f'{page_id}--{mid}', body)
        dir_ = re.search(r'dir="(\w+)"', attrs).group(1)
        theme = re.search(r'data-theme="(\w+)"', attrs).group(1)
        sections.append(f'<section class="page {body_cls}" id="{page_id}" dir="{dir_}" data-theme="{theme}" data-role="{role}" data-title="{title.strip()}" hidden>{body}</section>')

for (r, l), op in openers.items():
    if l == 'ar':
        op.open(BASE + '/lang/en')

LANGS = {l: json.loads(subprocess.run(['php', '-r', f'echo json_encode(require "{ROOT}/app/lang/{l}.php");'], capture_output=True, text=True, check=True).stdout) for l in ('en', 'ar')}

css = ''.join((PUB / 'assets' / p).read_text() for p in
              ['vendor/bootstrap/bootstrap.min.css', 'css/main.css', 'css/dashboard.css', 'css/auth.css', 'css/rtl.css'])
css = css.replace('body {', '.page {').replace('.app-body {', '.page.app-body {').replace('.site-body {', '.page.site-body {')
js = ''.join((PUB / 'assets' / p).read_text() + '\n' for p in
             ['vendor/bootstrap/bootstrap.bundle.min.js', 'vendor/chart.js/chart.umd.min.js'])
charts = (PUB / 'assets/js/charts.js').read_text()
favicon = ASSETS[asset_key('/assets/img/favicon.png')]
dashboards = {r: sid(r, f'/{r}/dashboard', 'en') for r in TITLES}

router = r'''
(function () {
  const pages = [...document.querySelectorAll('section.page')];
  const DASH = %s;
  function show() {
    const id = decodeURIComponent((location.hash || '#home').slice(1));
    const page = document.getElementById(id) || document.getElementById('home');
    pages.forEach((p) => { p.hidden = p !== page; });
    document.title = page.dataset.title;
    window.scrollTo(0, 0);
    window.dispatchEvent(new Event('resize'));
  }
  window.addEventListener('hashchange', show);
  show();

  function toast(msg) {
    const t = document.createElement('div');
    t.className = 'preview-toast'; t.setAttribute('role', 'status'); t.textContent = msg;
    document.body.appendChild(t); setTimeout(() => t.remove(), 3500);
  }
  document.addEventListener('submit', (e) => {
    e.preventDefault();
    const f = e.target, action = f.dataset.action || '';
    const sfx = location.hash.endsWith('-ar') ? '-ar' : '';
    bootstrap.Modal.getInstance(f.closest('.modal'))?.hide();
    if (action.endsWith('/login')) {
      const email = (f.querySelector('[name=email]')?.value || '').toLowerCase();
      location.hash = '#' + (email.startsWith('admin') ? DASH.admin : /supervisor|noura/.test(email) ? DASH.supervisor
        : /company|maaden|gulftech/.test(email) ? DASH.company : DASH.student) + sfx;
    } else if (action.endsWith('/logout')) {
      location.hash = '#login' + sfx;
    } else if (action.endsWith('/theme')) {
      const want = e.submitter && /^(light|dark)$/.test(e.submitter.value) ? e.submitter.value : null;
      setTheme(want || (document.querySelector('section.page:not([hidden])').dataset.theme === 'dark' ? 'light' : 'dark'));
      f.querySelector('button')?.removeAttribute('disabled');
    } else if (action.endsWith('/settings/avatar')) {
      useFile(f.querySelector('input[type=file]').files[0], f.closest('section.page').dataset.role);
    } else if (/register\/(student|company|supervisor)$/.test(action)) {
      location.hash = '#' + (action.endsWith('student') ? DASH.student : action.endsWith('company') ? DASH.company : DASH.supervisor) + sfx;
    } else if ((f.getAttribute('method') || '').toLowerCase() === 'get') {
      window.COOP_SEARCH(f, action);
    } else {
      window.COOP_SIMULATE(f, action);
    }
  }, true);
  // Light / dark theme for the whole preview (remembered in this browser). Dark is the default.
  function setTheme(t) {
    pages.forEach((p) => { p.dataset.theme = t; });
    document.querySelectorAll('input[name=dark_mode]').forEach((i) => { i.checked = t === 'dark'; });
    document.querySelectorAll('.set-theme-form button[name=theme]').forEach((b) => { const on = b.value === t; b.setAttribute('aria-pressed', on ? 'true' : 'false'); b.toggleAttribute('aria-current', on); });
    try { localStorage.setItem('coop-theme', t); } catch (e) {}
    window.dispatchEvent(new Event('resize'));
  }
  try { const saved = localStorage.getItem('coop-theme'); if (saved) setTheme(saved); } catch (e) {}
  window.COOP_SET_THEME = setTheme;
  document.addEventListener('change', (e) => {
    const s = e.target.closest('select[data-autosubmit-select]'); if (!s) return;
    const tgt = s.options[s.selectedIndex].dataset.target; if (tgt) location.hash = '#' + tgt + (location.hash.endsWith('-ar') && !tgt.endsWith('-ar') ? '' : '');
  });

  // Profile photo: press the avatar, pick an image, it shows everywhere for that role (saved in this browser).
  const avatarKey = (role) => 'coop-avatar-' + role;
  function applyAvatar(role, src) {
    document.querySelectorAll(`section.page[data-role="${role}"] [data-me]`).forEach((el) => {
      if (el.tagName === 'IMG') { el.src = src; return; }
      const img = document.createElement('img'); img.className = el.className; img.alt = ''; img.src = src; img.setAttribute('data-me', '');
      el.replaceWith(img);
    });
  }
  function useFile(file, role) {
    if (!file) return;
    if (!/^image\/(jpeg|png|webp)$/.test(file.type) || file.size > 2 * 1024 * 1024) { toast('Use a JPG, PNG or WebP image up to 2 MB.'); return; }
    const r = new FileReader();
    r.onload = () => { applyAvatar(role, r.result); try { localStorage.setItem(avatarKey(role), r.result); } catch (e) {} toast(location.hash.endsWith('-ar') ? 'تم تحديث الصورة.' : 'Photo updated.'); };
    r.readAsDataURL(file);
  }
  ['student', 'company', 'supervisor', 'admin'].forEach((role) => { try { const v = localStorage.getItem(avatarKey(role)); if (v) applyAvatar(role, v); } catch (e) {} });
  document.addEventListener('click', (e) => {
    const pick = e.target.closest('[data-avatar-pick]'); if (!pick) return;
    e.preventDefault();
    pick.closest('section.page').querySelector('#avatar-input')?.click();
  });
  document.addEventListener('change', (e) => {
    const inp = e.target; if (inp.type !== 'file' || inp.name !== 'avatar') return;
    if (inp.id === 'avatar-input') { useFile(inp.files[0], inp.closest('section.page').dataset.role); inp.value = ''; }
  });

  // Daily-code boxes: auto-advance
  document.querySelectorAll('.co-code').forEach((g) => {
    const boxes = [...g.querySelectorAll('input')];
    boxes.forEach((el, i) => el.addEventListener('input', () => { el.value = el.value.replace(/\D/g, '').slice(-1); if (el.value && boxes[i + 1]) boxes[i + 1].focus(); }));
  });
})();
''' % json.dumps(dashboards)

LANG = 'en'
dashboards = {r: sid(r, f'/{r}/dashboard', 'en') for r in TITLES}
doc = f'''<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>COOP · Cooperative Training</title>
<link rel="icon" href="{favicon}" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>{css}
section.page[hidden] {{ display: none !important; }}
.page.site-body {{ min-height: 100vh; }}
.preview-toast {{ position: fixed; z-index: 6000; top: 20px; left: 50%; transform: translateX(-50%); background: #15171c; color: #fff; padding: 10px 18px;
  border-radius: 999px; font: 600 13px/1.2 "Plus Jakarta Sans", system-ui, sans-serif; box-shadow: 0 8px 30px rgba(0,0,0,.25); }}
@media (max-width: 767.98px) {{ </style>
</head>
<body style="margin:0">
{''.join(sections)}
<script>{js}</script>
<script>const ASSETS = {json.dumps(ASSETS)};
document.querySelectorAll('img[data-asset]').forEach((i) => {{ i.src = ASSETS[i.dataset.asset]; }});</script>
<script>const FILES = {json.dumps(FILES)};
document.addEventListener('click', (e) => {{
  const a = e.target.closest('a[data-file]'); if (!a) return;
  e.preventDefault();
  const bin = atob(FILES[a.dataset.file]), bytes = new Uint8Array(bin.length);
  for (let i = 0; i < bin.length; i++) bytes[i] = bin.charCodeAt(i);
  const url = URL.createObjectURL(new Blob([bytes], {{ type: 'application/pdf' }}));
  const t = document.createElement('a'); t.href = url; t.download = a.getAttribute('download'); t.click(); setTimeout(() => URL.revokeObjectURL(url), 1000);
}});</script>
<script>window.COOP_LANG = {json.dumps(LANGS, ensure_ascii=False)};</script>
<script>{(ROOT / 'tools/preview_actions.js').read_text()}</script>
<script>{router}</script>
<script>{charts}</script>
</body>
</html>'''
OUT.write_text(doc)
print(f'Wrote {OUT} ({OUT.stat().st_size // 1024} KB, {len(sections)} pages)')
print('Links with no exported page (kept on the same page):', sorted(missing) or 'none')
