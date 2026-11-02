/* @ds-bundle: {"format":4,"namespace":"COOP","components":[{"name":"AppBar"},{"name":"Button"},{"name":"StatusBadge"},{"name":"Alert"},{"name":"TextField"},{"name":"FileUpload"},{"name":"DailyCodeInput"},{"name":"DailyCodeCard"},{"name":"OpportunityCard"},{"name":"StatCard"},{"name":"DataTable"}]} */
(function () {
  var R = window.React, h = R.createElement;
  function cx() { return Array.prototype.filter.call(arguments, Boolean).join(' '); }
  function omit(o, keys) { var r = {}; for (var k in o) if (keys.indexOf(k) < 0) r[k] = o[k]; return r; }
  var uid = 0; function useId(p) { var ref = R.useRef(null); if (ref.current == null) ref.current = (p || 'coop') + '-' + (++uid); return ref.current; }

  /* Status vocabulary from the COOP business rules -> tone */
  var TONES = {
    success: ['approved', 'accepted', 'active', 'completed', 'recorded', 'present'],
    warning: ['pending', 'pending approval', 'pending evaluation', 'request revision', 'revision requested', 'expiring'],
    danger: ['rejected', 'suspended', 'expired', 'locked', 'absent', 'invalid'],
    info: ['submitted', 'open', 'new', 'under review']
  };
  function toneOf(status) {
    var s = String(status || '').toLowerCase().trim();
    for (var t in TONES) if (TONES[t].indexOf(s) >= 0) return t;
    return 'neutral';
  }

  function Mark(props) {
    var s = props.size || 28;
    return h('svg', { width: s, height: s, viewBox: '0 0 48 48', 'aria-hidden': true, focusable: 'false' },
      h('rect', { width: 48, height: 48, rx: 11, fill: props.tile || '#FFFFFF' }),
      h('path', { d: 'M24 15.65A10 10 0 0 1 24 32.35A10 10 0 0 1 24 15.65Z', fill: '#4F81BD' }),
      h('circle', { cx: 18.5, cy: 24, r: 10, fill: 'none', stroke: props.ring || '#1B365D', strokeWidth: 3.5 }),
      h('circle', { cx: 29.5, cy: 24, r: 10, fill: 'none', stroke: props.ring || '#1B365D', strokeWidth: 3.5 }));
  }

  function Button(p) {
    var variant = p.variant || 'primary', size = p.size || 'md';
    return h('button', Object.assign({ type: 'button' }, omit(p, ['variant', 'size', 'block', 'className', 'children']), {
      className: cx('coop-btn', 'coop-btn-' + variant, size !== 'md' && 'coop-btn-' + size, p.block && 'coop-btn-block', p.className)
    }), p.children);
  }

  function StatusBadge(p) {
    var tone = p.tone || toneOf(p.status);
    return h('span', { className: cx('coop-badge', 'coop-badge-' + tone, p.className) }, p.children || p.status);
  }

  var ICONS = {
    success: 'M5 10.5l3.2 3.2L15 7',
    warning: 'M10 6v5M10 14h.01',
    danger: 'M7 7l6 6M13 7l-6 6',
    info: 'M10 9v5M10 6h.01'
  };
  function Alert(p) {
    var tone = p.tone || 'info';
    return h('div', { className: cx('coop-alert', 'coop-alert-' + tone, p.className), role: tone === 'danger' ? 'alert' : 'status' },
      h('svg', { className: 'coop-alert-icon', viewBox: '0 0 20 20', 'aria-hidden': true },
        h('circle', { cx: 10, cy: 10, r: 8.5, fill: 'none', stroke: 'currentColor', strokeWidth: 1.5 }),
        h('path', { d: ICONS[tone], fill: 'none', stroke: 'currentColor', strokeWidth: 1.8, strokeLinecap: 'round', strokeLinejoin: 'round' })),
      h('div', { className: 'coop-alert-body' },
        p.title ? h('div', { className: 'coop-alert-title' }, p.title) : null,
        p.children ? h('div', null, p.children) : null));
  }

  function TextField(p) {
    var gid = useId('field'), id = p.id || gid;
    var tag = p.multiline ? 'textarea' : (p.options ? 'select' : 'input');
    var desc = p.error ? id + '-err' : (p.help ? id + '-help' : undefined);
    var rest = omit(p, ['label', 'help', 'error', 'multiline', 'options', 'className', 'required']);
    var kids = p.options ? p.options.map(function (o) { var v = typeof o === 'string' ? { value: o, label: o } : o; return h('option', { key: v.value, value: v.value }, v.label); }) : undefined;
    return h('div', { className: cx('coop-field', p.className) },
      h('label', { className: 'coop-label', htmlFor: id }, p.label, p.required ? h('span', { className: 'coop-req', 'aria-hidden': true }, '*') : null),
      h(tag, Object.assign({}, rest, { id: id, className: 'coop-input', required: p.required, 'aria-invalid': p.error ? 'true' : undefined, 'aria-describedby': desc }), kids),
      p.error ? h('div', { className: 'coop-error', id: id + '-err' }, p.error) : p.help ? h('div', { className: 'coop-help', id: id + '-help' }, p.help) : null);
  }

  function FileUpload(p) {
    var id = useId('upload');
    var accept = p.accept || '.pdf,.docx';
    var maxMB = p.maxMB || 10;
    var st = R.useState(false), drag = st[0], setDrag = st[1];
    function pick(files) { if (files && files[0] && p.onFile) p.onFile(files[0]); }
    return h('div', { className: cx('coop-field', p.className) },
      p.label ? h('span', { className: 'coop-label' }, p.label) : null,
      p.file
        ? h('div', { className: 'coop-file' }, h('span', null, p.file.name), p.onRemove ? h(Button, { variant: 'ghost', size: 'sm', onClick: p.onRemove }, p.removeLabel || 'Remove') : null)
        : h('label', {
            className: 'coop-drop', htmlFor: id, 'data-drag': drag ? 'true' : undefined,
            onDragOver: function (e) { e.preventDefault(); setDrag(true); },
            onDragLeave: function () { setDrag(false); },
            onDrop: function (e) { e.preventDefault(); setDrag(false); pick(e.dataTransfer.files); }
          },
          h('input', { id: id, type: 'file', accept: accept, onChange: function (e) { pick(e.target.files); } }),
          h('span', { className: 'coop-drop-title' }, p.prompt || 'Choose a file or drag it here'),
          h('span', { className: 'coop-help' }, p.hint || ('PDF or DOCX, max ' + maxMB + ' MB'))),
      p.error ? h('div', { className: 'coop-error' }, p.error) : null);
  }

  function DailyCodeInput(p) {
    var len = p.length || 6;
    var st = R.useState(function () { return (p.defaultValue || '').split('').slice(0, len); }), vals = st[0], setVals = st[1];
    var refs = R.useRef([]);
    var id = useId('code');
    function emit(next) { setVals(next); var code = next.join(''); if (p.onChange) p.onChange(code); if (code.length === len && next.every(Boolean) && p.onComplete) p.onComplete(code); }
    function onInput(i, e) {
      var d = e.target.value.replace(/\D/g, '');
      if (!d) { var n0 = vals.slice(); n0[i] = ''; emit(n0); return; }
      var n = vals.slice(); for (var k = 0; k < d.length && i + k < len; k++) n[i + k] = d[k];
      emit(n); var nx = Math.min(i + d.length, len - 1); if (refs.current[nx]) refs.current[nx].focus();
    }
    function onKey(i, e) { if (e.key === 'Backspace' && !vals[i] && i > 0) refs.current[i - 1].focus(); }
    var boxes = [];
    for (var i = 0; i < len; i++) {
      if (i === len / 2 && len % 2 === 0) boxes.push(h('span', { key: 'g', className: 'coop-code-gap', 'aria-hidden': true }));
      boxes.push(h('input', {
        key: i, ref: (function (j) { return function (el) { refs.current[j] = el; }; })(i),
        className: 'coop-code-box', inputMode: 'numeric', autoComplete: i === 0 ? 'one-time-code' : 'off', maxLength: len,
        value: vals[i] || '', disabled: p.disabled, 'aria-label': (p.digitLabel || 'Digit') + ' ' + (i + 1) + '/' + len,
        onChange: (function (j) { return function (e) { onInput(j, e); }; })(i),
        onKeyDown: (function (j) { return function (e) { onKey(j, e); }; })(i)
      }));
    }
    var state = p.error ? 'error' : p.success ? 'success' : undefined;
    return h('div', { className: cx('coop-code', p.className), 'data-state': state, role: 'group', 'aria-labelledby': id + '-l' },
      h('span', { className: 'coop-label', id: id + '-l' }, p.label || "Today's attendance code"),
      h('div', { className: 'coop-code-boxes' }, boxes),
      p.error ? h('div', { className: 'coop-error' }, p.error)
        : p.success ? h('div', { className: 'coop-help', style: { color: 'var(--success)' } }, p.success)
        : p.help ? h('div', { className: 'coop-help' }, p.help) : null);
  }

  function DailyCodeCard(p) {
    var code = String(p.code || '');
    var shown = code.length === 6 ? code.slice(0, 3) + ' ' + code.slice(3) : code;
    return h('div', { className: cx('coop-dcard', p.className) },
      h('div', { className: 'coop-dcard-head' },
        h('span', { className: 'coop-dcard-title' }, p.title || "Today's attendance code"),
        h(StatusBadge, { status: p.status || 'Active' })),
      h('div', { className: 'coop-dcard-code', 'aria-label': code.split('').join(' ') }, shown),
      h('div', { className: 'coop-dcard-meta' }, p.meta),
      p.actions || null);
  }

  function OpportunityCard(p) {
    return h('article', { className: cx('coop-card coop-opp', p.className) },
      h('div', { className: 'coop-opp-head' },
        h('div', null, h('h3', { className: 'coop-opp-title' }, p.title), h('p', { className: 'coop-opp-company' }, p.company)),
        p.status ? h(StatusBadge, { status: p.status }) : null),
      p.description ? h('p', { className: 'coop-opp-desc' }, p.description) : null,
      h('ul', { className: 'coop-meta' },
        p.city ? h('li', null, (p.labels && p.labels.city) || 'City', ': ', h('b', null, p.city)) : null,
        p.majors ? h('li', null, (p.labels && p.labels.majors) || 'Majors', ': ', h('b', null, p.majors)) : null,
        p.capacity != null ? h('li', null, (p.labels && p.labels.capacity) || 'Seats', ': ', h('b', null, p.capacity)) : null),
      (p.footnote || p.actions) ? h('div', { className: 'coop-opp-foot' }, h('span', { className: 'coop-help' }, p.footnote), p.actions) : null);
  }

  function StatCard(p) {
    var pct = p.progress != null ? Math.max(0, Math.min(100, p.progress)) : null;
    return h('div', { className: cx('coop-card coop-stat', p.className) },
      h('span', { className: 'coop-stat-label' }, p.label),
      h('span', { className: 'coop-stat-value' }, p.value),
      pct != null ? h('div', { className: 'coop-stat-bar', role: 'progressbar', 'aria-valuenow': pct, 'aria-valuemin': 0, 'aria-valuemax': 100, 'aria-label': p.label }, h('span', { style: { width: pct + '%' } })) : null,
      p.note ? h('span', { className: 'coop-stat-note' }, p.note) : null);
  }

  function DataTable(p) {
    var cols = p.columns || [], rows = p.rows || [];
    return h('div', { className: cx('coop-table-wrap', p.className) },
      h('table', { className: 'coop-table' },
        p.caption ? h('caption', { style: { position: 'absolute', width: 1, height: 1, overflow: 'hidden', clip: 'rect(0 0 0 0)' } }, p.caption) : null,
        h('thead', null, h('tr', null, cols.map(function (c) { return h('th', { key: c.key, scope: 'col', style: c.align ? { textAlign: c.align } : undefined }, c.header); }))),
        h('tbody', null, rows.length ? rows.map(function (r, i) {
          return h('tr', { key: r.id != null ? r.id : i, 'aria-selected': p.selectedId != null && r.id === p.selectedId ? 'true' : undefined },
            cols.map(function (c) {
              var v = c.render ? c.render(r) : (c.status ? h(StatusBadge, { status: r[c.key] }) : r[c.key]);
              return h('td', { key: c.key, 'data-label': c.header, style: c.align ? { textAlign: c.align } : undefined }, v);
            }));
        }) : h('tr', null, h('td', { className: 'coop-table-empty', colSpan: cols.length }, p.empty || 'No records yet.')))));
  }

  function AppBar(p) {
    var links = p.links || [];
    return h('header', { className: cx('coop-bar', p.className) },
      h('a', { className: 'coop-bar-brand', href: p.homeHref || '#' }, h(Mark, { size: 28 }), 'COOP'),
      h('nav', { className: 'coop-bar-nav', 'aria-label': p.navLabel || 'Main' },
        links.map(function (l) { return h('a', { key: l.href + l.label, href: l.href, className: 'coop-bar-link', 'aria-current': l.current ? 'page' : undefined }, l.label); })),
      h('div', { className: 'coop-bar-user' },
        p.role ? h('span', { className: 'coop-bar-role' }, p.role) : null,
        p.user ? h('span', { className: 'coop-bar-name' }, p.user) : null,
        p.actions || null));
  }

  var api = { AppBar: AppBar, Button: Button, StatusBadge: StatusBadge, Alert: Alert, TextField: TextField, FileUpload: FileUpload, DailyCodeInput: DailyCodeInput, DailyCodeCard: DailyCodeCard, OpportunityCard: OpportunityCard, StatCard: StatCard, DataTable: DataTable, statusTone: toneOf };
  window.COOP = Object.assign(window.COOP || {}, api);
})();
