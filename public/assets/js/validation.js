/* Client-side checks that mirror app/core/Validator.php. The server always re-validates. */
(function () {
  'use strict';
  const isAr = document.documentElement.lang === 'ar';
  const t = isAr ? {
    required: 'هذا الحقل مطلوب.', email: 'أدخل بريدًا إلكترونيًا صحيحًا.', phone: 'استخدم 10 أرقام تبدأ بـ 05.',
    password: '8 أحرف على الأقل، وتحتوي على حروف وأرقام.', match: 'كلمتا المرور غير متطابقتين.', domain: 'استخدم بريد الكلية @',
    min: 'قصير جدًا.', range: 'القيمة خارج النطاق المسموح.', file: 'الملف أكبر من الحد المسموح.',
  } : {
    required: 'This field is required.', email: 'Enter a valid email address.', phone: 'Use 10 digits starting with 05.',
    password: 'At least 8 characters with letters and numbers.', match: 'Passwords do not match.', domain: 'Use your college email @',
    min: 'Too short.', range: 'Value is out of range.', file: 'File is larger than allowed.',
  };

  function check(input) {
    const v = (input.value || '').trim();
    if (input.type === 'checkbox') return input.required && !input.checked ? t.required : '';
    if (input.required && !v) return t.required;
    if (!v) return '';
    if (input.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) return t.email;
    if (input.dataset.domain) {
      const doms = input.dataset.domain.split(',');
      if (!doms.some((d) => v.toLowerCase().endsWith('@' + d.trim()))) return t.domain + doms.join(', @');
    }
    if (input.dataset.rule === 'phone' && !/^05[0-9]{8}$/.test(v)) return t.phone;
    if (input.dataset.rule === 'password' && !(v.length >= 8 && /[A-Za-z]/.test(v) && /[0-9]/.test(v))) return t.password;
    if (input.dataset.match) {
      const other = input.form.querySelector('[name="' + input.dataset.match + '"]');
      if (other && other.value !== input.value) return t.match;
    }
    if (input.minLength > 0 && v.length < input.minLength) return t.min;
    if (input.type === 'number') {
      const n = Number(v);
      if (Number.isNaN(n) || (input.min !== '' && n < Number(input.min)) || (input.max !== '' && n > Number(input.max))) return t.range;
    }
    if (input.type === 'file' && input.files[0] && input.dataset.max && input.files[0].size > Number(input.dataset.max)) return t.file;
    return '';
  }

  function show(input, msg) {
    const field = input.closest('.co-field');
    if (!field) return;
    let help = field.querySelector('.co-field__help[data-js]');
    field.classList.toggle('co-field--invalid', !!msg);
    input.setAttribute('aria-invalid', msg ? 'true' : 'false');
    if (msg) {
      if (!help) {
        help = document.createElement('span');
        help.className = 'co-field__help';
        help.dataset.js = '1';
        help.id = (input.id || input.name) + '-err';
        field.appendChild(help);
        input.setAttribute('aria-describedby', help.id);
      }
      help.textContent = msg;
      field.querySelectorAll('.co-field__help:not([data-js])').forEach((h) => (h.hidden = true));
    } else if (help) {
      help.remove();
      field.querySelectorAll('.co-field__help:not([data-js])').forEach((h) => (h.hidden = false));
    }
  }

  document.querySelectorAll('form[data-validate]').forEach((form) => {
    const inputs = () => form.querySelectorAll('input:not([type=hidden]):not([disabled]), select, textarea');
    inputs().forEach((i) => i.addEventListener('blur', () => show(i, check(i))));
    form.addEventListener('submit', (e) => {
      let first = null;
      inputs().forEach((i) => {
        const msg = check(i);
        show(i, msg);
        if (msg && !first) first = i;
      });
      if (first) {
        e.preventDefault();
        e.stopImmediatePropagation();
        first.focus();
      }
    }, true);
  });

  // File size check on any upload field
  document.querySelectorAll('input[type=file][data-max]').forEach((i) => i.addEventListener('change', () => show(i, check(i))));
})();
