(function () {
  var R = React, h = R.createElement, C = window.COOP;
  var STORE = 'easy-coop-demo-v6';

  /* ---------- lookups ---------- */
  function by(list, id) { for (var i = 0; i < list.length; i++) if (list[i].id === id) return list[i]; return null; }
  function userName(s, uid) { var u = by(s.users, uid); return u ? u.name : ''; }
  function stuName(s, sid) { var st = by(s.students, sid); return st ? userName(s, st.user) : ''; }
  function supUser(s, supId) { return s.users.filter(function (x) { return x.ref === supId; })[0] || null; }
  function supName(s, supId) { var u = supUser(s, supId); return u ? u.name : ''; }
  function oppOfPlacement(s, pid) { var p = by(s.placements, pid); return p ? by(s.opps, p.opp) : null; }
  function compName(s, cid) { var c = by(s.companies, cid); return c ? c.name : ''; }
  function compUser(s, cid) { var c = by(s.companies, cid); return c ? c.user : null; }
  function frac(x, y) { return '⁦' + x + ' / ' + y + '⁩'; }
  function nowTime() { var d = new Date(); return ('0' + d.getHours()).slice(-2) + ':' + ('0' + d.getMinutes()).slice(-2); }
  function fill(str, args) { return String(str).replace(/\{(\w+)\}/g, function (m, k) { return args && args[k] != null ? args[k] : m; }); }
  function initials(name) { var p = String(name).replace(/^Dr\.\s*/, '').split(/\s+/); return ((p[0] || '')[0] || '') + ((p[1] || '')[0] || ''); }
  function attRate(s, sid) {
    var st = by(s.students, sid); if (!st || !st.placement) return null;
    var p = by(s.placements, st.placement);
    var days = workdays(p.start, TODAY < p.end ? TODAY : p.end).length;
    var got = s.attendance.filter(function (a) { return a.student === sid && a.status !== 'Rejected'; }).length;
    return days ? Math.min(100, Math.round(got * 100 / days)) : 0;
  }
  function validFile(f) { return f && /\.(pdf|docx)$/i.test(f.name) && f.size <= 10 * 1024 * 1024; }

  /* ---------- state ---------- */
  function load() { try { var v = localStorage.getItem(STORE); if (v) return JSON.parse(v); } catch (e) {} return seed(); }
  function save(s) { try { localStorage.setItem(STORE, JSON.stringify(s)); } catch (e) {} }
  function lsGet(k, d) { try { var v = localStorage.getItem(STORE + '-' + k); return v == null ? d : v; } catch (e) { return d; } }
  function lsSet(k, v) { try { if (v == null) localStorage.removeItem(STORE + '-' + k); else localStorage.setItem(STORE + '-' + k, v); } catch (e) {} }
  function id(s, p) { s.seq += 1; return p + s.seq; }
  function stamp() { return TODAY + 'T' + nowTime(); }
  function log(s, uid, action) { s.audit.unshift({ id: id(s, 'l'), user: uid, action: action, ts: stamp() }); }
  function notify(s, uid, key, args) { if (uid) s.notifs.unshift({ id: id(s, 'n'), user: uid, key: key, args: args || {}, ts: stamp(), read: false }); }

  function reducer(state, a) {
    if (a.type === 'reset') return seed();
    if (a.type === 'restore') return a.state;
    var s = JSON.parse(JSON.stringify(state));
    switch (a.type) {
      case 'apply': {
        s.apps.unshift({ id: id(s, 'a'), student: a.student, opp: a.opp, status: 'Submitted', date: TODAY });
        var op = by(s.opps, a.opp); notify(s, compUser(s, op.company), 'nNewApp', { n: stuName(s, a.student), o: op.title }); break;
      }
      case 'appDecision': {
        var ap = by(s.apps, a.id), o0 = by(s.opps, ap.opp); ap.status = a.status;
        if (a.status === 'Accepted') {
          var pl = s.placements.filter(function (p) { return p.opp === ap.opp; })[0];
          if (!pl) { pl = { id: id(s, 'p'), opp: ap.opp, start: '2026-10-04', end: '2026-12-24', status: 'Active' }; s.placements.push(pl); }
          by(s.students, ap.student).placement = pl.id;
        }
        notify(s, by(s.students, ap.student).user, a.status === 'Accepted' ? 'nAppAccepted' : 'nAppRejected', { o: o0.title });
        log(s, a.by, (a.status === 'Accepted' ? 'Accepted' : 'Rejected') + ' application from ' + stuName(s, ap.student) + ' for ' + o0.title);
        break;
      }
      case 'postOpp': {
        s.opps.unshift(Object.assign({ id: id(s, 'o'), status: 'Pending Approval', posted: TODAY }, a.opp)); log(s, a.by, 'Posted opportunity ' + a.opp.title);
        s.users.filter(function (u) { return u.role === 'Admin'; }).forEach(function (u) { notify(s, u.id, 'nOppNew', { o: a.opp.title }); });
        break;
      }
      case 'oppDecision': { var o = by(s.opps, a.id); o.status = a.status; notify(s, compUser(s, o.company), a.status === 'Open' ? 'nOppApproved' : 'nOppRejected', { o: o.title }); log(s, a.by, (a.status === 'Open' ? 'Approved' : 'Rejected') + ' opportunity ' + o.title); break; }
      case 'companyDecision': { var c = by(s.companies, a.id); c.status = a.status; log(s, a.by, a.status + ' company ' + c.name); break; }
      case 'genCode': {
        s.codes.forEach(function (k) { if (k.placement === a.placement && k.status === 'Active') k.status = 'Expired'; });
        s.codes.push({ id: id(s, 'k'), placement: a.placement, code: String(100000 + Math.floor(Math.random() * 900000)), date: TODAY, status: 'Active' });
        log(s, a.by, 'Generated daily code for ' + oppOfPlacement(s, a.placement).title); break;
      }
      case 'checkIn': {
        s.attendance.push({ id: id(s, 't'), student: a.student, placement: a.placement, date: TODAY, time: a.time, status: 'Pending' });
        var sv = supUser(s, by(s.students, a.student).supervisor); if (sv) notify(s, sv.id, 'nCheckIn', { n: stuName(s, a.student) }); break;
      }
      case 'attDecision': a.ids.forEach(function (x) { var r = by(s.attendance, x); r.status = a.status; notify(s, by(s.students, r.student).user, a.status === 'Approved' ? 'nAttApproved' : 'nAttRejected', { d: r.date }); }); log(s, a.by, a.status + ' ' + a.ids.length + ' attendance record(s)'); break;
      case 'submitReport': {
        var r = s.reports.filter(function (x) { return x.student === a.student; })[0];
        if (r) { r.file = a.file; r.fileKey = a.fileKey; r.date = TODAY; r.status = 'Submitted'; } else s.reports.push({ id: id(s, 'r'), student: a.student, file: a.file, fileKey: a.fileKey, date: TODAY, status: 'Submitted' });
        var sv2 = supUser(s, by(s.students, a.student).supervisor); if (sv2) notify(s, sv2.id, 'nReportIn', { n: stuName(s, a.student) }); break;
      }
      case 'reportDecision': { var rp = by(s.reports, a.id); rp.status = a.status; notify(s, by(s.students, rp.student).user, a.status === 'Approved' ? 'nReportApproved' : 'nReportRevision'); log(s, a.by, a.status + ' final report of ' + stuName(s, rp.student)); break; }
      case 'submitEval': s.evals.unshift({ id: id(s, 'e'), student: a.student, type: a.evalType, by: a.by, score: a.score, comments: a.comments, date: TODAY }); notify(s, by(s.students, a.student).user, 'nEvalIn', { x: a.evalType === 'Company' ? 'company' : 'academic', s: a.score }); log(s, a.by, 'Submitted ' + a.evalType.toLowerCase() + ' evaluation for ' + stuName(s, a.student)); break;
      case 'userStatus': { var u = by(s.users, a.id); u.status = a.status; log(s, a.by, (a.status === 'Suspended' ? 'Suspended' : 'Activated') + ' account ' + u.name); break; }
      case 'assign': { by(s.students, a.student).supervisor = a.supervisor; notify(s, by(s.students, a.student).user, 'nSupervisor', { n: supName(s, a.supervisor) }); log(s, a.by, 'Assigned ' + supName(s, a.supervisor) + ' to ' + stuName(s, a.student)); break; }
      case 'profile': Object.assign(by(s.students, a.student), a.fields); Object.assign(by(s.users, by(s.students, a.student).user), a.userFields); break;
      case 'readNotifs': s.notifs.forEach(function (n) { if (n.user === a.user) n.read = true; }); break;
      case 'pref': s.prefs[a.user + ':' + a.key] = a.value; break;
      case 'contact': s.users.filter(function (u) { return u.role === 'Admin'; }).forEach(function (u) { notify(s, u.id, 'nContact', { n: a.msg.name, e: a.msg.email, x: a.msg.topic, m: a.msg.msg.length > 120 ? a.msg.msg.slice(0, 117) + '\u2026' : a.msg.msg }); }); break;
      case 'register': {
        s.users.push({ id: a.uid, name: a.name, email: a.email, role: a.kind, status: 'Active', ref: a.ref, phone: a.phone, dept: a.kind === 'Supervisor' ? a.dept : undefined, pwHash: a.pwHash, pwSalt: a.pwSalt });
        if (a.kind === 'Student') s.students.push({ id: a.ref, user: a.uid, major: a.major, gpa: a.gpa, phone: a.phone, cv: '', supervisor: null, placement: null });
        else if (a.kind === 'Company') s.companies.push({ id: a.ref, user: a.uid, name: a.name, city: a.city, status: 'Pending Approval' });
        notify(s, a.uid, 'nWelcome', {});
        s.users.filter(function (u) { return u.role === 'Admin' && u.id !== a.uid; }).forEach(function (u) { notify(s, u.id, a.kind === 'Company' ? 'nCompanyPending' : a.kind === 'Supervisor' ? 'nNewSupervisor' : a.kind === 'Admin' ? 'nNewAdmin' : 'nNewStudent', { n: a.name, dp: a.dept, e: a.email }); });
        log(s, a.uid, 'Created ' + a.kind.toLowerCase() + ' account ' + a.name);
        break;
      }
      case 'companyProfile': {
        var cp = by(s.companies, a.company), cu = by(s.users, a.user);
        cp.name = a.name; cp.city = a.city; cu.name = a.name; cu.phone = a.phone;
        log(s, a.user, 'Updated company profile ' + a.name); break;
      }
      case 'logExport': log(s, a.by, 'Exported ' + a.report + ' report (' + a.format + ')'); break;
      case 'avatar': by(s.users, a.user).avatar = a.photo || null; break;
      case 'sendMsg': { var ts = stamp(); s.msgs.push({ id: id(s, 'm'), chat: a.chat, from: a.from, text: a.text, ts: ts }); s.reads[a.from + ':' + a.chat] = ts; break; }
      case 'readChat': { var ms = s.msgs.filter(function (m) { return m.chat === a.chat; }); s.reads[a.user + ':' + a.chat] = ms.length ? ms[ms.length - 1].ts : stamp(); break; }
      case 'newChat': s.chats.push({ id: id(s, 'ch'), kind: 'direct', members: a.members }); break;
    }
    return s;
  }

  /* ---------- context + formatting ---------- */
  var Ctx = R.createContext(null);
  function useApp() { return R.useContext(Ctx); }
  var MON = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  function fmtDate(d, lang) {
    if (!d) return '';
    var y = d.slice(0, 4), m = +d.slice(5, 7), day = +d.slice(8, 10);
    if (lang !== 'ar') return day + ' ' + MON[m - 1] + ' ' + y;
    try { return new Intl.DateTimeFormat('ar-u-nu-latn-ca-gregory', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC' }).format(new Date(d.slice(0, 10) + 'T12:00:00Z')); } catch (e) { return d.slice(0, 10); }
  }
  function longToday(lang) {
    try { return new Intl.DateTimeFormat(lang === 'ar' ? 'ar-u-nu-latn-ca-gregory' : 'en-GB', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC' }).format(new Date(TODAY + 'T12:00:00Z')); } catch (e) { return TODAY; }
  }
  function relDay(d, t) {
    var diff = Math.round((new Date(TODAY + 'T12:00:00Z') - new Date(d.slice(0, 10) + 'T12:00:00Z')) / 864e5);
    if (diff <= 0) return t('today'); if (diff === 1) return t('yesterday'); return fill(t('daysAgo'), { n: diff });
  }
  function greeting(t) { var hr = new Date().getHours(); return hr < 12 ? t('goodMorning') : hr < 17 ? t('goodAfternoon') : t('goodEvening'); }

  /* ---------- icons (outline, 24 grid) ---------- */
  var IC = {
    check: 'M20 6L9 17l-5-5', clock: 'M12 7v5l3 2M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18z', brief: 'M4 8h16v11H4zM9 8V5h6v3M4 13h16',
    doc: 'M6 3h9l3 3v15H6zM9 11h6M9 15h6', star: 'M12 3l2.6 5.6 6 .7-4.5 4.1 1.2 6-5.3-3-5.3 3 1.2-6L3.4 9.3l6-.7z', chart: 'M4 20h16M7 16v-5M12 16V7M17 16v-8',
    users: 'M9 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7zM2.5 20c.6-3.4 3.2-5.5 6.5-5.5s5.9 2.1 6.5 5.5M16 4.5a3.5 3.5 0 0 1 0 6.5M18 14.8c1.9.7 3.2 2.6 3.5 5.2',
    shield: 'M12 3l7 3v5c0 4.5-3 8.3-7 10-4-1.7-7-5.5-7-10V6z', bell: 'M6 16V11a6 6 0 1 1 12 0v5l1.5 2h-15zM10 20.5a2 2 0 0 0 4 0',
    gear: 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM19.4 13.5l1.6 1.2-2 3.4-1.9-.7a7.6 7.6 0 0 1-2 1.1L14.8 21h-4l-.3-2a7.6 7.6 0 0 1-2-1.1l-1.9.7-2-3.4 1.6-1.2a7.7 7.7 0 0 1 0-2.3L4.6 10.5l2-3.4 1.9.7a7.6 7.6 0 0 1 2-1.1l.3-2.2h4l.3 2.2a7.6 7.6 0 0 1 2 1.1l1.9-.7 2 3.4-1.6 1.2a7.7 7.7 0 0 1 0 2.3z',
    plus: 'M12 5v14M5 12h14', key: 'M4 5h16v14H4zM8 10v4M12 10v4M16 10v4', x: 'M6 6l12 12M18 6L6 18', search: 'M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14zM20 20l-4-4',
    minus: 'M5 12h14', camera: 'M4 8h3l2-2.5h6L17 8h3v11H4zM12 16.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z', chat: 'M4 5h16v11H9l-5 4zM8 9.5h8M8 12.5h5', send: 'M4 12l16-8-6 16-3-7z', upload: 'M12 16V4M7 9l5-5 5 5M4 20h16', list: 'M8 6h12M8 12h12M8 18h12M4 6h.01M4 12h.01M4 18h.01'
  };
  function Icon(p) { return h('svg', { className: 'ic' + (p.className ? ' ' + p.className : ''), viewBox: '0 0 24 24', width: p.size || 20, height: p.size || 20, 'aria-hidden': true }, h('path', { d: IC[p.name] })); }
  function Avatar(p) {
    var app = useApp(), photo = p.photo;
    if (!photo && app) { var u = app.state.users.filter(function (x) { return x.name === p.name; })[0]; photo = u && u.avatar; }
    var cls = 'avatar' + (p.size ? ' avatar-' + p.size : '');
    return photo ? h('img', { className: cls + ' avatar-img', src: photo, alt: '' }) : h('span', { className: cls, 'aria-hidden': true }, initials(p.name));
  }
  /* crop the chosen image to a centred square and shrink it to 256px so it fits in browser storage */
  function readAvatar(file) {
    return new Promise(function (res, rej) {
      if (!file || !/^image\/(png|jpe?g|webp)$/.test(file.type)) return rej(new Error('type'));
      if (file.size > 5 * 1024 * 1024) return rej(new Error('size'));
      var url = URL.createObjectURL(file), img = new Image();
      img.onload = function () {
        var side = Math.min(img.naturalWidth, img.naturalHeight), c = document.createElement('canvas'); c.width = c.height = 256;
        c.getContext('2d').drawImage(img, (img.naturalWidth - side) / 2, (img.naturalHeight - side) / 2, side, side, 0, 0, 256, 256);
        URL.revokeObjectURL(url); res(c.toDataURL('image/jpeg', 0.86));
      };
      img.onerror = function () { URL.revokeObjectURL(url); rej(new Error('read')); };
      img.src = url;
    });
  }

  /* ---------- building blocks ---------- */
  function Badge(p) { var app = useApp(); return h(C.StatusBadge, { status: p.status }, app.lang === 'ar' ? (STATUS_AR[p.status] || p.status) : p.status); }
  function Page(p) {
    return h('div', { className: 'page' },
      h('section', { className: 'hero' },
        h('div', { className: 'wrap hero-in' },
          h('div', { className: 'hero-text' }, p.eyebrow ? h('p', { className: 'hero-eyebrow' }, p.eyebrow) : null, h('h1', { className: 'hero-title' }, p.title), p.lead ? h('p', { className: 'hero-lead' }, p.lead) : null),
          p.action ? h('div', { className: 'hero-action' }, p.action) : null,
          p.extra ? h('div', { className: 'hero-extra' }, p.extra) : null)),
      h('div', { className: 'wrap content' }, p.children));
  }
  function Section(p) { return h('section', { className: 'section' }, h('div', { className: 'section-head' }, h('div', null, h('h2', { className: 'h2' }, p.title), p.lead ? h('p', { className: 'section-lead' }, p.lead) : null), p.aside || null), p.children); }
  function Empty(p) { return h('div', { className: 'empty' }, h('p', { className: 'empty-title' }, p.title), p.children ? h('div', { className: 'empty-body' }, p.children) : null); }
  function Grid(p) { return h('div', { className: 'grid grid-' + (p.min || 'md') }, p.children); }
  function Row(p) { return h('div', { className: 'row' + (p.end ? ' row-end' : '') }, p.children); }
  function Card(p) { return h('div', { className: 'card' + (p.className ? ' ' + p.className : '') }, p.children); }

  function Tiles(p) {
    return h('nav', { className: 'tiles', 'aria-label': p.label },
      p.items.map(function (it, i) {
        return h('button', { key: it.label, className: 'tile' + (it.wide ? ' tile-wide' : '') + (i % 4 === 1 || i % 4 === 2 ? ' tile-b' : ''), onClick: it.onClick },
          h(Icon, { name: it.icon, size: 22 }), h('span', { className: 'tile-label' }, it.label), it.count ? h('span', { className: 'tile-count' }, it.count) : null);
      }));
  }

  function HeroProgress(p) {
    return h('div', { className: 'glass' },
      h('div', { className: 'glass-top' }, h('span', { className: 'glass-label' }, p.label), h('span', { className: 'glass-value' }, p.value)),
      p.pct != null ? h('div', { className: 'glass-bar', role: 'progressbar', 'aria-valuenow': p.pct, 'aria-valuemin': 0, 'aria-valuemax': 100, 'aria-label': p.label }, h('span', { style: { width: p.pct + '%' } })) : null,
      p.note ? h('p', { className: 'glass-note' }, p.note) : null);
  }

  function Feed(p) {
    var app = useApp(), t = app.t;
    if (!p.items.length) return h(Empty, { title: t('noActivity') });
    return h('ol', { className: 'feed' }, p.items.map(function (it) {
      return h('li', { key: it.key, className: 'feed-item feed-' + (it.tone || 'neutral') },
        h('span', { className: 'feed-dot' }, h(Icon, { name: it.icon, size: 18 })),
        h('div', { className: 'feed-body' },
          h('div', { className: 'feed-top' }, it.who ? h('span', { className: 'feed-who' }, h(Avatar, { name: it.who, size: 'sm' }), it.who) : h('span'), h('span', { className: 'feed-when' }, relDay(it.date, t) + (it.time ? ' · ' + it.time : ''))),
          h('p', { className: 'feed-text' }, it.text)));
    }));
  }

  function activityFor(app, scope) {
    var s = app.state, t = app.t, items = [];
    var inScope = scope.students ? function (sid) { return scope.students.indexOf(sid) >= 0; } : function () { return true; };
    s.attendance.forEach(function (a) { if (inScope(a.student) && (!scope.pids || scope.pids.indexOf(a.placement) >= 0)) items.push({ key: a.id, icon: 'check', tone: a.status === 'Rejected' ? 'danger' : 'success', who: scope.self ? null : stuName(s, a.student), date: a.date, time: a.time, text: scope.self ? fill(t('aYouCheckIn'), { x: a.time }) : fill(t('aCheckIn'), { n: stuName(s, a.student), x: a.time }) }); });
    s.apps.forEach(function (a) { var o = by(s.opps, a.opp); if (inScope(a.student) && (!scope.oppIds || scope.oppIds.indexOf(a.opp) >= 0)) items.push({ key: a.id, icon: 'brief', tone: a.status === 'Accepted' ? 'success' : a.status === 'Rejected' ? 'danger' : 'info', who: stuName(s, a.student), date: a.date, text: fill(t(a.status === 'Accepted' ? 'aAccepted' : a.status === 'Rejected' ? 'aRejectedApp' : 'aApplied'), { n: stuName(s, a.student), o: o.title }) }); });
    s.reports.forEach(function (r) { if (inScope(r.student) && !scope.oppIds) items.push({ key: r.id, icon: 'doc', tone: 'info', who: stuName(s, r.student), date: r.date, text: fill(t('aReport'), { n: stuName(s, r.student) }) }); });
    s.evals.forEach(function (e) { if (inScope(e.student) && (!scope.evalBy || scope.evalBy === e.by)) items.push({ key: e.id, icon: 'star', tone: 'warning', who: stuName(s, e.student), date: e.date, text: fill(t('aEval'), { n: stuName(s, e.student), x: e.type === 'Company' ? t('companyEval') : t('academicEval'), s: e.score }) }); });
    items.sort(function (a, b) { var x = a.date + (a.time || '00:00'), y = b.date + (b.time || '00:00'); return x < y ? 1 : x > y ? -1 : 0; });
    return items.slice(0, scope.limit || 6);
  }

  /* bottom sheet / dialog */
  function Sheet(p) {
    var app = useApp(), ref = R.useRef(null);
    R.useEffect(function () {
      var prev = document.activeElement; if (ref.current) ref.current.focus();
      function onKey(e) { if (e.key === 'Escape') p.onClose(); }
      document.addEventListener('keydown', onKey);
      return function () { document.removeEventListener('keydown', onKey); if (prev && prev.focus) prev.focus(); };
    }, []);
    return h('div', { className: 'sheet-wrap', onClick: function (e) { if (e.target === e.currentTarget) p.onClose(); } },
      h('div', { className: 'sheet', role: 'dialog', 'aria-modal': 'true', 'aria-label': p.label, tabIndex: -1, ref: ref },
        h('div', { className: 'sheet-grip', 'aria-hidden': true }),
        p.head || null,
        h('div', { className: 'sheet-body' }, p.children),
        p.hideClose ? null : h('div', { className: 'sheet-foot' }, h(C.Button, { variant: 'secondary', block: true, onClick: p.onClose }, app.t('close')))));
  }

  function Stepper(p) {
    var app = useApp(), t = app.t;
    return h('div', { className: 'coop-field' },
      h('span', { className: 'coop-label', id: p.id + '-l' }, p.label),
      h('div', { className: 'stepper', role: 'group', 'aria-labelledby': p.id + '-l' },
        h('button', { type: 'button', className: 'step-btn', 'aria-label': t('decrease'), disabled: p.value <= p.min, onClick: function () { p.onChange(p.value - 1); } }, h(Icon, { name: 'minus', size: 18 })),
        h('output', { className: 'step-val', 'aria-live': 'polite' }, p.value),
        h('button', { type: 'button', className: 'step-btn', 'aria-label': t('increase'), disabled: p.value >= p.max, onClick: function () { p.onChange(p.value + 1); } }, h(Icon, { name: 'plus', size: 18 }))),
      p.help ? h('span', { className: 'coop-help' }, p.help) : null);
  }
  function Segmented(p) {
    return h('div', { className: 'coop-field' },
      h('span', { className: 'coop-label', id: p.id + '-l' }, p.label),
      h('div', { className: 'seg', role: 'radiogroup', 'aria-labelledby': p.id + '-l' }, p.options.map(function (o) {
        return h('button', { key: o.value, type: 'button', role: 'radio', 'aria-checked': p.value === o.value ? 'true' : 'false', className: 'seg-btn', onClick: function () { p.onChange(o.value); } }, o.label);
      })));
  }
  function Switch(p) {
    return h('div', { className: 'switch-row' },
      h('div', { className: 'switch-text' }, h('span', { className: 'switch-title', id: p.id + '-t' }, p.title), p.desc ? h('span', { className: 'switch-desc' }, p.desc) : null),
      h('button', { type: 'button', role: 'switch', className: 'switch', 'aria-checked': p.on ? 'true' : 'false', 'aria-labelledby': p.id + '-t', onClick: function () { p.onChange(!p.on); } }, h('span', { className: 'switch-knob' })));
  }

  /* live validation: errors show once a field is touched or the form was submitted */
  function useForm(initial, validate) {
    var v = R.useState(initial), touched = R.useState({}), submitted = R.useState(false);
    var errs = validate(v[0]);
    return {
      values: v[0], errors: errs,
      err: function (k) { return (touched[0][k] || submitted[0]) ? errs[k] : null; },
      set: function (k, val) { var n = Object.assign({}, v[0]); n[k] = val; v[1](n); var tt = Object.assign({}, touched[0]); tt[k] = true; touched[1](tt); },
      setAll: function (obj) { v[1](Object.assign({}, v[0], obj)); },
      submit: function () { submitted[1](true); return Object.keys(errs).filter(function (k) { return errs[k]; }).length === 0; },
      reset: function (val) { v[1](val || initial); touched[1]({}); submitted[1](false); },
      tried: submitted[0]
    };
  }

  /* ================= STUDENT ================= */
  /* student's overall COOP progress: placement 20%, training days 50%, final report 15%, two evaluations 15% */
  function studentProgress(s, st) {
    var pl = st.placement ? by(s.placements, st.placement) : null;
    var total = pl ? workdays(pl.start, pl.end).length : 0;
    var done = pl ? Math.min(total, workdays(pl.start, TODAY < pl.end ? TODAY : pl.end).length) : 0;
    var rep = s.reports.filter(function (r) { return r.student === st.id; })[0];
    var evals = s.evals.filter(function (e) { return e.student === st.id; });
    var evalTypes = ['Company', 'Academic'].filter(function (ty) { return evals.some(function (e) { return e.type === ty; }); }).length;
    var today = s.attendance.filter(function (x) { return x.student === st.id && x.date === TODAY; })[0];
    var pct = (pl ? 20 : 0) + (total ? 50 * done / total : 0) + (rep ? (rep.status === 'Approved' ? 15 : rep.status === 'Submitted' ? 7.5 : 0) : 0) + evalTypes * 7.5;
    return { pl: pl, total: total, done: done, rep: rep, evalTypes: evalTypes, today: today, pct: Math.round(pct) };
  }
  function MyProgress(p) {
    var app = useApp(), s = app.state, t = app.t, g = studentProgress(s, p.st);
    var steps = [
      { label: t('stepPlacement'), value: g.pl ? t('stepDone') : t('stepNotYet'), state: g.pl ? 'done' : 'now' },
      { label: t('stepTraining'), value: g.pl ? frac(g.done, g.total) + ' ' + t('stepDays') : '—', state: !g.pl ? 'todo' : g.done >= g.total ? 'done' : 'now' },
      { label: t('stepReport'), value: g.rep ? (app.lang === 'ar' ? (STATUS_AR[g.rep.status] || g.rep.status) : g.rep.status) : t('stepNotYet'), state: g.rep && g.rep.status === 'Approved' ? 'done' : g.rep ? 'now' : 'todo' },
      { label: t('stepEvals'), value: frac(g.evalTypes, 2), state: g.evalTypes === 2 ? 'done' : g.evalTypes ? 'now' : 'todo' }
    ];
    var next = !g.pl ? t('nextApply') : !g.today && g.done < g.total ? t('nextCheckIn') : g.rep && g.rep.status === 'Request Revision' ? t('nextRevision') : g.done < g.total ? t('nextKeepGoing') : !g.rep ? t('nextReport') : t('nextEvals');
    return h('div', { className: 'glass progress-card' },
      h('div', { className: 'glass-top' }, h('span', { className: 'glass-label' }, t('yourProgress')), h('span', { className: 'glass-value' }, g.pct + '%')),
      h('div', { className: 'glass-bar', role: 'progressbar', 'aria-valuenow': g.pct, 'aria-valuemin': 0, 'aria-valuemax': 100, 'aria-label': t('yourProgress') }, h('span', { style: { width: g.pct + '%' } })),
      h('ol', { className: 'psteps' }, steps.map(function (x) {
        return h('li', { key: x.label, className: 'pstep pstep-' + x.state },
          h('span', { className: 'pstep-dot', 'aria-hidden': true }, x.state === 'done' ? h(Icon, { name: 'check', size: 14 }) : null),
          h('span', { className: 'pstep-text' }, h('span', { className: 'pstep-label' }, x.label), h('span', { className: 'pstep-val' }, x.value)));
      })),
      h('p', { className: 'glass-note' }, h('b', null, t('nextStep') + ': '), next));
  }

  function StudentDashboard() {
    var app = useApp(), s = app.state, t = app.t, st = by(s.students, app.me.ref);
    var opp = st.placement ? oppOfPlacement(s, st.placement) : null;
    var today = s.attendance.filter(function (x) { return x.student === st.id && x.date === TODAY; })[0];
    var report = s.reports.filter(function (x) { return x.student === st.id; })[0];
    var myApps = s.apps.filter(function (x) { return x.student === st.id; });
    var rate = attRate(s, st.id);
    var first = app.me.name.split(' ')[0];
    var extra = h(MyProgress, { st: st });
    var tiles = opp ? [
      { icon: 'key', label: t('logAttendance'), onClick: function () { app.go('attendance'); } },
      { icon: 'doc', label: t('navReport'), onClick: function () { app.go('report'); } },
      { icon: 'brief', label: t('navOpportunities'), onClick: function () { app.go('opportunities'); } },
      { icon: 'chat', label: t('navChat'), count: totalUnread(s, app.me.id) || null, onClick: function () { app.go('chat'); } }]
      : [{ icon: 'search', label: t('browseOpps'), onClick: function () { app.go('opportunities'); } }, { icon: 'list', label: t('navApplications'), count: myApps.length || null, onClick: function () { app.go('applications'); } }];
    return h(Page, { eyebrow: longToday(app.lang), title: greeting(t) + ', ' + first, lead: st.major, extra: extra },
      h(Tiles, { label: t('quickActions'), items: tiles }),
      opp ? h(R.Fragment, null,
        !today ? h(C.Alert, { tone: 'warning', title: t('checkInToday') }, h('button', { className: 'link', onClick: function () { app.go('attendance'); } }, t('logAttendance'))) : null,
        report && report.status === 'Request Revision' ? h(C.Alert, { tone: 'danger', title: t('revisionNote') }) : null,
        h('div', { className: 'split' },
          h(Section, { title: t('yourPlacement') },
            h(C.OpportunityCard, { title: opp.title, company: compName(s, opp.company), description: opp.description, city: opp.city, majors: opp.majors, labels: { city: t('city'), majors: t('majors'), capacity: t('seats') },
              status: 'Active', footnote: fmtDate(by(s.placements, st.placement).start, app.lang) + ' – ' + fmtDate(by(s.placements, st.placement).end, app.lang) }),
            h(Card, { className: 'mini-row' }, h('span', { className: 'coop-label' }, t('supervisor')), h('span', { className: 'who' }, h(Avatar, { name: supName(s, st.supervisor) || '?', size: 'sm' }), supName(s, st.supervisor) || t('unassigned'))),
            h(Card, { className: 'mini-row' }, h('span', { className: 'coop-label' }, t('finalReport')), h(Badge, { status: report ? report.status : 'Not submitted' }))),
          h(Section, { title: t('activity') }, h(Feed, { items: activityFor(app, { students: [st.id], self: true }) }))))
      : h(R.Fragment, null,
        h(Empty, { title: t('noPlacementTitle') }, h('p', null, t('noPlacementBody'))),
        myApps.length ? h(Section, { title: t('myApplications') }, h(AppsTable, { apps: myApps })) : null));
  }

  function AppsTable(p) {
    var app = useApp(), s = app.state, t = app.t;
    return h(C.DataTable, { caption: t('myApplications'), empty: t('noApps'), columns: [
      { key: 'opp', header: t('opportunity') }, { key: 'company', header: t('company') }, { key: 'date', header: t('appliedOn') },
      { key: 'status', header: t('status'), render: function (r) { return h(Badge, { status: r.status }); } }],
      rows: p.apps.map(function (a) { var o = by(s.opps, a.opp); return { id: a.id, opp: o.title, company: compName(s, o.company), date: fmtDate(a.date, app.lang), status: a.status }; }) });
  }

  function StudentOpportunities() {
    var app = useApp(), s = app.state, t = app.t, st = by(s.students, app.me.ref);
    var q = R.useState(''), major = R.useState(st.major), city = R.useState('');
    var visible = s.opps.filter(function (o) { var c = by(s.companies, o.company); return o.status === 'Open' && c.status === 'Approved'; });
    var majors = [], cities = [];
    visible.forEach(function (o) { o.majors.split(', ').forEach(function (m) { if (majors.indexOf(m) < 0) majors.push(m); }); if (cities.indexOf(o.city) < 0) cities.push(o.city); });
    var list = visible.filter(function (o) {
      var text = (o.title + ' ' + compName(s, o.company)).toLowerCase();
      return (!q[0] || text.indexOf(q[0].toLowerCase()) >= 0) && (!major[0] || o.majors.indexOf(major[0]) >= 0) && (!city[0] || o.city === city[0]);
    });
    var allOpt = { value: '', label: t('all') };
    return h(Page, { title: t('browseOpps'), lead: list.length + ' ' + t('oppsFound') },
      st.placement ? h(C.Alert, { tone: 'info' }, t('alreadyPlaced')) : null,
      h(Card, { className: 'filters' },
        h(C.TextField, { id: 'f-q', label: t('searchOpps'), value: q[0], onChange: function (e) { q[1](e.target.value); }, type: 'search' }),
        h(C.TextField, { id: 'f-major', label: t('filterMajor'), value: major[0], onChange: function (e) { major[1](e.target.value); }, options: [allOpt].concat(majors.sort()) }),
        h(C.TextField, { id: 'f-city', label: t('filterCity'), value: city[0], onChange: function (e) { city[1](e.target.value); }, options: [allOpt].concat(cities.sort()) })),
      list.length ? h(Grid, null, list.map(function (o) {
        var mine = s.apps.filter(function (a) { return a.student === st.id && a.opp === o.id; })[0];
        var taken = s.placements.filter(function (p) { return p.opp === o.id; }).map(function (p) { return s.students.filter(function (x) { return x.placement === p.id; }).length; })[0] || 0;
        return h(C.OpportunityCard, { key: o.id, title: o.title, company: compName(s, o.company), description: o.description, city: o.city, majors: o.majors, capacity: frac(o.capacity - taken, o.capacity),
          labels: { city: t('city'), majors: t('majors'), capacity: t('seats') }, status: mine ? mine.status : null,
          footnote: t('postedOn') + ' ' + fmtDate(o.posted, app.lang),
          actions: mine ? h(C.Button, { size: 'sm', variant: 'secondary', onClick: function () { app.go('applications'); } }, t('viewApplication'))
            : h(C.Button, { size: 'sm', disabled: !!st.placement || taken >= o.capacity, onClick: function () { app.act({ type: 'apply', student: st.id, opp: o.id }, t('appliedToast'), 'success', true); } }, taken >= o.capacity ? t('full') : t('apply')) });
      })) : h(Empty, { title: t('noOppsMatch') }));
  }

  function StudentApplications() {
    var app = useApp(), t = app.t;
    return h(Page, { title: t('myApplications') }, h(AppsTable, { apps: app.state.apps.filter(function (a) { return a.student === app.me.ref; }) }));
  }

  function StudentAttendance() {
    var app = useApp(), s = app.state, t = app.t, st = by(s.students, app.me.ref);
    var res = R.useState(null), nonce = R.useState(0);
    if (!st.placement) return h(Page, { title: t('attendanceTitle') }, h(Empty, { title: t('noPlacementAttendance') }, h(C.Button, { onClick: function () { app.go('opportunities'); } }, t('browseOpps'))));
    var mine = s.attendance.filter(function (a) { return a.student === st.id; }).slice().reverse();
    var todayRec = mine.filter(function (a) { return a.date === TODAY; })[0];
    var p = by(s.placements, st.placement), dayN = workdays(p.start, TODAY).length, dayM = workdays(p.start, p.end).length;
    function submit(code) {
      var codes = s.codes.filter(function (k) { return k.placement === st.placement; });
      var active = codes.filter(function (k) { return k.status === 'Active' && k.date === TODAY; })[0];
      var match = codes.filter(function (k) { return k.code === code; })[0];
      var err = null;
      if (todayRec) err = t('codeDuplicate');
      else if (match && match.status === 'Expired') err = t('codeExpired');
      else if (!active) err = t('codeNone');
      else if (!match || match.id !== active.id) err = t('codeInvalid');
      if (err) { res[1]({ error: err }); nonce[1](nonce[0] + 1); return; }
      var tm = nowTime();
      app.act({ type: 'checkIn', student: st.id, placement: st.placement, time: tm }, t('codeOk') + ' ' + tm, 'success', false);
      res[1]({ success: t('codeOk') + ' ' + tm + '. ' + t('awaitingApproval') });
    }
    var rate = attRate(s, st.id);
    return h(Page, { eyebrow: fill(t('trainingDay'), { n: dayN, m: dayM }), title: t('attendanceTitle'), lead: oppOfPlacement(s, st.placement).title + ' · ' + compName(s, oppOfPlacement(s, st.placement).company),
      extra: h(HeroProgress, { label: t('termProgress'), value: rate + '%', pct: rate, note: mine.length + ' ' + t('daysLogged').toLowerCase() }) },
      h(Card, { className: 'stack checkin' },
        todayRec && !(res[0] && res[0].success) ? h(C.Alert, { tone: 'success', title: t('checkedInToday') + ' ' + todayRec.time }, t('awaitingApproval')) : null,
        h(C.DailyCodeInput, { key: nonce[0], label: t('codeLabel'), help: t('codeHelp'), error: res[0] && res[0].error, success: res[0] && res[0].success, disabled: !!(res[0] && res[0].success),
          digitLabel: app.lang === 'ar' ? 'الرقم' : 'Digit', onChange: function () { if (res[0] && res[0].error) res[1](null); }, onComplete: submit }),
        h('p', { className: 'demo-note' }, h('span', { className: 'demo-tag' }, t('demo')), t('demoCodeHint'))),
      h(Section, { title: t('history') },
        h(C.DataTable, { caption: t('history'), columns: [{ key: 'date', header: t('date') }, { key: 'time', header: t('checkInTime') }, { key: 'status', header: t('status'), render: function (r) { return h(Badge, { status: r.status }); } }],
          rows: mine.slice(0, 12).map(function (a) { return { id: a.id, date: fmtDate(a.date, app.lang), time: a.time, status: a.status }; }) })));
  }

  function EvalList(p) {
    var app = useApp(), s = app.state, t = app.t;
    if (!p.evals.length) return h(Empty, { title: p.empty || t('noEvals') });
    return h('div', { className: 'eval-list' }, p.evals.map(function (e) {
      return h(Card, { key: e.id, className: 'eval' },
        h('div', { className: 'eval-top' }, h('div', null, p.showStudent ? h('span', { className: 'who' }, h(Avatar, { name: stuName(s, e.student), size: 'sm' }), stuName(s, e.student)) : null,
          h('span', { className: 'eval-type' }, (e.type === 'Company' ? t('companyEval') : t('academicEval')) + ' · ' + fmtDate(e.date, app.lang))),
          h('span', { className: 'eval-score' + (e.score < 60 ? ' low' : '') }, h('b', null, e.score), '/100', e.score < 60 ? h('span', { className: 'low-tag' }, t('belowPass')) : null)),
        h('div', { className: 'bar' + (e.score < 60 ? ' bar-low' : '') }, h('span', { style: { width: e.score + '%' } })),
        h('p', { className: 'eval-text' }, e.comments));
    }));
  }

  function StudentReport() {
    var app = useApp(), s = app.state, t = app.t, st = by(s.students, app.me.ref);
    var rep = s.reports.filter(function (r) { return r.student === st.id; })[0];
    var file = R.useState(null), err = R.useState(null), viewing = R.useState(false), busy = R.useState(false);
    var evals = s.evals.filter(function (e) { return e.student === st.id; });
    function doSubmit() {
      var f = file[0], key = 'f' + Date.now(); busy[1](true);
      FileStore.put(key, f.file).then(function () { app.act({ type: 'submitReport', student: st.id, file: f.name, fileKey: key }, t('reportSubmitted'), 'success', true); file[1](null); busy[1](false); },
        function () { busy[1](false); err[1](t('storageError')); });
    }
    var canSubmit = !!st.placement && (!rep || rep.status === 'Request Revision');
    return h(Page, { title: t('reportTitle'), lead: t('reportLead') },
      !st.placement ? h(Empty, { title: t('noPlacementAttendance') }) : h(Card, { className: 'stack narrow' },
        rep ? h('div', { className: 'kv' }, h(FileButton, { file: rep.file, label: t('openFile'), openText: t('openReport'), onClick: function () { viewing[1](true); } }), h(Badge, { status: rep.status })) : null,
        rep && rep.status === 'Approved' ? h(C.Alert, { tone: 'success', title: t('reportApproved') }) : null,
        rep && rep.status === 'Request Revision' ? h(C.Alert, { tone: 'warning', title: t('revisionNote') }) : null,
        canSubmit ? h(R.Fragment, null,
          h(C.FileUpload, { label: t('reportFile'), prompt: t('chooseFile'), hint: t('fileHint'), removeLabel: t('remove'), file: file[0], error: err[0],
            onFile: function (f) { if (!validFile(f)) { err[1](t('fileTypeError')); file[1](null); } else { err[1](null); file[1]({ name: f.name, file: f }); } },
            onRemove: function () { file[1](null); } }),
          h(Row, { end: true }, h(C.Button, { disabled: !file[0] || busy[0], onClick: doSubmit }, t('submitReport')))) : null),
      viewing[0] && rep ? h(ReportViewer, { reportId: rep.id, onClose: function () { viewing[1](false); } }) : null,
      h(Section, { title: t('evaluations') }, h(EvalList, { evals: evals })));
  }

  function ProfileForm() {
    var app = useApp(), t = app.t, st = by(app.state.students, app.me.ref);
    var f = useForm({ name: app.me.name, phone: st.phone, gpa: st.gpa, cv: st.cv ? { name: st.cv } : null, cvErr: null }, function (v) {
      var e = {};
      if (v.name.trim().length < 3) e.name = app.lang === 'ar' ? 'أدخل اسمك الكامل.' : 'Enter your full name.';
      if (!/^05\d{8}$/.test(v.phone)) e.phone = t('phoneError');
      var g = parseFloat(v.gpa); if (isNaN(g) || g < 0 || g > 5) e.gpa = t('gpaError');
      return e;
    });
    function onSave(e) {
      e.preventDefault(); if (!f.submit()) return;
      app.act({ type: 'profile', student: st.id, fields: { phone: f.values.phone, gpa: f.values.gpa, cv: f.values.cv ? f.values.cv.name : '' }, userFields: { name: f.values.name.trim() } }, t('profileSaved'), 'success', true);
    }
    return h('form', { className: 'form', onSubmit: onSave, noValidate: true },
      f.tried && Object.keys(f.errors).length ? h(C.Alert, { tone: 'danger', title: t('fixErrors') }) : null,
      h(C.TextField, { id: 'p-name', label: t('fullName'), value: f.values.name, error: f.err('name'), onChange: function (e) { f.set('name', e.target.value); } }),
      h(C.TextField, { id: 'p-email', label: t('email'), value: app.me.email, disabled: true }),
      h('div', { className: 'form-2' },
        h(C.TextField, { id: 'p-phone', label: t('phone'), value: f.values.phone, inputMode: 'numeric', error: f.err('phone'), help: '05XXXXXXXX', onChange: function (e) { f.set('phone', e.target.value.trim()); } }),
        h(C.TextField, { id: 'p-gpa', label: t('gpa'), value: f.values.gpa, inputMode: 'decimal', error: f.err('gpa'), help: '0 – 5', onChange: function (e) { f.set('gpa', e.target.value.trim()); } })),
      h(C.TextField, { id: 'p-major', label: t('major'), value: st.major, disabled: true }),
      h(C.FileUpload, { label: t('cv'), prompt: t('chooseFile'), hint: t('fileHint'), removeLabel: t('remove'), file: f.values.cv, error: f.values.cvErr,
        onFile: function (file) { if (!validFile(file)) f.setAll({ cvErr: t('fileTypeError') }); else f.setAll({ cvErr: null, cv: { name: file.name } }); },
        onRemove: function () { f.setAll({ cv: null }); } }),
      h(Row, { end: true }, h(C.Button, { type: 'submit' }, t('saveProfile'))));
  }

  /* ================= COMPANY ================= */
  function companyCtx(app) {
    var s = app.state, cid = app.me.ref;
    var opps = s.opps.filter(function (o) { return o.company === cid; });
    var oppIds = opps.map(function (o) { return o.id; });
    var placements = s.placements.filter(function (p) { return oppIds.indexOf(p.opp) >= 0; });
    var pids = placements.map(function (p) { return p.id; });
    var trainees = s.students.filter(function (x) { return pids.indexOf(x.placement) >= 0; });
    return { cid: cid, opps: opps, oppIds: oppIds, placements: placements, pids: pids, trainees: trainees };
  }

  function CompanyOverview() {
    var app = useApp(), s = app.state, t = app.t, k = companyCtx(app);
    var newApps = s.apps.filter(function (a) { return k.oppIds.indexOf(a.opp) >= 0 && a.status === 'Submitted'; });
    var todays = s.attendance.filter(function (a) { return k.pids.indexOf(a.placement) >= 0 && a.date === TODAY; });
    var pct = k.trainees.length ? Math.round(todays.length * 100 / k.trainees.length) : 0;
    return h(Page, { eyebrow: longToday(app.lang), title: greeting(t) + ', ' + app.me.name, lead: t('companyOverview'),
      extra: h(HeroProgress, { label: t('todaysCheckins'), value: frac(todays.length, k.trainees.length), pct: pct, note: k.trainees.length + ' ' + t('activeTrainees').toLowerCase() }) },
      h(Tiles, { label: t('quickActions'), items: [
        { icon: 'plus', label: t('postOpp'), onClick: function () { app.go('postings', { compose: true }); } },
        { icon: 'users', label: t('navApplicants'), count: newApps.length || null, onClick: function () { app.go('applicants'); } },
        { icon: 'key', label: t('navDailyCode'), onClick: function () { app.go('dailycode'); } },
        { icon: 'chat', label: t('navChat'), count: totalUnread(s, app.me.id) || null, onClick: function () { app.go('chat'); } }] }),
      h('div', { className: 'split' },
        h(Section, { title: t('dailyCodeTitle') }, h(CodeCards, { placements: k.placements })),
        h(Section, { title: t('activity') }, h(Feed, { items: activityFor(app, { pids: k.pids, oppIds: k.oppIds, limit: 6 }).concat([]).slice(0, 6) }))));
  }

  var TEMPLATES = [
    { title: 'Supply Chain Analyst Trainee', majors: 'Business Administration', description: 'Track inbound shipments, reconcile warehouse stock counts and prepare weekly KPI summaries.' },
    { title: 'HR Operations Trainee', majors: 'Business Administration', description: 'Help run onboarding, update employee records and prepare attendance reports for payroll.' },
    { title: 'Procurement Assistant Trainee', majors: 'Business Administration', description: 'Prepare purchase requests, compare supplier quotations and follow up delivery dates.' },
    { title: 'Accounts Payable Trainee', majors: 'Business Administration', description: 'Match invoices to purchase orders, prepare payment batches and reconcile vendor statements.' },
    { title: 'Maintenance Planning Trainee', majors: 'Mechanical Engineering', description: 'Schedule preventive maintenance work orders and follow up spare-part requests with stores.' },
    { title: 'Process Engineering Trainee', majors: 'Chemical Engineering', description: 'Support the operations team with process monitoring, shift logs and daily production reports.' },
    { title: 'IT Support Trainee', majors: 'Computer Science', description: 'Handle first-line support tickets, set up user devices and keep the asset register current.' }
  ];

  function PostingsPage() {
    var app = useApp(), s = app.state, t = app.t, k = companyCtx(app);
    var open = R.useState(!!(app.params && app.params.compose));
    var blank = { title: '', city: by(s.companies, k.cid).city, majors: 'Business Administration', capacity: 2, description: '' };
    var f = useForm(blank, function (v) {
      var e = {};
      if (v.title.trim().length < 5) e.title = t('titleRequired');
      if (v.description.trim().length < 20) e.description = t('descRequired');
      return e;
    });
    function submit(e) {
      e.preventDefault(); if (!f.submit()) return;
      var v = f.values;
      app.act({ type: 'postOpp', by: app.me.id, opp: { company: k.cid, title: v.title.trim(), city: v.city, majors: v.majors, capacity: v.capacity, description: v.description.trim() } }, t('postedToast'), 'success', true);
      f.reset(blank); open[1](false);
    }
    var descLeft = Math.max(0, 20 - f.values.description.trim().length);
    var approved = by(s.companies, k.cid).status === 'Approved';
    return h(Page, { title: t('myPostings'), lead: t('postHelp'), action: open[0] || !approved ? null : h('button', { className: 'hero-btn', onClick: function () { open[1](true); } }, h(Icon, { name: 'plus', size: 18 }), t('postOpp')) },
      !approved ? h(C.Alert, { tone: 'warning', title: t('companyPendingTitle') }, t('companyPendingBody')) : null,
      open[0] && approved ? h(Card, null, h('form', { className: 'form', onSubmit: submit, noValidate: true },
        h('h2', { className: 'h2' }, t('postTitle')),
        h('div', { className: 'coop-field' },
          h('span', { className: 'coop-label' }, t('quickTemplates')),
          h('div', { className: 'chips' }, TEMPLATES.map(function (tp) {
            var on = f.values.title === tp.title;
            return h('button', { key: tp.title, type: 'button', className: 'chip', 'aria-pressed': on ? 'true' : 'false', onClick: function () { f.setAll({ title: tp.title, majors: tp.majors, description: tp.description }); } }, tp.title);
          })),
          h('span', { className: 'coop-help' }, t('templatesHelp'))),
        f.tried && Object.keys(f.errors).length ? h(C.Alert, { tone: 'danger', title: t('fixErrors') }) : null,
        h(C.TextField, { id: 'o-title', label: t('oppTitle'), required: true, value: f.values.title, error: f.err('title'), placeholder: 'e.g. Procurement Analyst Trainee', onChange: function (e) { f.set('title', e.target.value); } }),
        h('div', { className: 'form-3' },
          h(C.TextField, { id: 'o-city', label: t('city'), value: f.values.city, options: SA_CITIES.map(function (c) { return { value: c.en, label: app.lang === 'ar' ? c.ar : c.en }; }).sort(function (p, q) { return p.label.localeCompare(q.label, app.lang); }), onChange: function (e) { f.set('city', e.target.value); } }),
          h(C.TextField, { id: 'o-majors', label: t('majorReq'), value: f.values.majors, options: ['Business Administration', 'Chemical Engineering', 'Industrial Engineering', 'Mechanical Engineering', 'Electrical Engineering', 'Computer Science'], onChange: function (e) { f.set('majors', e.target.value); } }),
          h(Stepper, { id: 'o-cap', label: t('capacity'), value: f.values.capacity, min: 1, max: 20, help: '1 – 20', onChange: function (n) { f.set('capacity', n); } })),
        h(C.TextField, { id: 'o-desc', label: t('description'), required: true, multiline: true, value: f.values.description, error: f.err('description'), help: descLeft ? fill(t('charsLeft'), { n: descLeft }) : null, onChange: function (e) { f.set('description', e.target.value); } }),
        h(Row, { end: true }, h(C.Button, { variant: 'secondary', onClick: function () { open[1](false); f.reset(blank); } }, t('cancel')), h(C.Button, { type: 'submit' }, t('postOpp'))))) : null,
      h(C.DataTable, { caption: t('myPostings'), columns: [
        { key: 'title', header: t('opportunity') }, { key: 'city', header: t('city') }, { key: 'cap', header: t('seats'), align: 'end' }, { key: 'apps', header: t('navApplicants'), align: 'end' }, { key: 'posted', header: t('postedOn') },
        { key: 'status', header: t('status'), render: function (r) { return h(Badge, { status: r.status }); } }],
        rows: k.opps.map(function (o) { return { id: o.id, title: o.title, city: o.city, cap: o.capacity, apps: s.apps.filter(function (a) { return a.opp === o.id; }).length, posted: fmtDate(o.posted, app.lang), status: o.status }; }) }));
  }

  function ApplicantsTable(p) {
    var app = useApp(), s = app.state, t = app.t;
    return h(C.DataTable, { caption: t('applicantsTitle'), empty: t('noRecords'), columns: [
      { key: 'student', header: t('student'), render: function (r) { return h('span', { className: 'who' }, h(Avatar, { name: r.student, size: 'sm' }), r.student); } }, { key: 'major', header: t('major') }, { key: 'gpa', header: t('gpa'), align: 'end' }, { key: 'opp', header: t('opportunity') }, { key: 'date', header: t('submitted') },
      { key: 'status', header: t('status'), render: function (r) { return h(Badge, { status: r.status }); } },
      { key: 'act', header: t('actions'), align: 'end', render: function (r) { return r.status !== 'Submitted' ? null : h('div', { className: 'btns' },
        h(C.Button, { size: 'sm', onClick: function () { app.act({ type: 'appDecision', id: r.id, status: 'Accepted', by: app.me.id }, t('acceptedToast'), 'success', true); } }, t('accept')),
        h(C.Button, { size: 'sm', variant: 'danger', onClick: function () { app.act({ type: 'appDecision', id: r.id, status: 'Rejected', by: app.me.id }, t('rejectedToast'), 'info', true); } }, t('reject'))); } }],
      rows: p.apps.map(function (a) { var st = by(s.students, a.student); return { id: a.id, student: stuName(s, a.student), major: st.major, gpa: st.gpa, opp: by(s.opps, a.opp).title, date: fmtDate(a.date, app.lang), status: a.status }; }) });
  }
  function ApplicantsPage() {
    var app = useApp(), s = app.state, t = app.t, k = companyCtx(app);
    var apps = s.apps.filter(function (a) { return k.oppIds.indexOf(a.opp) >= 0; }).sort(function (a, b) { return (a.status === 'Submitted' ? 0 : 1) - (b.status === 'Submitted' ? 0 : 1) || (a.date < b.date ? 1 : -1); });
    var n = apps.filter(function (a) { return a.status === 'Submitted'; }).length;
    return h(Page, { title: t('applicantsTitle'), lead: n + ' ' + t('newApplicants').toLowerCase() }, h(ApplicantsTable, { apps: apps }));
  }

  function CodeCards(p) {
    var app = useApp(), s = app.state, t = app.t;
    if (!p.placements.length) return h(Empty, { title: t('noRecords') });
    return h('div', { className: 'stack' }, p.placements.map(function (pl) {
      var code = s.codes.filter(function (k) { return k.placement === pl.id && k.date === TODAY && k.status === 'Active'; })[0];
      var trainees = s.students.filter(function (x) { return x.placement === pl.id; }).length;
      var ins = s.attendance.filter(function (a) { return a.placement === pl.id && a.date === TODAY; }).length;
      var title = oppOfPlacement(s, pl.id).title;
      var gen = function () { app.act({ type: 'genCode', placement: pl.id, by: app.me.id }, t('codeGenerated'), 'success', true); };
      return code ? h(C.DailyCodeCard, { key: pl.id, code: code.code, status: 'Active', title: title, meta: t('expiresToday') + ' · ' + frac(ins, trainees) + ' ' + t('checkedIn'),
          actions: h(C.Button, { variant: 'secondary', size: 'sm', onClick: gen }, t('regenerateCode')) })
        : h('div', { key: pl.id, className: 'coop-dcard' }, h('span', { className: 'coop-dcard-title' }, title), h('p', { className: 'muted' }, t('noCodeYet')), h(C.Button, { onClick: gen }, t('generateCode')));
    }));
  }
  function DailyCodePage() {
    var app = useApp(), s = app.state, t = app.t, k = companyCtx(app);
    var past = s.codes.filter(function (c) { return k.pids.indexOf(c.placement) >= 0 && c.status === 'Expired'; }).slice().reverse();
    return h(Page, { title: t('dailyCodeTitle'), lead: t('expiresToday') },
      h(Grid, null, h(CodeCards, { placements: k.placements })),
      h(Section, { title: t('recentCodes') }, h(C.DataTable, { caption: t('recentCodes'), empty: t('noRecords'), columns: [
        { key: 'date', header: t('date') }, { key: 'opp', header: t('opportunity') }, { key: 'code', header: app.lang === 'ar' ? 'الرمز' : 'Code', render: function (r) { return h('span', { className: 'mono' }, r.code); } }, { key: 'status', header: t('status'), render: function (r) { return h(Badge, { status: r.status }); } }],
        rows: past.map(function (c) { return { id: c.id, date: fmtDate(c.date, app.lang), opp: oppOfPlacement(s, c.placement).title, code: c.code.slice(0, 3) + ' ' + c.code.slice(3), status: c.status }; }) })));
  }

  function EvalForm(p) {
    var app = useApp(), s = app.state, t = app.t;
    var init = { student: p.students[0] ? p.students[0].id : '', score: 80, comments: '' };
    var f = useForm(init, function (v) { var e = {}; if (v.comments.trim().length < 10) e.comments = t('commentsRequired'); return e; });
    function submit(e) {
      e.preventDefault(); if (!f.submit()) return;
      app.act({ type: 'submitEval', student: f.values.student, evalType: p.evalType, by: app.me.id, score: f.values.score, comments: f.values.comments.trim() }, t('evalSaved'), 'success', true);
      f.reset({ student: f.values.student, score: 80, comments: '' });
    }
    if (!p.students.length) return null;
    return h(Card, null, h('form', { className: 'form', onSubmit: submit, noValidate: true },
      h('div', { className: 'form-2' },
        h(C.TextField, { id: 'ev-stu', label: t('student'), value: f.values.student, options: p.students.map(function (x) { return { value: x.id, label: stuName(s, x.id) }; }), onChange: function (e) { f.set('student', e.target.value); } }),
        h('div', { className: 'coop-field' },
          h('label', { className: 'coop-label', htmlFor: 'ev-score' }, t('score') + ': ', h('b', { className: 'score-out' + (f.values.score < 60 ? ' low' : '') }, f.values.score), ' / 100', f.values.score < 60 ? h('span', { className: 'low-tag' }, t('belowPass')) : null),
          h('input', { id: 'ev-score', className: 'range', type: 'range', min: 0, max: 100, step: 1, value: f.values.score, onChange: function (e) { f.set('score', +e.target.value); } }),
          h('span', { className: 'coop-help' }, t('scoreHelp')))),
      h(C.TextField, { id: 'ev-com', label: t('comments'), multiline: true, value: f.values.comments, error: f.err('comments'), help: f.values.comments.trim().length < 10 ? fill(t('charsLeft'), { n: 10 - f.values.comments.trim().length }) : null, onChange: function (e) { f.set('comments', e.target.value); } }),
      h(Row, { end: true }, h(C.Button, { type: 'submit' }, t('submitEval')))));
  }
  function TraineesPage() {
    var app = useApp(), s = app.state, t = app.t, k = companyCtx(app);
    var ids = k.trainees.map(function (x) { return x.id; });
    var evals = s.evals.filter(function (e) { return e.type === 'Company' && ids.indexOf(e.student) >= 0; });
    return h(Page, { title: t('traineesTitle'), lead: k.trainees.length + ' ' + t('activeTrainees').toLowerCase() },
      h(StudentCards, { students: k.trainees, badge: function (x) { return evals.some(function (e) { return e.student === x.id; }) ? 'Evaluated' : 'Pending Evaluation'; } }),
      h(Section, { title: t('submitEval') }, h(EvalForm, { students: k.trainees, evalType: 'Company' })),
      h(Section, { title: t('evaluations') }, h(EvalList, { evals: evals, showStudent: true, empty: t('noRecords') })));
  }

  /* student cards + detail sheet (FamilyLink member cards) */
  function StudentCards(p) {
    var app = useApp(), s = app.state, t = app.t, open = R.useState(null);
    if (!p.students.length) return h(Empty, { title: t('noRecords') });
    var sel = open[0] ? by(s.students, open[0]) : null;
    return h(R.Fragment, null,
      h('div', { className: 'people' }, p.students.map(function (x) {
        var rt = attRate(s, x.id), o = x.placement ? oppOfPlacement(s, x.placement) : null, name = stuName(s, x.id);
        return h('button', { key: x.id, className: 'person', onClick: function () { open[1](x.id); }, 'aria-label': t('viewDetails') + ': ' + name },
          h('span', { className: 'person-top' }, h(Avatar, { name: name }), h('span', { className: 'person-id' }, h('span', { className: 'person-name' }, name), h('span', { className: 'person-sub' }, o ? compName(s, o.company) : x.major)), p.badge ? h(Badge, { status: p.badge(x) }) : null),
          h('span', { className: 'person-stat' }, h('span', null, t('attendanceRate')), h('b', null, rt == null ? '—' : rt + '%')),
          h('span', { className: 'bar' }, h('span', { style: { width: (rt || 0) + '%' } })));
      })),
      sel ? h(StudentSheet, { student: sel, onClose: function () { open[1](null); } }) : null);
  }
  function StudentSheet(p) {
    var app = useApp(), s = app.state, t = app.t, x = p.student, name = stuName(s, x.id);
    var o = x.placement ? oppOfPlacement(s, x.placement) : null, rt = attRate(s, x.id);
    var rep = s.reports.filter(function (r) { return r.student === x.id; })[0];
    var recent = s.attendance.filter(function (a) { return a.student === x.id; }).slice(-5).reverse();
    var openRep = R.useState(null);
    if (openRep[0]) return h(ReportViewer, { reportId: openRep[0], onClose: function () { openRep[1](null); } });
    return h(Sheet, { label: t('studentDetails'), onClose: p.onClose,
      head: h('div', { className: 'sheet-head' }, h(Avatar, { name: name, size: 'lg' }), h('div', { className: 'sheet-id' }, h('p', { className: 'sheet-name' }, name), h('p', { className: 'sheet-sub' }, x.major + ' · GPA ' + x.gpa)), h('div', { className: 'sheet-kpi' }, h('b', null, rt == null ? '—' : rt + '%'), h('span', null, t('attendanceRate')))) },
      o ? h('div', { className: 'kv-list' },
        h('div', null, h('span', null, t('placement')), h('b', null, o.title + ' · ' + compName(s, o.company))),
        h('div', null, h('span', null, t('supervisor')), h('b', null, supName(s, x.supervisor) || t('unassigned'))),
        h('div', null, h('span', null, t('finalReport')), h('span', { className: 'row' }, rep ? h(FileButton, { file: rep.file, label: t('openFile'), openText: t('openReport'), onClick: function () { openRep[1](rep.id); } }) : null, h(Badge, { status: rep ? rep.status : 'Not submitted' })))) : h('p', { className: 'muted' }, t('noPlacementTitle')),
      recent.length ? h('div', { className: 'stack-sm' }, h('p', { className: 'coop-label' }, t('recentCheckins')),
        h('ul', { className: 'mini-list' }, recent.map(function (a) { return h('li', { key: a.id }, h('span', null, fmtDate(a.date, app.lang) + ' · ' + a.time), h(Badge, { status: a.status })); }))) : null,
      h('div', { className: 'stack-sm' }, h('p', { className: 'coop-label' }, t('evaluations')), h(EvalList, { evals: s.evals.filter(function (e) { return e.student === x.id; }) })));
  }

  /* ================= SUPERVISOR ================= */
  function supStudents(app) { return app.state.students.filter(function (x) { return x.supervisor === app.me.ref; }); }

  function SupStudents() {
    var app = useApp(), s = app.state, t = app.t, mine = supStudents(app), ids = mine.map(function (x) { return x.id; });
    var pend = s.attendance.filter(function (a) { return ids.indexOf(a.student) >= 0 && a.status === 'Pending'; }).length;
    var reps = s.reports.filter(function (r) { return ids.indexOf(r.student) >= 0 && r.status === 'Submitted'; }).length;
    var placed = mine.filter(function (x) { return x.placement; });
    var avg = placed.length ? Math.round(placed.reduce(function (a, x) { return a + attRate(s, x.id); }, 0) / placed.length) : 0;
    return h(Page, { eyebrow: longToday(app.lang), title: greeting(t) + ', ' + app.me.name, lead: mine.length + ' ' + t('myStudents').toLowerCase(),
      extra: h(HeroProgress, { label: t('attendanceRate'), value: avg + '%', pct: avg, note: pend + ' ' + t('pendingAttendance').toLowerCase() }) },
      h(Tiles, { label: t('quickActions'), items: [
        { icon: 'check', label: t('attendanceApprovals'), count: pend || null, onClick: function () { app.go('attendance'); } },
        { icon: 'doc', label: t('reportsTitle'), count: reps || null, onClick: function () { app.go('reports'); } },
        { icon: 'star', label: t('navEvaluations'), onClick: function () { app.go('evaluations'); } },
        { icon: 'chat', label: t('navChat'), count: totalUnread(s, app.me.id) || null, onClick: function () { app.go('chat'); } }] }),
      h('div', { className: 'split' },
        h(Section, { title: t('myStudents') }, h(StudentCards, { students: mine, badge: function (x) { var r = s.reports.filter(function (y) { return y.student === x.id; })[0]; return r ? r.status : 'Not submitted'; } })),
        h(Section, { title: t('activity') }, h(Feed, { items: activityFor(app, { students: ids }) }))));
  }

  function SupAttendance() {
    var app = useApp(), s = app.state, t = app.t, ids = supStudents(app).map(function (x) { return x.id; });
    var pend = s.attendance.filter(function (a) { return ids.indexOf(a.student) >= 0 && a.status === 'Pending'; }).sort(function (a, b) { return a.date < b.date ? 1 : -1; });
    function decide(list, status) { app.act({ type: 'attDecision', ids: list, status: status, by: app.me.id }, (status === 'Approved' ? t('approvedToast') : t('rejectedToast2')) + ' (' + list.length + ')', status === 'Approved' ? 'success' : 'info', true); }
    return h(Page, { title: t('attendanceApprovals'), lead: pend.length ? pend.length + ' ' + t('pendingAttendance').toLowerCase() : t('nothingPending'),
      action: pend.length ? h('button', { className: 'hero-btn', onClick: function () { decide(pend.map(function (a) { return a.id; }), 'Approved'); } }, h(Icon, { name: 'check', size: 18 }), t('approveAll') + ' (' + pend.length + ')') : null },
      h(C.DataTable, { caption: t('attendanceApprovals'), empty: t('nothingPending'), columns: [
        { key: 'name', header: t('student'), render: function (r) { return h('span', { className: 'who' }, h(Avatar, { name: r.name, size: 'sm' }), r.name); } }, { key: 'pl', header: t('placement') }, { key: 'date', header: t('date') }, { key: 'time', header: t('checkInTime') },
        { key: 'status', header: t('status'), render: function (r) { return h(Badge, { status: r.status }); } },
        { key: 'act', header: t('actions'), align: 'end', render: function (r) { return h('div', { className: 'btns' },
          h(C.Button, { size: 'sm', onClick: function () { decide([r.id], 'Approved'); } }, t('approve')),
          h(C.Button, { size: 'sm', variant: 'danger', onClick: function () { decide([r.id], 'Rejected'); } }, t('reject'))); } }],
        rows: pend.map(function (a) { return { id: a.id, name: stuName(s, a.student), pl: compName(s, oppOfPlacement(s, a.placement).company), date: fmtDate(a.date, app.lang), time: a.time, status: a.status }; }) }));
  }

  function SupReports() {
    var app = useApp(), s = app.state, t = app.t, ids = supStudents(app).map(function (x) { return x.id; });
    var reps = s.reports.filter(function (r) { return ids.indexOf(r.student) >= 0; });
    var viewing = R.useState(null);
    return h(Page, { title: t('reportsTitle'), lead: t('reportsLead') },
      viewing[0] ? h(ReportViewer, { reportId: viewing[0], onClose: function () { viewing[1](null); } }) : null,
      h(C.DataTable, { caption: t('reportsTitle'), empty: t('noRecords'), columns: [
        { key: 'name', header: t('student'), render: function (r) { return h('span', { className: 'who' }, h(Avatar, { name: r.name, size: 'sm' }), r.name); } }, { key: 'file', header: t('file'), render: function (r) { return h(FileButton, { file: r.file, label: t('openFile'), openText: t('openReport'), onClick: function () { viewing[1](r.id); } }); } }, { key: 'date', header: t('submitted') },
        { key: 'status', header: t('status'), render: function (r) { return h(Badge, { status: r.status }); } },
        { key: 'act', header: t('actions'), align: 'end', render: function (r) { return r.status !== 'Submitted' ? null : h('div', { className: 'btns' },
          h(C.Button, { size: 'sm', onClick: function () { app.act({ type: 'reportDecision', id: r.id, status: 'Approved', by: app.me.id }, t('reportApprovedToast'), 'success', true); } }, t('approve')),
          h(C.Button, { size: 'sm', variant: 'secondary', onClick: function () { app.act({ type: 'reportDecision', id: r.id, status: 'Request Revision', by: app.me.id }, t('revisionToast'), 'info', true); } }, t('requestRevision'))); } }],
        rows: reps.map(function (r) { return { id: r.id, name: stuName(s, r.student), file: r.file, date: fmtDate(r.date, app.lang), status: r.status }; }) }));
  }

  function SupEvals() {
    var app = useApp(), s = app.state, t = app.t, mine = supStudents(app).filter(function (x) { return x.placement; }), ids = mine.map(function (x) { return x.id; });
    return h(Page, { title: t('academicEvalTitle') },
      h(EvalForm, { students: mine, evalType: 'Academic' }),
      h(Section, { title: t('evaluations') }, h(EvalList, { evals: s.evals.filter(function (e) { return ids.indexOf(e.student) >= 0; }), showStudent: true, empty: t('noRecords') })));
  }

  /* ================= ADMIN ================= */
  function Analytics() {
    var app = useApp(), s = app.state, t = app.t;
    var studentsActive = s.students.filter(function (x) { return by(s.users, x.user).status === 'Active'; });
    var placed = studentsActive.filter(function (x) { return x.placement; }).length;
    var rate = studentsActive.length ? Math.round(placed * 100 / studentsActive.length) : 0;
    var pc = s.companies.filter(function (c) { return c.status === 'Pending Approval'; }).length, po = s.opps.filter(function (o) { return o.status === 'Pending Approval'; }).length;
    var placedStudents = s.students.filter(function (x) { return x.placement; });
    var att = placedStudents.map(function (x) { return attRate(s, x.id); }); var avgAtt = att.length ? Math.round(att.reduce(function (a, b) { return a + b; }, 0) / att.length) : 0;
    var perCo = s.companies.filter(function (c) { return c.status === 'Approved'; }).map(function (c) {
      var n = placedStudents.filter(function (x) { return oppOfPlacement(s, x.placement).company === c.id; }).length;
      var cap = s.opps.filter(function (o) { return o.company === c.id && o.status === 'Open'; }).reduce(function (a, o) { return a + o.capacity; }, 0);
      return { name: c.name, n: n, cap: cap };
    });
    var max = Math.max.apply(null, perCo.map(function (x) { return Math.max(x.cap, x.n); }).concat([1]));
    return h(Page, { eyebrow: longToday(app.lang), title: t('analyticsTitle'), lead: app.me.name,
      extra: h(HeroProgress, { label: t('placementRate'), value: rate + '%', pct: rate, note: frac(placed, studentsActive.length) + ' ' + t('placedOf') }) },
      h(Tiles, { label: t('quickActions'), items: [
        { icon: 'shield', label: t('navApprovals'), count: (pc + po) || null, onClick: function () { app.go('approvals'); } },
        { icon: 'users', label: t('navUsers'), onClick: function () { app.go('users'); } },
        { icon: 'chat', label: t('navChat'), count: totalUnread(s, app.me.id) || null, onClick: function () { app.go('chat'); } },
        { icon: 'list', label: t('navAudit'), onClick: function () { app.go('audit'); } }] }),
      h(Grid, { min: 'sm' },
        h(C.StatCard, { label: t('attendanceRate'), value: avgAtt + '%', progress: avgAtt, note: fmtDate('2026-09-06', app.lang) + ' – ' + fmtDate(TODAY, app.lang) }),
        h(C.StatCard, { label: t('activePlacements'), value: String(s.placements.filter(function (p) { return p.status === 'Active'; }).length) }),
        h(C.StatCard, { label: t('pendingApprovals'), value: String(pc + po), note: pc + ' ' + t('companiesShort') + ' · ' + po + ' ' + t('opportunitiesShort') })),
      h('div', { className: 'split' },
        h(Section, { title: t('placementsByCompany') },
          h(Card, { className: 'bars' },
            perCo.map(function (c) { return h('div', { key: c.name, className: 'bar-row' },
              h('span', { className: 'bar-label' }, c.name),
              h('span', { className: 'bar-track' }, h('span', { className: 'bar-cap', style: { width: (c.cap * 100 / max) + '%' } }), h('span', { className: 'bar-fill', style: { width: (c.n * 100 / max) + '%' } })),
              h('span', { className: 'bar-val' }, c.n + ' / ' + c.cap)); }),
            h('p', { className: 'legend' }, h('span', { className: 'sw sw-fill' }), (app.lang === 'ar' ? 'متدربون' : 'Trainees placed'), h('span', { className: 'sw sw-cap' }), (app.lang === 'ar' ? 'المقاعد المفتوحة' : 'Open seats')))),
        h(Section, { title: t('navAudit'), aside: h(C.Button, { variant: 'ghost', size: 'sm', onClick: function () { app.go('audit'); } }, t('seeAll')) }, h(AuditFeed, { limit: 5 }))));
  }

  function AuditFeed(p) {
    var app = useApp(), s = app.state;
    var rows = (p.rows || s.audit).slice(0, p.limit || 25);
    return h(Feed, { items: rows.map(function (l) { var tone = /Rejected|Suspended/.test(l.action) ? 'danger' : /Approved|Accepted|Activated/.test(l.action) ? 'success' : 'info';
      return { key: l.id, icon: /code/i.test(l.action) ? 'key' : /company|opportunity/i.test(l.action) ? 'shield' : /account/i.test(l.action) ? 'users' : 'list', tone: tone, who: userName(s, l.user), date: l.ts.slice(0, 10), time: l.ts.slice(11), text: l.action }; }) });
  }

  function Approvals() {
    var app = useApp(), s = app.state, t = app.t;
    var comps = s.companies.filter(function (c) { return c.status === 'Pending Approval'; });
    var opps = s.opps.filter(function (o) { return o.status === 'Pending Approval'; });
    function btns(onA, onR) { return h('div', { className: 'btns' }, h(C.Button, { size: 'sm', onClick: onA }, t('approve')), h(C.Button, { size: 'sm', variant: 'danger', onClick: onR }, t('reject'))); }
    return h(Page, { title: t('approvalsTitle'), lead: (comps.length + opps.length) + ' ' + t('pendingApprovals').toLowerCase() },
      h(Section, { title: t('companiesPending') + ' (' + comps.length + ')' }, h(C.DataTable, { caption: t('companiesPending'), empty: t('nothingPending'), columns: [
        { key: 'name', header: t('company') }, { key: 'city', header: t('city') }, { key: 'email', header: t('email') }, { key: 'status', header: t('status'), render: function (r) { return h(Badge, { status: r.status }); } },
        { key: 'act', header: t('actions'), align: 'end', render: function (r) { return btns(function () { app.act({ type: 'companyDecision', id: r.id, status: 'Approved', by: app.me.id }, t('approvedToast'), 'success', true); }, function () { app.act({ type: 'companyDecision', id: r.id, status: 'Rejected', by: app.me.id }, t('rejectedToast2'), 'info', true); }); } }],
        rows: comps.map(function (c) { return { id: c.id, name: c.name, city: c.city, email: by(s.users, c.user).email, status: c.status }; }) })),
      h(Section, { title: t('oppsPending') + ' (' + opps.length + ')' }, h(C.DataTable, { caption: t('oppsPending'), empty: t('nothingPending'), columns: [
        { key: 'title', header: t('opportunity') }, { key: 'co', header: t('company') }, { key: 'majors', header: t('majors') }, { key: 'cap', header: t('seats'), align: 'end' },
        { key: 'act', header: t('actions'), align: 'end', render: function (r) { return btns(function () { app.act({ type: 'oppDecision', id: r.id, status: 'Open', by: app.me.id }, t('approvedToast'), 'success', true); }, function () { app.act({ type: 'oppDecision', id: r.id, status: 'Rejected', by: app.me.id }, t('rejectedToast2'), 'info', true); }); } }],
        rows: opps.map(function (o) { return { id: o.id, title: o.title, co: compName(s, o.company), majors: o.majors, cap: o.capacity }; }) })));
  }

  function Users() {
    var app = useApp(), s = app.state, t = app.t, role = R.useState('');
    var roles = { Student: t('roleStudent'), Company: t('roleCompany'), Supervisor: t('roleSupervisor'), Admin: t('roleAdmin') };
    var rows = s.users.filter(function (u) { return !role[0] || u.role === role[0]; });
    return h(Page, { title: t('usersTitle'), lead: s.users.length + ' ' + (app.lang === 'ar' ? 'حسابًا' : 'accounts') },
      h('div', { className: 'chips', role: 'group', 'aria-label': t('role') }, [['', t('all')]].concat(Object.keys(roles).map(function (k) { return [k, roles[k]]; })).map(function (r) {
        return h('button', { key: r[0] || 'all', type: 'button', className: 'chip', 'aria-pressed': role[0] === r[0] ? 'true' : 'false', onClick: function () { role[1](r[0]); } }, r[1]);
      })),
      h(C.DataTable, { caption: t('usersTitle'), columns: [
        { key: 'name', header: t('fullName'), render: function (r) { return h('span', { className: 'who' }, h(Avatar, { name: r.name, size: 'sm' }), r.name); } }, { key: 'email', header: t('email') }, { key: 'role', header: t('role') }, { key: 'status', header: t('status'), render: function (r) { return h(Badge, { status: r.status }); } },
        { key: 'act', header: t('actions'), align: 'end', render: function (r) { if (r.id === app.me.id) return null; var sus = r.status === 'Active';
          return h(C.Button, { size: 'sm', variant: sus ? 'danger' : 'secondary', onClick: function () { app.act({ type: 'userStatus', id: r.id, status: sus ? 'Suspended' : 'Active', by: app.me.id }, sus ? t('suspendedToast') : t('activatedToast'), sus ? 'info' : 'success', true); } }, sus ? t('suspend') : t('activate')); } }],
        rows: rows.map(function (u) { return { id: u.id, name: u.name, email: u.email, role: roles[u.role], status: u.status }; }) }));
  }

  function Assign() {
    var app = useApp(), s = app.state, t = app.t;
    var sups = s.users.filter(function (u) { return u.role === 'Supervisor'; });
    return h(Page, { title: t('assignTitle'), lead: t('assignHelp') },
      h(C.DataTable, { caption: t('assignTitle'), columns: [
        { key: 'name', header: t('student'), render: function (r) { return h('span', { className: 'who' }, h(Avatar, { name: r.name, size: 'sm' }), r.name); } }, { key: 'major', header: t('major') }, { key: 'pl', header: t('placement') },
        { key: 'sup', header: t('supervisor'), render: function (r) {
          return h('select', { className: 'coop-input select-sm', id: 'sup-' + r.id, 'aria-label': t('supervisor') + ' – ' + r.name, value: r.sup || '', onChange: function (e) { if (!e.target.value) return; app.act({ type: 'assign', student: r.id, supervisor: e.target.value, by: app.me.id }, t('assignedToast'), 'success', true); } },
            r.sup ? null : h('option', { value: '' }, t('unassigned')), sups.map(function (u) { return h('option', { key: u.ref, value: u.ref }, u.name); })); } }],
        rows: s.students.map(function (x) { var o = x.placement ? oppOfPlacement(s, x.placement) : null; return { id: x.id, name: stuName(s, x.id), major: x.major, pl: o ? compName(s, o.company) : '—', sup: x.supervisor }; }) }));
  }

  function Audit() {
    var app = useApp(), s = app.state, t = app.t, q = R.useState('');
    var rows = s.audit.filter(function (l) { return !q[0] || (userName(s, l.user) + ' ' + l.action).toLowerCase().indexOf(q[0].toLowerCase()) >= 0; });
    return h(Page, { title: t('auditTitle'), lead: rows.length + ' ' + (app.lang === 'ar' ? 'سجل' : 'entries') },
      h(Card, { className: 'filters filters-1' }, h(C.TextField, { id: 'a-q', label: t('filterUser'), type: 'search', value: q[0], onChange: function (e) { q[1](e.target.value); } })),
      h(AuditFeed, { rows: rows, limit: 25 }));
  }


  /* ================= COMPANY PROFILE (UC-07) ================= */
  function CompanyProfileForm() {
    var app = useApp(), s = app.state, t = app.t, me = app.me, co = by(s.companies, me.ref);
    var f = useForm({ name: co.name, city: co.city, phone: userPhone(s, me) || '' }, function (v) {
      var e = {};
      if (v.name.trim().length < 2) e.name = t('companyNameError');
      if (!v.city) e.city = t('cityError');
      if (!/^0\d{8,9}$/.test(v.phone)) e.phone = t('companyPhoneError');
      return e;
    });
    function onSave(e) {
      e.preventDefault(); if (!f.submit()) return;
      app.act({ type: 'companyProfile', company: co.id, user: me.id, name: f.values.name.trim(), city: f.values.city, phone: f.values.phone }, t('companySaved'), 'success', true);
    }
    var cities = SA_CITIES.map(function (c) { return { value: c.en, label: app.lang === 'ar' ? c.ar : c.en }; }).sort(function (p, q) { return p.label.localeCompare(q.label); });
    return h('form', { className: 'form', onSubmit: onSave, noValidate: true },
      f.tried && Object.keys(f.errors).length ? h(C.Alert, { tone: 'danger', title: t('fixErrors') }) : null,
      h(C.TextField, { id: 'c-name', label: t('companyName'), required: true, value: f.values.name, error: f.err('name'), onChange: function (e) { f.set('name', e.target.value); } }),
      h('div', { className: 'form-2' },
        h(C.TextField, { id: 'c-city', label: t('city'), required: true, value: f.values.city, error: f.err('city'), options: cities, onChange: function (e) { f.set('city', e.target.value); } }),
        h(C.TextField, { id: 'c-phone', label: t('contactPhone'), required: true, value: f.values.phone, inputMode: 'numeric', error: f.err('phone'), help: '0138001200', onChange: function (e) { f.set('phone', e.target.value.trim()); } })),
      h(C.TextField, { id: 'c-email', label: t('companyEmail'), value: me.email, disabled: true }),
      h('div', { className: 'kv' }, h('span', { className: 'coop-label' }, t('approvalStatus')), h(Badge, { status: co.status })),
      h(Row, { end: true }, h(C.Button, { type: 'submit' }, t('saveCompany'))));
  }

  /* ================= REPORTS (UC-17, FR-21) ================= */
  function stLabel(app, v) { return app.lang === 'ar' ? (STATUS_AR[v] || v) : v; }
  function attCounts(s, sid) {
    var r = s.attendance.filter(function (a) { return a.student === sid; });
    var c = { days: 0, approved: 0, pending: 0, rejected: 0 };
    r.forEach(function (a) { if (a.status === 'Approved') c.approved++; else if (a.status === 'Pending') c.pending++; else if (a.status === 'Rejected') c.rejected++; });
    c.days = c.approved + c.pending; return c;
  }
  function latestScore(s, sid, type) { var e = s.evals.filter(function (x) { return x.student === sid && x.type === type; })[0]; return e ? e.score : null; }
  function avg(list) { var v = list.filter(function (x) { return x != null; }); return v.length ? Math.round(v.reduce(function (a, b) { return a + b; }, 0) / v.length) : null; }
  function dash(v) { return v == null || v === '' ? '—' : v; }

  /* rows: array of plain objects; cols: [{key, header, align}] — CSV with BOM so Excel opens Arabic correctly */
  function downloadCSV(name, cols, rows, meta) {
    function esc(v) { v = v == null ? '' : String(v); return /[",\n\r]/.test(v) ? '"' + v.replace(/"/g, '""') + '"' : v; }
    var lines = (meta || []).map(function (m) { return esc(m); });
    if (lines.length) lines.push('');
    lines.push(cols.map(function (c) { return esc(c.header); }).join(','));
    rows.forEach(function (r) { lines.push(cols.map(function (c) { var v = r[c.key]; return esc(v === '—' ? '' : v); }).join(',')); });
    var blob = new Blob(['﻿' + lines.join('\r\n')], { type: 'text/csv;charset=utf-8' });
    var url = URL.createObjectURL(blob), a = document.createElement('a');
    a.href = url; a.download = name + '.csv'; document.body.appendChild(a); a.click();
    setTimeout(function () { URL.revokeObjectURL(url); a.remove(); }, 500);
  }

  /* shared shell: parameters card, export buttons, print header, stats and table */
  function ReportView(p) {
    var app = useApp(), t = app.t;
    var empty = !p.rows.length;
    var meta = [p.title, t('generatedOn') + ': ' + fmtDate(TODAY, app.lang) + ' ' + t('generatedBy') + ' ' + app.me.name].concat(p.filters ? [t('filtersLabel') + ': ' + p.filters] : []);
    function doPrint() { app.dispatch({ type: 'logExport', by: app.me.id, report: p.logName, format: 'PDF' }); setTimeout(function () { window.print(); }, 50); }
    function doCSV() { downloadCSV(p.file, p.columns, p.rows.concat(p.totalRow ? [p.totalRow] : []), meta); app.act({ type: 'logExport', by: app.me.id, report: p.logName, format: 'CSV' }, t('exportedToast'), 'success', false); }
    return h('div', { className: 'stack' },
      h(Card, { className: 'no-print' }, h('div', { className: 'report-params' }, h('div', { className: 'report-fields' }, p.params),
        h('div', { className: 'report-actions' },
          h(C.Button, { variant: 'secondary', disabled: empty, onClick: doPrint }, t('printPdf')),
          h(C.Button, { disabled: empty, onClick: doCSV }, t('exportExcel'))))),
      empty ? h(C.Alert, { tone: 'warning', title: t('noReportData') }) : h('div', { className: 'report-out' },
        h('div', { className: 'print-head' },
          h('p', { className: 'print-college' }, t('college')),
          h('h1', { className: 'print-title' }, 'E-COOP · ' + p.title),
          meta.slice(1).map(function (m, i) { return h('p', { key: i, className: 'print-meta' }, m); })),
        p.stats ? h(Grid, { min: 'sm' }, p.stats.map(function (st, i) { return h(C.StatCard, { key: i, label: st[0], value: st[1], progress: st[2] }); })) : null,
        h(C.DataTable, { caption: p.title, columns: p.columns.map(function (c) { return c.badge ? Object.assign({}, c, { render: function (r) { return r[c.key] === '—' ? '—' : h(Badge, { status: r['_' + c.key] }); } }) : c; }),
          rows: p.totalRow ? p.rows.concat([Object.assign({ id: '_total' }, p.totalRow)]) : p.rows })));
  }

  function AcademicReports() {
    var app = useApp(), s = app.state, t = app.t, mine = supStudents(app);
    var major = R.useState(''), pl = R.useState(''), sem = R.useState('481');
    var majors = [], pls = [];
    mine.forEach(function (x) { if (majors.indexOf(x.major) < 0) majors.push(x.major); if (x.placement && pls.indexOf(x.placement) < 0) pls.push(x.placement); });
    var plOpts = pls.map(function (id) { var o = oppOfPlacement(s, id); return { value: id, label: compName(s, o.company) + ' · ' + o.title }; });
    var list = mine.filter(function (x) { return (!major[0] || x.major === major[0]) && (!pl[0] || x.placement === pl[0]); });
    var rows = list.map(function (x) {
      var c = attCounts(s, x.id), o = x.placement ? oppOfPlacement(s, x.placement) : null, rep = s.reports.filter(function (r) { return r.student === x.id; })[0];
      var repSt = rep ? rep.status : 'Not submitted';
      return { id: x.id, student: stuName(s, x.id), major: x.major, placement: o ? compName(s, o.company) + ' · ' + o.title : '—', days: c.days, approved: c.approved, pending: c.pending, rejected: c.rejected,
        att: x.placement ? attRate(s, x.id) : '—', report: stLabel(app, repSt), _report: repSt, cScore: dash(latestScore(s, x.id, 'Company')), aScore: dash(latestScore(s, x.id, 'Academic')) };
    });
    var cols = [{ key: 'student', header: t('colStudent') }, { key: 'major', header: t('colMajor') }, { key: 'placement', header: t('colPlacement') },
      { key: 'days', header: t('colDays'), align: 'end' }, { key: 'approved', header: t('colApproved'), align: 'end' }, { key: 'pending', header: t('colPending'), align: 'end' }, { key: 'rejected', header: t('colRejected'), align: 'end' },
      { key: 'att', header: t('colAttRate'), align: 'end' }, { key: 'report', header: t('colReport'), badge: true }, { key: 'cScore', header: t('colCompanyScore'), align: 'end' }, { key: 'aScore', header: t('colAcademicScore'), align: 'end' }];
    var placed = list.filter(function (x) { return x.placement; });
    var avgAtt = avg(placed.map(function (x) { return attRate(s, x.id); }));
    var appr = rows.filter(function (r) { return r._report === 'Approved'; }).length;
    var avgC = avg(list.map(function (x) { return latestScore(s, x.id, 'Company'); }));
    var all = { value: '', label: t('all') };
    var filters = [t('semester') + ' ' + sem[0], major[0] || t('all'), pl[0] ? plOpts.filter(function (o) { return o.value === pl[0]; })[0].label : t('allPlacements')].join(' · ');
    return h(Page, { title: t('academicReportsTitle'), lead: t('academicReportsLead') },
      h(ReportView, { title: t('academicReportsTitle'), logName: 'academic cohort', file: 'E-COOP_Academic_Report_' + TODAY, filters: filters, columns: cols, rows: rows,
        stats: [[t('stStudents'), String(rows.length)], [t('stAvgAtt'), avgAtt == null ? '—' : avgAtt + '%', avgAtt], [t('stReportsApproved'), frac(appr, rows.length)], [t('stAvgCompany'), avgC == null ? '—' : avgC + '/100', avgC]],
        params: h('div', { className: 'form-3' },
          h(C.TextField, { id: 'ar-sem', label: t('semester'), value: sem[0], options: [{ value: '481', label: t('semester481') }], onChange: function (e) { sem[1](e.target.value); } }),
          h(C.TextField, { id: 'ar-major', label: t('filterMajor'), value: major[0], options: [all].concat(majors.sort()), onChange: function (e) { major[1](e.target.value); } }),
          h(C.TextField, { id: 'ar-pl', label: t('cohort'), value: pl[0], options: [{ value: '', label: t('allPlacements') }].concat(plOpts), onChange: function (e) { pl[1](e.target.value); } })) }));
  }

  function InstitutionReports() {
    var app = useApp(), s = app.state, t = app.t, kind = R.useState('placement');
    var active = s.students.filter(function (x) { return by(s.users, x.user).status === 'Active'; });
    var cols, rows, totalRow = null, stats, title;
    if (kind[0] === 'placement') {
      title = t('rPlacement');
      var majors = []; active.forEach(function (x) { if (majors.indexOf(x.major) < 0) majors.push(x.major); }); majors.sort();
      var T = { n: 0, p: 0, a: 0, acc: 0 };
      rows = majors.map(function (m) {
        var st = active.filter(function (x) { return x.major === m; }), ids = st.map(function (x) { return x.id; });
        var placedN = st.filter(function (x) { return x.placement; }).length, ap = s.apps.filter(function (a) { return ids.indexOf(a.student) >= 0; });
        var acc = ap.filter(function (a) { return a.status === 'Accepted'; }).length;
        T.n += st.length; T.p += placedN; T.a += ap.length; T.acc += acc;
        return { id: m, major: m, students: st.length, placed: placedN, unplaced: st.length - placedN, rate: Math.round(placedN * 100 / st.length), apps: ap.length, acc: acc };
      });
      totalRow = { major: t('total'), students: T.n, placed: T.p, unplaced: T.n - T.p, rate: T.n ? Math.round(T.p * 100 / T.n) : 0, apps: T.a, acc: T.acc };
      cols = [{ key: 'major', header: t('colMajor') }, { key: 'students', header: t('colStudents'), align: 'end' }, { key: 'placed', header: t('colPlaced'), align: 'end' }, { key: 'unplaced', header: t('colUnplaced'), align: 'end' },
        { key: 'rate', header: t('colPlacementRate'), align: 'end' }, { key: 'apps', header: t('colApplications'), align: 'end' }, { key: 'acc', header: t('colAccepted'), align: 'end' }];
      stats = [[t('stStudents'), String(T.n)], [t('stPlacementRate'), totalRow.rate + '%', totalRow.rate], [t('colApplications'), String(T.a)]];
    } else if (kind[0] === 'companies') {
      title = t('rCompanies');
      rows = s.companies.map(function (c) {
        var opps = s.opps.filter(function (o) { return o.company === c.id; }), oids = opps.map(function (o) { return o.id; });
        var open = opps.filter(function (o) { return o.status === 'Open'; });
        var ap = s.apps.filter(function (a) { return oids.indexOf(a.opp) >= 0; });
        var pids = s.placements.filter(function (p) { return oids.indexOf(p.opp) >= 0; }).map(function (p) { return p.id; });
        var tr = s.students.filter(function (x) { return pids.indexOf(x.placement) >= 0; });
        return { id: c.id, company: c.name, city: cityLabel(c.city, app.lang), status: stLabel(app, c.status), _status: c.status, opps: open.length + ' / ' + opps.length,
          seats: open.reduce(function (a, o) { return a + o.capacity; }, 0), apps: ap.length, acc: ap.filter(function (a) { return a.status === 'Accepted'; }).length, trainees: tr.length,
          score: dash(avg(tr.map(function (x) { return latestScore(s, x.id, 'Company'); }))) };
      });
      cols = [{ key: 'company', header: t('colCompany') }, { key: 'city', header: t('colCity') }, { key: 'status', header: t('colStatus'), badge: true }, { key: 'opps', header: t('colOpps'), align: 'end' },
        { key: 'seats', header: t('colSeats'), align: 'end' }, { key: 'apps', header: t('colApplications'), align: 'end' }, { key: 'acc', header: t('colAccepted'), align: 'end' },
        { key: 'trainees', header: t('colTrainees'), align: 'end' }, { key: 'score', header: t('colAvgScore'), align: 'end' }];
      stats = [[t('stCompanies'), frac(s.companies.filter(function (c) { return c.status === 'Approved'; }).length, s.companies.length)], [t('colTrainees'), String(rows.reduce(function (a, r) { return a + r.trainees; }, 0))]];
    } else {
      title = t('rCompletion');
      var placed = s.students.filter(function (x) { return x.placement; });
      rows = placed.map(function (x) {
        var o = oppOfPlacement(s, x.placement), rep = s.reports.filter(function (r) { return r.student === x.id; })[0], repSt = rep ? rep.status : 'Not submitted';
        var cS = latestScore(s, x.id, 'Company'), aS = latestScore(s, x.id, 'Academic');
        var done = repSt === 'Approved' && cS != null && aS != null;
        return { id: x.id, student: stuName(s, x.id), major: x.major, company: compName(s, o.company), sup: supName(s, x.supervisor) || '—', att: attRate(s, x.id),
          report: stLabel(app, repSt), _report: repSt, cScore: dash(cS), aScore: dash(aS), status: done ? t('completedLbl') : t('inProgress'), _status: done ? 'Completed' : 'In progress' };
      });
      cols = [{ key: 'student', header: t('colStudent') }, { key: 'major', header: t('colMajor') }, { key: 'company', header: t('colCompany') }, { key: 'sup', header: t('colSupervisor') },
        { key: 'att', header: t('colAttRate'), align: 'end' }, { key: 'report', header: t('colReport'), badge: true }, { key: 'cScore', header: t('colCompanyScore'), align: 'end' },
        { key: 'aScore', header: t('colAcademicScore'), align: 'end' }, { key: 'status', header: t('colStatus'), badge: true }];
      var doneN = rows.filter(function (r) { return r._status === 'Completed'; }).length, cr = rows.length ? Math.round(doneN * 100 / rows.length) : 0;
      var aa = avg(placed.map(function (x) { return attRate(s, x.id); }));
      stats = [[t('stCompletion'), cr + '%', cr], [t('stAvgAtt'), aa == null ? '—' : aa + '%', aa], [t('stReportsApproved'), frac(rows.filter(function (r) { return r._report === 'Approved'; }).length, rows.length)]];
    }
    var files = { placement: 'Placement_by_Major', companies: 'Company_Participation', completion: 'Completion_and_Evaluations' };
    return h(Page, { title: t('instReportsTitle'), lead: t('instReportsLead') },
      h(ReportView, { title: title, logName: title.toLowerCase(), file: 'E-COOP_' + files[kind[0]] + '_' + TODAY, filters: t('semester') + ' 481', columns: cols, rows: rows, totalRow: totalRow, stats: stats,
        params: h(Segmented, { id: 'ir-kind', label: t('reportType'), value: kind[0], onChange: kind[1], options: [{ value: 'placement', label: t('rPlacement') }, { value: 'companies', label: t('rCompanies') }, { value: 'completion', label: t('rCompletion') }] }) }));
  }

  /* ================= SETTINGS (all roles) ================= */
  var PREFS = [['attendance', 'pAttendance', 'pAttendanceD'], ['applications', 'pApplications', 'pApplicationsD'], ['reports', 'pReports', 'pReportsD'], ['summary', 'pSummary', 'pSummaryD']];
  function Settings() {
    var app = useApp(), s = app.state, t = app.t, me = app.me;
    function pref(k) { var v = s.prefs[me.id + ':' + k]; return v == null ? k !== 'summary' : v; }
    function onPhoto(e) {
      var f = e.target.files && e.target.files[0]; e.target.value = '';
      if (!f) return;
      readAvatar(f).then(function (photo) { app.act({ type: 'avatar', user: me.id, photo: photo }, t('photoUpdated'), 'success', true); },
        function (err) { app.toast('danger', err.message === 'size' ? t('photoTooBig') : t('photoType')); });
    }
    return h(Page, { title: t('settingsTitle'), extra: h('div', { className: 'glass glass-me' },
      h('div', { className: 'me-photo' }, h(Avatar, { name: me.name, size: 'xl' }),
        h('button', { type: 'button', className: 'photo-edit', title: me.avatar ? t('changePhoto') : t('addPhoto'), 'aria-label': me.avatar ? t('changePhoto') : t('addPhoto'), onClick: function () { var el = document.getElementById('avatar-input'); if (el) el.click(); } }, h(Icon, { name: 'camera', size: 18 })),
        h('input', { id: 'avatar-input', type: 'file', accept: 'image/png,image/jpeg,image/webp', className: 'sr-only', tabIndex: -1, 'aria-hidden': 'true', onChange: onPhoto })),
      h('div', { className: 'me-info' }, h('p', { className: 'glass-value' }, me.name), h('p', { className: 'glass-note' }, t(ROLE_KEY[me.role]) + ' · ' + me.email),
        h('div', { className: 'photo-actions' },
          me.avatar ? h('button', { className: 'glass-btn', onClick: function () { app.act({ type: 'avatar', user: me.id, photo: null }, t('photoRemoved'), 'info', true); } }, t('removePhoto')) : null,
          h('span', { className: 'glass-note' }, t('photoHint'))))) },
      h('div', { className: 'split split-even' },
        h('div', { className: 'stack' },
          h(Section, { title: t('profileSection') }, h(Card, null, me.role === 'Student' ? h(ProfileForm) : me.role === 'Company' ? h(CompanyProfileForm) : h('div', { className: 'kv-list' },
            h('div', null, h('span', null, t('fullName')), h('b', null, me.name)), h('div', null, h('span', null, t('email')), h('b', { className: 'ltr' }, me.email)), userPhone(s, me) ? h('div', null, h('span', null, t('phone')), h('b', { className: 'ltr' }, userPhone(s, me))) : null, h('div', null, h('span', null, t('role')), h('b', null, t(ROLE_KEY[me.role])))))),
          h(Section, { title: t('helpSection') }, h(Card, { className: 'stack' }, h(C.Button, { variant: 'secondary', onClick: app.startTour }, t('showTour')), h(C.Button, { variant: 'danger', onClick: app.signOut }, t('signOut'))))),
        h('div', { className: 'stack' },
          h(Section, { title: t('notifSection') }, h(Card, { className: 'switches' }, PREFS.map(function (p) {
            return h(Switch, { key: p[0], id: 'pref-' + p[0], title: t(p[1]), desc: t(p[2]), on: pref(p[0]), onChange: function (v) { app.act({ type: 'pref', user: me.id, key: p[0], value: v }, t('prefSaved'), 'success', false); } });
          }))),
          h(Section, { title: t('appearance') }, h(Card, { className: 'stack' },
            h(Segmented, { id: 'set-lang', label: t('languageLabel'), value: app.lang, onChange: app.setLang, options: [{ value: 'en', label: 'English' }, { value: 'ar', label: 'العربية' }] }),
            h(Segmented, { id: 'set-theme', label: t('themeLabel'), value: app.theme, onChange: app.setTheme, options: [{ value: 'light', label: t('themeLight') }, { value: 'dark', label: t('themeDark') }] }))))));
  }

  /* ================= ONBOARDING ================= */
  var TOUR = { Student: 'tStudent', Company: 'tCompany', Supervisor: 'tSupervisor', Admin: 'tAdmin' };
  var TOUR_ICON = { Student: ['brief', 'key', 'doc'], Company: ['plus', 'users', 'key'], Supervisor: ['users', 'check', 'star'], Admin: ['shield', 'users', 'chart'] };
  function Tour(p) {
    var app = useApp(), t = app.t, step = R.useState(0), n = step[0], pre = TOUR[app.me.role];
    return h(Sheet, { label: t(pre + (n + 1) + 't'), onClose: p.onClose, hideClose: true },
      h('div', { className: 'tour' },
        h('span', { className: 'tour-icon' }, h(Icon, { name: TOUR_ICON[app.me.role][n], size: 32 })),
        h('p', { className: 'tour-step' }, fill(t('tourStep'), { n: n + 1, m: 3 })),
        h('h2', { className: 'tour-title' }, t(pre + (n + 1) + 't')),
        h('p', { className: 'tour-text' }, t(pre + (n + 1) + 'd')),
        h('div', { className: 'dots', 'aria-hidden': true }, [0, 1, 2].map(function (i) { return h('span', { key: i, className: 'dot' + (i === n ? ' on' : '') }); })),
        h('div', { className: 'tour-actions' },
          n > 0 ? h(C.Button, { variant: 'secondary', onClick: function () { step[1](n - 1); } }, t('tourBack')) : h(C.Button, { variant: 'ghost', onClick: p.onClose }, t('tourSkip')),
          n < 2 ? h(C.Button, { onClick: function () { step[1](n + 1); } }, t('tourNext')) : h(C.Button, { onClick: p.onClose }, t('tourDone')))));
  }

  /* ================= NOTIFICATIONS ================= */
  function Notifications(p) {
    var app = useApp(), s = app.state, t = app.t;
    var mine = s.notifs.filter(function (n) { return n.user === app.me.id; }).slice(0, 20);
    return h(Sheet, { label: t('notifications'), onClose: p.onClose,
      head: h('div', { className: 'sheet-head sheet-head-row' }, h('p', { className: 'sheet-name' }, t('notifications')), mine.some(function (n) { return !n.read; }) ? h(C.Button, { variant: 'ghost', size: 'sm', onClick: function () { app.dispatch({ type: 'readNotifs', user: app.me.id }); } }, t('markAllRead')) : null) },
      mine.length ? h('ul', { className: 'notifs' }, mine.map(function (n) {
        return h('li', { key: n.id, className: 'notif' + (n.read ? '' : ' unread') }, h('span', { className: 'notif-dot', 'aria-hidden': true }),
          h('div', null, h('p', { className: 'notif-text' }, fill(t(n.key), Object.assign({}, n.args, n.args.d ? { d: fmtDate(n.args.d, app.lang) } : {}))), h('p', { className: 'notif-when' }, relDay(n.ts, t) + ' · ' + n.ts.slice(11))));
      })) : h(Empty, { title: t('noNotifs') }));
  }

  /* ================= CHAT (FamilyLink-style messaging) ================= */
  function chatUnread(s, uid, ch) {
    var last = s.reads[uid + ':' + ch.id] || '';
    return s.msgs.filter(function (m) { return m.chat === ch.id && m.from !== uid && m.ts > last; }).length;
  }
  function myChats(s, uid) { return s.chats.filter(function (c) { return c.members.indexOf(uid) >= 0; }); }
  function totalUnread(s, uid) { return myChats(s, uid).reduce(function (a, c) { return a + chatUnread(s, uid, c); }, 0); }
  function chatName(s, ch, uid, t) {
    if (ch.kind === 'group') return ch.title;
    var other = ch.members.filter(function (m) { return m !== uid; })[0];
    return userName(s, other);
  }
  function roleOfUser(s, uid, t) { var u = by(s.users, uid); return u ? t(ROLE_KEY[u.role]) : ''; }

  /* who each role may start a direct conversation with */
  function contactsFor(s, me) {
    var ids = [];
    function add(uid) { if (uid && uid !== me.id && ids.indexOf(uid) < 0) { var u = by(s.users, uid); if (u && u.status === 'Active') ids.push(uid); } }
    var admins = s.users.filter(function (u) { return u.role === 'Admin'; }).map(function (u) { return u.id; });
    if (me.role === 'Student') {
      var st = by(s.students, me.ref);
      var su = supUser(s, st.supervisor); if (su) add(su.id);
      if (st.placement) add(compUser(s, oppOfPlacement(s, st.placement).company));
      admins.forEach(add);
    } else if (me.role === 'Company') {
      var opIds = s.opps.filter(function (o) { return o.company === me.ref; }).map(function (o) { return o.id; });
      var pids = s.placements.filter(function (p) { return opIds.indexOf(p.opp) >= 0; }).map(function (p) { return p.id; });
      s.students.filter(function (x) { return pids.indexOf(x.placement) >= 0; }).forEach(function (x) { add(x.user); var sv = supUser(s, x.supervisor); if (sv) add(sv.id); });
      admins.forEach(add);
    } else if (me.role === 'Supervisor') {
      s.students.filter(function (x) { return x.supervisor === me.ref; }).forEach(function (x) { add(x.user); if (x.placement) add(compUser(s, oppOfPlacement(s, x.placement).company)); });
      admins.forEach(add);
    } else {
      s.users.forEach(function (u) { add(u.id); });
    }
    return ids;
  }

  function ChatPage() {
    var app = useApp(), s = app.state, t = app.t, me = app.me;
    var list = myChats(s, me.id).map(function (c) {
      var ms = s.msgs.filter(function (m) { return m.chat === c.id; });
      return { ch: c, last: ms[ms.length - 1] || null, unread: chatUnread(s, me.id, c) };
    }).sort(function (a, b) { var x = a.last ? a.last.ts : '', y = b.last ? b.last.ts : ''; return x < y ? 1 : x > y ? -1 : 0; });
    var initial = app.params && app.params.chat ? app.params.chat : null;
    var sel = R.useState(initial), picker = R.useState(false);
    var wide = R.useState(function () { return window.matchMedia('(min-width: 860px)').matches; });
    R.useEffect(function () { var mq = window.matchMedia('(min-width: 860px)'); function on() { wide[1](mq.matches); } mq.addEventListener('change', on); return function () { mq.removeEventListener('change', on); }; }, []);
    var current = sel[0] ? by(s.chats, sel[0]) : (wide[0] && list[0] ? list[0].ch : null);
    R.useEffect(function () { if (current && chatUnread(s, me.id, current)) app.dispatch({ type: 'readChat', user: me.id, chat: current.id }); }, [current && current.id, s.msgs.length]);
    function openWith(uid) {
      picker[1](false);
      var ex = s.chats.filter(function (c) { return c.kind === 'direct' && c.members.indexOf(uid) >= 0 && c.members.indexOf(me.id) >= 0; })[0];
      if (ex) { sel[1](ex.id); return; }
      var nid = 'ch' + (s.seq + 1);
      app.dispatch({ type: 'newChat', members: [me.id, uid] });
      sel[1](nid);
    }
    var showList = wide[0] || !current, showThread = wide[0] || !!current;
    var totalU = totalUnread(s, me.id);
    return h(Page, { title: t('chatTitle'), lead: totalU ? fill(t('unreadN'), { n: totalU }) : t('chatLead'),
      action: h('button', { className: 'hero-btn', onClick: function () { picker[1](true); } }, h(Icon, { name: 'plus', size: 18 }), t('newMessage')) },
      h('div', { className: 'chat' + (current ? ' has-thread' : '') },
        showList ? h('div', { className: 'chat-list', role: 'navigation', 'aria-label': t('conversations') },
          list.length ? list.map(function (r) {
            var name = chatName(s, r.ch, me.id, t), on = current && current.id === r.ch.id;
            var sub = r.ch.kind === 'group' ? t('groupChat') + ' · ' + r.ch.members.length + ' ' + t('members') : roleOfUser(s, r.ch.members.filter(function (m) { return m !== me.id; })[0], t);
            return h('button', { key: r.ch.id, className: 'chat-row' + (on ? ' on' : '') + (r.unread ? ' unread' : ''), 'aria-current': on ? 'true' : undefined, onClick: function () { sel[1](r.ch.id); } },
              r.ch.kind === 'group' ? h('span', { className: 'avatar avatar-group', 'aria-hidden': true }, h(Icon, { name: 'users', size: 20 })) : h(Avatar, { name: name }),
              h('span', { className: 'chat-row-main' },
                h('span', { className: 'chat-row-top' }, h('span', { className: 'chat-row-name' }, name), r.last ? h('span', { className: 'chat-row-when' }, relDay(r.last.ts, t)) : null),
                h('span', { className: 'chat-row-sub' }, sub),
                r.last ? h('span', { className: 'chat-row-last' }, (r.last.from === me.id ? t('you') + ': ' : '') + r.last.text) : null),
              r.unread ? h('span', { className: 'chat-badge', 'aria-label': fill(t('unreadN'), { n: r.unread }) }, r.unread) : null);
          }) : h('p', { className: 'chat-empty' }, t('noChats'))) : null,
        showThread ? (current ? h(Thread, { ch: current, key: current.id, onBack: wide[0] ? null : function () { sel[1](null); } }) : h('div', { className: 'chat-thread chat-none' }, h(Icon, { name: 'chat', size: 32 }), h('p', null, t('pickChat')))) : null),
      picker[0] ? h(ContactPicker, { onPick: openWith, onClose: function () { picker[1](false); } }) : null);
  }

  function Thread(p) {
    var app = useApp(), s = app.state, t = app.t, me = app.me, ch = p.ch;
    var msgs = s.msgs.filter(function (m) { return m.chat === ch.id; });
    var draft = R.useState(''), scroller = R.useRef(null), input = R.useRef(null);
    R.useEffect(function () { var el = scroller.current; if (el) el.scrollTop = el.scrollHeight; }, [msgs.length]);
    var text = draft[0], tooLong = text.length > 1000;
    function send(e) {
      if (e) e.preventDefault();
      var v = text.trim(); if (!v || tooLong) return;
      app.dispatch({ type: 'sendMsg', chat: ch.id, from: me.id, text: v });
      draft[1](''); if (input.current) input.current.focus();
    }
    var name = chatName(s, ch, me.id, t);
    var rows = [], lastDay = null;
    msgs.forEach(function (m, i) {
      var day = m.ts.slice(0, 10);
      if (day !== lastDay) { rows.push(h('li', { key: 'd' + day, className: 'day-sep' }, h('span', null, relDay(day, t) === t('today') || relDay(day, t) === t('yesterday') ? relDay(day, t) : fmtDate(day, app.lang)))); lastDay = day; }
      var mine = m.from === me.id, prev = msgs[i - 1], grouped = prev && prev.from === m.from && prev.ts.slice(0, 10) === day;
      rows.push(h('li', { key: m.id, className: 'msg' + (mine ? ' mine' : '') + (grouped ? ' grouped' : '') },
        !mine ? (grouped ? h('span', { className: 'msg-av-space', 'aria-hidden': true }) : h(Avatar, { name: userName(s, m.from), size: 'sm' })) : null,
        h('div', { className: 'msg-col' },
          !mine && ch.kind === 'group' && !grouped ? h('span', { className: 'msg-from' }, userName(s, m.from) + ' · ' + roleOfUser(s, m.from, t)) : null,
          h('div', { className: 'bubble' }, m.text),
          h('span', { className: 'msg-time' }, m.ts.slice(11)))));
    });
    return h('section', { className: 'chat-thread', 'aria-label': name },
      h('header', { className: 'thread-head' },
        p.onBack ? h('button', { className: 'thread-back', onClick: p.onBack, 'aria-label': t('backToList') }, app.lang === 'ar' ? '→' : '←') : null,
        ch.kind === 'group' ? h('span', { className: 'avatar avatar-group', 'aria-hidden': true }, h(Icon, { name: 'users', size: 20 })) : h(Avatar, { name: name }),
        h('div', { className: 'thread-id' }, h('p', { className: 'thread-name' }, name),
          h('p', { className: 'thread-sub' }, ch.kind === 'group' ? ch.members.map(function (u) { return u === me.id ? t('you') : userName(s, u).split(' ')[0]; }).join(', ') : roleOfUser(s, ch.members.filter(function (m) { return m !== me.id; })[0], t)))),
      h('ol', { className: 'msgs', ref: scroller, 'aria-live': 'polite' }, rows.length ? rows : h('li', { className: 'chat-empty' }, t('chatPrivacy'))),
      h('form', { className: 'composer', onSubmit: send },
        h('label', { htmlFor: 'composer-' + ch.id, className: 'sr-only' }, t('typeMessage')),
        h('textarea', { id: 'composer-' + ch.id, ref: input, className: 'composer-input', rows: 1, placeholder: t('typeMessage'), value: text, 'aria-invalid': tooLong ? 'true' : undefined,
          onChange: function (e) { draft[1](e.target.value); }, onKeyDown: function (e) { if (e.key === 'Enter' && !e.shiftKey && !e.nativeEvent.isComposing) { e.preventDefault(); send(); } } }),
        h('button', { type: 'submit', className: 'composer-send', disabled: !text.trim() || tooLong, 'aria-label': t('send') }, h(Icon, { name: 'send', size: 20 }))),
      tooLong ? h('p', { className: 'coop-error composer-err' }, t('msgTooLong')) : null);
  }

  /* phone numbers: students keep theirs on the student record; staff and companies in USER_PHONES */
  function userPhone(s, u) { if (!u) return ''; if (u.phone) return u.phone; if (u.role === 'Student') { var st = by(s.students, u.ref); return st ? st.phone : ''; } return USER_PHONES[u.id] || ''; }
  function normPhone(v) { var d = String(v).replace(/[^0-9]/g, ''); if (d.indexOf('00966') === 0) d = d.slice(5); else if (d.indexOf('966') === 0) d = d.slice(3); if (d.length === 9 && d[0] !== '0') d = '0' + d; return d; }
  function lookupUser(s, q) {
    var v = q.trim();
    if (/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(v)) return { kind: 'email', user: s.users.filter(function (u) { return u.email.toLowerCase() === v.toLowerCase(); })[0] || null };
    var ph = normPhone(v);
    if (/^0\d{9}$/.test(ph)) return { kind: 'phone', user: s.users.filter(function (u) { return normPhone(userPhone(s, u)) === ph; })[0] || null };
    return { kind: 'invalid', user: null };
  }

  function ContactPicker(p) {
    var app = useApp(), s = app.state, t = app.t, ids = contactsFor(s, app.me);
    var q = R.useState(''), tried = R.useState(false);
    var res = q[0].trim() ? lookupUser(s, q[0]) : null;
    var found = res && res.user;
    function msgFor() {
      if (!res) return null;
      if (res.kind === 'invalid') return tried[0] ? { tone: 'error', text: t('invalidLookup') } : null;
      if (!found) return { tone: 'error', text: t('noMatch') };
      if (found.id === app.me.id) return { tone: 'error', text: t('cannotSelf') };
      if (found.status !== 'Active') return { tone: 'error', text: t('suspendedContact') };
      return null;
    }
    var m = msgFor(), canOpen = found && found.id !== app.me.id && found.status === 'Active';
    function submit(e) { e.preventDefault(); tried[1](true); if (canOpen) p.onPick(found.id); }
    function row(u, extra) {
      return h('button', { className: 'contact', onClick: function () { p.onPick(u.id); } }, h(Avatar, { name: u.name }),
        h('span', { className: 'contact-main' }, h('span', { className: 'contact-name' }, u.name), h('span', { className: 'contact-role' }, t(ROLE_KEY[u.role]) + (extra ? ' · ' : '')), extra ? h('span', { className: 'contact-id ltr' }, extra) : null),
        h('span', { className: 'contact-go' }, t('startChat')));
    }
    return h(Sheet, { label: t('newMessage'), onClose: p.onClose, head: h('div', { className: 'sheet-head sheet-head-row' }, h('p', { className: 'sheet-name' }, t('newMessage'))) },
      h('form', { className: 'lookup', onSubmit: submit, noValidate: true },
        h(C.TextField, { id: 'lookup', label: t('searchContact'), type: 'text', inputMode: 'email', autoComplete: 'off', value: q[0], placeholder: 'name@jic.edu.sa · 05XXXXXXXX',
          help: m ? null : t('searchHelp'), error: m ? m.text : null, onChange: function (e) { q[1](e.target.value); tried[1](false); } }),
        canOpen ? h('div', { className: 'lookup-hit' }, h('p', { className: 'coop-label' }, t('found')), row(found, res.kind === 'email' ? found.email : userPhone(s, found)))
          : h(Row, { end: true }, h(C.Button, { type: 'submit', disabled: !q[0].trim() }, t('findAccount')))),
      h('p', { className: 'demo-note' }, h('span', { className: 'demo-tag' }, t('demo')), t('lookupDemo')),
      ids.length ? h('div', { className: 'stack-sm' }, h('p', { className: 'coop-label' }, t('suggested')),
        h('ul', { className: 'contacts' }, ids.map(function (uid) { return h('li', { key: uid }, row(by(s.users, uid))); }))) : null);
  }

  /* ================= REPORT VIEWER ================= */
  function SampleReport(p) {
    var app = useApp(), s = app.state, rep = p.report, st = by(s.students, rep.student), name = stuName(s, rep.student);
    var o = st && st.placement ? oppOfPlacement(s, st.placement) : null, pl = st && st.placement ? by(s.placements, st.placement) : null;
    var co = o ? compName(s, o.company) : '—';
    var secs = [
      ['1. Introduction', 'This report summarises my cooperative training as ' + (o ? o.title : 'a trainee') + ' at ' + co + ' from ' + (pl ? fmtDate(pl.start, 'en') : '—') + ' to ' + (pl ? fmtDate(pl.end, 'en') : '—') + '. It covers the company, my tasks, the skills I built and the challenges I faced.'],
      ['2. Company overview', co + ' operates in the Eastern Province of Saudi Arabia. My department supports daily operations and reports to the operations manager. The team I joined has six full-time staff.'],
      ['3. Tasks and responsibilities', (o ? o.description : '') + ' I also attended the weekly KPI review and prepared short summaries for my line manager.'],
      ['4. Skills gained', 'I improved my Excel skills (pivot tables, lookups and charts), learned how the ERP system records stock movements, and practised presenting results clearly to non-specialists.'],
      ['5. Challenges', 'The main challenge was reconciling data from two systems that use different item codes. I built a mapping sheet with my supervisor, which cut the weekly reconciliation from one day to two hours.'],
      ['6. Conclusion', 'The placement connected my Business Administration courses to real operations. I recommend continuing the partnership between the college and ' + co + '.']
    ];
    return h('article', { className: 'paper', lang: 'en', dir: 'ltr' },
      h('p', { className: 'paper-kicker' }, 'Jubail Industrial College · Department of Business Administration'),
      h('h1', { className: 'paper-title' }, 'Final Cooperative Training Report'),
      h('dl', { className: 'paper-meta' },
        h('dt', null, 'Student'), h('dd', null, name),
        h('dt', null, 'Major'), h('dd', null, st ? st.major : ''),
        h('dt', null, 'Company'), h('dd', null, co),
        h('dt', null, 'Academic supervisor'), h('dd', null, st ? supName(s, st.supervisor) : ''),
        h('dt', null, 'Submitted'), h('dd', null, fmtDate(rep.date, 'en'))),
      secs.map(function (x) { return h('section', { key: x[0] }, h('h2', null, x[0]), h('p', null, x[1])); }));
  }

  function ReportViewer(p) {
    var app = useApp(), s = app.state, t = app.t, rep = by(s.reports, p.reportId);
    var st = R.useState({ kind: 'loading' }), view = st[0], setView = st[1];
    var pagesRef = R.useRef(null), boxRef = R.useRef(null), closeRef = R.useRef(null);
    R.useEffect(function () {
      var prev = document.activeElement; if (closeRef.current) closeRef.current.focus();
      function onKey(e) { if (e.key === 'Escape') p.onClose(); }
      document.addEventListener('keydown', onKey);
      return function () { document.removeEventListener('keydown', onKey); if (prev && prev.focus) prev.focus(); };
    }, []);
    R.useEffect(function () {
      var alive = true;
      if (!rep) return;
      if (!rep.fileKey) { setView({ kind: 'sample' }); return; }
      var isPdf = /\.pdf$/i.test(rep.file);
      FileStore.get(rep.fileKey).then(function (rec) {
        if (!alive) return;
        if (!rec) { setView({ kind: 'missing' }); return; }
        return rec.blob.arrayBuffer().then(function (buf) {
          if (isPdf) {
            return LibLoader.pdf().then(function (pdfjs) {
              return pdfjs.getDocument({ data: new Uint8Array(buf), isEvalSupported: false }).promise;
            }).then(function (doc) {
              if (!alive) return;
              setView({ kind: 'pdf', pages: doc.numPages });
              var host = pagesRef.current; if (!host) return;
              host.innerHTML = '';
              var width = Math.min((boxRef.current ? boxRef.current.clientWidth : 800) - 32, 820);
              var chain = Promise.resolve();
              for (var i = 1; i <= doc.numPages; i++) (function (n) {
                chain = chain.then(function () {
                  return doc.getPage(n).then(function (page) {
                    if (!alive) return;
                    var base = page.getViewport({ scale: 1 }), scale = width / base.width, dpr = window.devicePixelRatio || 1;
                    var vp = page.getViewport({ scale: scale * dpr });
                    var c = document.createElement('canvas'); c.width = vp.width; c.height = vp.height;
                    c.style.width = Math.round(vp.width / dpr) + 'px'; c.style.height = Math.round(vp.height / dpr) + 'px';
                    c.className = 'pdf-page'; c.setAttribute('aria-label', fill(t('pageN'), { n: n, m: doc.numPages }));
                    host.appendChild(c);
                    return page.render({ canvasContext: c.getContext('2d'), viewport: vp }).promise;
                  });
                });
              })(i);
              return chain;
            });
          }
          return LibLoader.docx().then(function (mammoth) { return mammoth.convertToHtml({ arrayBuffer: buf }); }).then(function (out) { if (alive) setView({ kind: 'docx', html: out.value }); });
        });
      }).catch(function (e) { if (alive) setView({ kind: /unavailable|load /.test(String(e && e.message)) ? 'nolib' : 'error' }); });
      return function () { alive = false; };
    }, [p.reportId, rep && rep.fileKey]);
    if (!rep) return null;
    var name = stuName(s, rep.student);
    var canDecide = app.me.role === 'Supervisor' && rep.status === 'Submitted';
    return h('div', { className: 'viewer-wrap', onClick: function (e) { if (e.target === e.currentTarget) p.onClose(); } },
      h('div', { className: 'viewer', role: 'dialog', 'aria-modal': 'true', 'aria-label': rep.file },
        h('header', { className: 'viewer-head' },
          h('span', { className: 'viewer-icon' }, h(Icon, { name: 'doc', size: 22 })),
          h('div', { className: 'viewer-id' }, h('p', { className: 'viewer-name' }, rep.file), h('p', { className: 'viewer-sub' }, name + ' · ' + t('submitted') + ' ' + fmtDate(rep.date, app.lang) + (view.kind === 'pdf' ? ' · ' + view.pages + ' ' + t('pagesWord') : ''))),
          h(Badge, { status: rep.status }),
          h('button', { className: 'viewer-close', ref: closeRef, onClick: p.onClose }, h(Icon, { name: 'x', size: 18 }), h('span', null, t('close')))),
        h('div', { className: 'viewer-body', ref: boxRef },
          view.kind === 'sample' ? h(R.Fragment, null, h('p', { className: 'viewer-note' }, h('span', { className: 'demo-tag' }, t('demo')), t('sampleNote')), h(SampleReport, { report: rep })) : null,
          view.kind === 'loading' ? h('p', { className: 'viewer-msg' }, t('loadingFile')) : null,
          view.kind === 'missing' ? h('div', { className: 'viewer-msg' }, h(C.Alert, { tone: 'warning', title: t('missingFile') })) : null,
          view.kind === 'nolib' ? h('div', { className: 'viewer-msg' }, h(C.Alert, { tone: 'danger', title: t('noViewer') })) : null,
          view.kind === 'error' ? h('div', { className: 'viewer-msg' }, h(C.Alert, { tone: 'danger', title: t('fileError') })) : null,
          view.kind === 'docx' ? h('article', { className: 'paper paper-docx', dangerouslySetInnerHTML: { __html: view.html } }) : null,
          h('div', { className: 'pdf-pages', ref: pagesRef, hidden: view.kind !== 'pdf' })),
        canDecide ? h('footer', { className: 'viewer-foot' },
          h(C.Button, { variant: 'secondary', onClick: function () { app.act({ type: 'reportDecision', id: rep.id, status: 'Request Revision', by: app.me.id }, t('revisionToast'), 'info', true); p.onClose(); } }, t('requestRevision')),
          h(C.Button, { onClick: function () { app.act({ type: 'reportDecision', id: rep.id, status: 'Approved', by: app.me.id }, t('reportApprovedToast'), 'success', true); p.onClose(); } }, t('approve'))) : null));
  }

  function FileButton(p) {
    return h('button', { className: 'file-btn', onClick: p.onClick, 'aria-label': p.label + ': ' + p.file }, h(Icon, { name: 'doc', size: 18 }), h('span', { className: 'file-btn-name' }, p.file), h('span', { className: 'file-btn-open' }, p.openText));
  }

  /* ================= SHELL ================= */
  var ROUTES = {
    Student: [['dashboard', 'navDashboard', StudentDashboard], ['opportunities', 'navOpportunities', StudentOpportunities], ['applications', 'navApplications', StudentApplications], ['attendance', 'navAttendance', StudentAttendance], ['report', 'navReport', StudentReport], ['chat', 'navChat', ChatPage], ['settings', 'navSettings', Settings]],
    Company: [['overview', 'navOverview', CompanyOverview], ['postings', 'navPostings', PostingsPage], ['applicants', 'navApplicants', ApplicantsPage], ['dailycode', 'navDailyCode', DailyCodePage], ['trainees', 'navTrainees', TraineesPage], ['chat', 'navChat', ChatPage], ['settings', 'navSettings', Settings]],
    Supervisor: [['students', 'navStudents', SupStudents], ['attendance', 'navAttendance', SupAttendance], ['reports', 'navReports', SupReports], ['evaluations', 'navEvaluations', SupEvals], ['academic', 'navAcademicReports', AcademicReports], ['chat', 'navChat', ChatPage], ['settings', 'navSettings', Settings]],
    Admin: [['analytics', 'navAnalytics', Analytics], ['approvals', 'navApprovals', Approvals], ['users', 'navUsers', Users], ['assign', 'navAssign', Assign], ['audit', 'navAudit', Audit], ['reports', 'navInstReports', InstitutionReports], ['chat', 'navChat', ChatPage], ['settings', 'navSettings', Settings]]
  };
  var ROLE_KEY = { Student: 'roleStudent', Company: 'roleCompany', Supervisor: 'roleSupervisor', Admin: 'roleAdmin' };

  function Mark(p) {
    return h('span', { className: 'logo-tile', style: { height: p.size + 'px' }, 'aria-hidden': true }, h('img', { src: LOGO, alt: '', className: 'logo-img' }));
  }


  function SignIn() {
    var app = useApp(), s = app.state, t = app.t, err = R.useState(null);
    var accounts = [['u1', 'descFaisal'], ['u6', 'descReem'], ['u9', 'descCompany'], ['u13', 'descSupervisor'], ['u15', 'descAdmin']];
    return h('main', { className: 'signin coop-root' },
      h('div', { className: 'signin-hero' },
        h('button', { className: 'back', onClick: app.home }, (app.lang === 'ar' ? '→ ' : '← ') + LANDING[app.lang].backHome),
        h('div', { className: 'signin-brand' }, h(Mark, { size: 64, onDark: true }), h('p', { className: 'wordmark' }, 'E-COOP'), h('p', { className: 'signin-tag' }, t('appTagline')))),
      h('div', { className: 'signin-body' },
        h('h1', { className: 'signin-title' }, t('signInTitle')),
        h('p', { className: 'lead' }, t('signInLead')),
        err[0] ? h(C.Alert, { tone: 'danger', title: err[0] }) : null,
        h('p', { className: 'coop-label list-label' }, t('demoAccounts')),
        h('ul', { className: 'accounts' }, accounts.map(function (a) {
          var u = by(s.users, a[0]);
          return h('li', { key: a[0] }, h('button', { className: 'account', onClick: function () { if (u.status !== 'Active') { err[1](t('suspendedSignIn')); return; } app.signIn(u.id); } },
            h(Avatar, { name: u.name }),
            h('span', { className: 'account-main' },
              h('span', { className: 'account-top' }, h('span', { className: 'account-name' }, u.name), h('span', { className: 'account-role' }, t(ROLE_KEY[u.role]))),
              h('span', { className: 'account-desc' }, t(a[1])), h('span', { className: 'account-email' }, u.email))));
        })),
        h('div', { className: 'signin-foot' },
          h(C.Button, { variant: 'ghost', size: 'sm', onClick: app.toggleLang }, t('language')),
          h(C.Button, { variant: 'ghost', size: 'sm', onClick: function () { app.dispatch({ type: 'reset' }); app.toast('success', t('resetDone')); } }, t('resetDemo'))),
        h('p', { className: 'caption muted' }, t('college'))));
  }

  function Shell() {
    var app = useApp(), t = app.t, routes = ROUTES[app.me.role];
    var cur = routes.filter(function (r) { return r[0] === app.page; })[0] || routes[0];
    var unread = app.state.notifs.filter(function (n) { return n.user === app.me.id && !n.read; }).length;
    var showN = R.useState(false);
    return h('div', { className: 'shell coop-root' },
      h('header', { className: 'topbar' },
        h('div', { className: 'wrap topbar-in' },
          h('a', { className: 'brand', href: '#', onClick: function (e) { e.preventDefault(); app.go(routes[0][0]); } }, h(Mark, { size: 30, onDark: true }), h('span', null, 'E-COOP')),
          h('nav', { className: 'topnav', 'aria-label': 'Main' }, routes.filter(function (r) { return r[0] !== 'settings'; }).map(function (r) {
            var cu = r[0] === 'chat' ? totalUnread(app.state, app.me.id) : 0;
            return h('a', { key: r[0], href: '#' + r[0], className: 'topnav-link', 'aria-current': r === cur ? 'page' : undefined, onClick: function (e) { e.preventDefault(); app.go(r[0]); } }, t(r[1]), cu ? h('span', { className: 'nav-count', 'aria-label': fill(t('unreadN'), { n: cu }) }, cu) : null);
          })),
          h('div', { className: 'top-actions' },
            h('button', { className: 'icon-btn', onClick: function () { showN[1](true); }, 'aria-label': t('notifications') + (unread ? ' (' + unread + ')' : '') }, h(Icon, { name: 'bell', size: 20 }), unread ? h('span', { className: 'count' }, unread) : null, h('span', { className: 'icon-btn-label' }, t('notifications'))),
            h('button', { className: 'icon-btn', onClick: app.toggleLang, lang: app.lang === 'ar' ? 'en' : 'ar' }, h('span', { className: 'lang-txt' }, t('language'))),
            h('button', { className: 'me-btn', onClick: function () { app.go('settings'); }, 'aria-label': t('navSettings') }, h(Avatar, { name: app.me.name, size: 'sm' }), h('span', { className: 'me-role' }, t(ROLE_KEY[app.me.role])))))),
      h('nav', { className: 'tabs', 'aria-label': 'Main' }, routes.map(function (r) { var cu = r[0] === 'chat' ? totalUnread(app.state, app.me.id) : 0; return h('button', { key: r[0], className: 'tab', 'aria-current': r === cur ? 'page' : undefined, onClick: function () { app.go(r[0]); } }, t(r[1]), cu ? h('span', { className: 'nav-count' }, cu) : null); })),
      h('main', { className: 'main', key: cur[0] }, h(cur[2])),
      showN[0] ? h(Notifications, { onClose: function () { showN[1](false); app.dispatch({ type: 'readNotifs', user: app.me.id }); } }) : null,
      app.tourOpen ? h(Tour, { onClose: app.endTour }) : null);
  }

  function applyTheme(th) { document.documentElement.setAttribute('data-theme', th === 'dark' ? 'dark' : 'light'); }
  function initialTheme() { var v = lsGet('theme', null); if (v === 'light' || v === 'dark') return v; try { return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'; } catch (e) { return 'light'; } }

  function App() {
    var st = R.useReducer(reducer, null, load), state = st[0], dispatch = st[1];
    var lang = R.useState(function () { return lsGet('lang', 'en'); });
    var theme = R.useState(initialTheme);
    var who = R.useState(function () { return lsGet('user', null); });
    var page = R.useState(function () { return (location.hash || '').slice(1) || null; });
    var params = R.useState(null);
    var toast = R.useState(null);
    var view = R.useState(function () { return lsGet('user', null) ? 'app' : 'landing'; });
    var tour = R.useState(false);
    var stateRef = R.useRef(state); stateRef.current = state;
    R.useEffect(function () { save(state); }, [state]);
    R.useEffect(function () { document.documentElement.lang = lang[0]; document.documentElement.dir = lang[0] === 'ar' ? 'rtl' : 'ltr'; lsSet('lang', lang[0]); }, [lang[0]]);
    R.useEffect(function () { applyTheme(theme[0]); lsSet('theme', theme[0]); }, [theme[0]]);
    R.useEffect(function () { if (!toast[0]) return; var tm = setTimeout(function () { toast[1](null); }, toast[0].undo ? 6000 : 3500); return function () { clearTimeout(tm); }; }, [toast[0]]);
    var me = who[0] ? by(state.users, who[0]) : null;
    if (me && me.status !== 'Active') me = null;
    R.useEffect(function () { if (me && lsGet('toured-' + me.role, null) == null) tour[1](true); }, [me && me.id]);
    var dict = I18N[lang[0]];
    var t = function (k) { return dict[k] != null ? dict[k] : (I18N.en[k] != null ? I18N.en[k] : k); };
    var app = {
      state: state, dispatch: dispatch, lang: lang[0], theme: theme[0], me: me, page: page[0], params: params[0], t: t, tourOpen: tour[0],
      go: function (p, prm) { page[1](p); params[1](prm || null); try { history.replaceState(null, '', '#' + p); } catch (e) {} window.scrollTo(0, 0); },
      toggleLang: function () { lang[1](lang[0] === 'ar' ? 'en' : 'ar'); },
      setLang: function (v) { lang[1](v); }, setTheme: function (v) { theme[1](v); },
      openApp: function () { view[1]('signin'); window.scrollTo(0, 0); },
      home: function () { view[1]('landing'); window.scrollTo(0, 0); },
      signIn: function (uid) { who[1](uid); page[1](null); view[1]('app'); lsSet('user', uid); },
      signOut: function () { view[1]('signin'); who[1](null); lsSet('user', null); window.scrollTo(0, 0); },
      startTour: function () { tour[1](true); },
      endTour: function () { tour[1](false); if (me) lsSet('toured-' + me.role, '1'); },
      toast: function (tone, msg, undoState) { toast[1]({ tone: tone, msg: msg, n: Date.now(), undo: undoState || null }); },
      /* dispatch with a snackbar; undoable actions keep the previous state for Undo */
      act: function (action, msg, tone, undoable) { var prev = stateRef.current; dispatch(action); if (msg) app.toast(tone || 'success', msg, undoable ? prev : null); }
    };
    return h(Ctx.Provider, { value: app },
      me ? h(Shell) : view[0] === 'signin' ? h(SignIn) : h(Landing, { lang: lang[0], t: t, Mark: Mark, toggleLang: app.toggleLang, openApp: app.openApp, users: state.users, signIn: app.signIn, sendContact: function (m) { dispatch({ type: 'contact', msg: m }); }, register: function (r) { var k = Date.now().toString(36); dispatch(Object.assign({ type: 'register', uid: 'u' + k, ref: (r.kind === 'Student' ? 's' : r.kind === 'Supervisor' ? 'sup' : r.kind === 'Admin' ? 'a' : 'c') + k }, r)); app.signIn('u' + k); } }),
      toast[0] ? h('div', { className: 'snack-wrap', key: toast[0].n },
        h('div', { className: 'snack snack-' + toast[0].tone, role: 'status' },
          h('span', { className: 'snack-icon' }, h(Icon, { name: toast[0].tone === 'danger' ? 'x' : 'check', size: 18 })),
          h('span', { className: 'snack-msg' }, toast[0].msg),
          toast[0].undo ? h('button', { className: 'snack-btn', onClick: function () { var u = toast[0].undo; dispatch({ type: 'restore', state: u }); toast[1]({ tone: 'info', msg: t('undone'), n: Date.now() }); } }, t('undo')) : null,
          h('button', { className: 'snack-x', 'aria-label': t('close'), onClick: function () { toast[1](null); } }, h(Icon, { name: 'x', size: 16 })))) : null);
  }

  ReactDOM.createRoot(document.getElementById('app')).render(h(App));
})();
