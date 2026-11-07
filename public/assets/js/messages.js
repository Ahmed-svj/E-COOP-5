/* Conversations: send without reload, poll for new messages every 5 s. */
(function () {
  'use strict';
  const box = document.getElementById('chat-msgs');
  const form = document.getElementById('chat-form');
  if (!box || !form) return;
  const id = box.dataset.conversation;
  const textarea = form.querySelector('textarea');
  box.scrollTop = box.scrollHeight;

  function add(m) {
    const div = document.createElement('div');
    div.className = 'app-msg' + (m.mine ? ' app-msg--mine' : '');
    const p = document.createElement('p');
    p.textContent = m.body;
    const s = document.createElement('span');
    s.textContent = m.time;
    div.append(p, s);
    box.appendChild(div);
    box.dataset.last = m.id;
  }

  function poll() {
    coop.fetch('/api/messages/' + id + '?after=' + (box.dataset.last || 0))
      .then((r) => (r.ok ? r.json() : []))
      .then((list) => {
        if (!list.length) return;
        const atBottom = box.scrollHeight - box.scrollTop - box.clientHeight < 60;
        list.forEach(add);
        if (atBottom) box.scrollTop = box.scrollHeight;
      }).catch(() => {});
  }

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const body = textarea.value.trim();
    if (!body) return;
    const data = new FormData(form);
    textarea.value = '';
    coop.fetch(form.action, { method: 'POST', body: data }).then(poll).finally(() => {
      form.querySelectorAll('button').forEach((b) => (b.disabled = false));
      textarea.focus();
    });
  });
  textarea.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form.requestSubmit(); }
  });
  setInterval(() => { if (!document.hidden) poll(); }, 5000);
})();
