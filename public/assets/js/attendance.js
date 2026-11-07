/* 6-digit daily code: auto-advance, backspace, paste (UC-04). Always LTR. */
(function () {
  'use strict';
  document.querySelectorAll('form[data-code-form]').forEach((form) => {
    const boxes = Array.from(form.querySelectorAll('.co-code input'));
    boxes.forEach((el, i) => {
      el.addEventListener('input', () => {
        el.value = el.value.replace(/\D/g, '').slice(-1);
        if (el.value && boxes[i + 1]) boxes[i + 1].focus();
        if (boxes.every((b) => b.value)) form.querySelector('button[type=submit]')?.focus();
      });
      el.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !el.value && boxes[i - 1]) { boxes[i - 1].focus(); boxes[i - 1].value = ''; }
        if (e.key === 'ArrowLeft' && boxes[i - 1]) boxes[i - 1].focus();
        if (e.key === 'ArrowRight' && boxes[i + 1]) boxes[i + 1].focus();
      });
      el.addEventListener('paste', (e) => {
        const digits = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6);
        if (!digits) return;
        e.preventDefault();
        digits.split('').forEach((c, j) => { if (boxes[j]) boxes[j].value = c; });
        (boxes[digits.length] || boxes[5]).focus();
      });
      el.addEventListener('focus', () => el.select());
    });
  });
})();
