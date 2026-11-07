/* Poll the unread count every 30 s and update the bell's dot. */
(function () {
  'use strict';
  const bell = document.getElementById('notif-bell');
  if (!bell) return;
  let last = null;
  function poll() {
    if (document.hidden) return;
    coop.fetch('/api/notifications/unread').then((r) => (r.ok ? r.json() : null)).then((d) => {
      if (!d) return;
      bell.classList.toggle('co-iconbtn--dot', d.count > 0);
      if (last !== null && d.count > last) {
        coop.toast(document.documentElement.lang === 'ar' ? 'لديك إشعار جديد' : 'You have a new notification', 'lavender');
      }
      last = d.count;
    }).catch(() => {});
  }
  poll();
  setInterval(poll, 30000);
})();
