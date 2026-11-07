/* Admin analytics (UC-22) with Chart.js, coloured from the design tokens. */
(function () {
  'use strict';
  if (!window.Chart) return;
  const css = getComputedStyle(document.documentElement);
  const v = (n) => css.getPropertyValue('--' + n).trim();
  const ink = v('ink-muted'), line = v('line');
  Chart.defaults.font.family = css.getPropertyValue('--font-sans');
  Chart.defaults.color = ink;
  Chart.defaults.plugins.legend.labels.usePointStyle = true;
  const palette = [v('mint'), v('lavender'), v('amber-ink'), v('red-ink'), v('ink-subtle'), v('mint-ink'), v('lavender-ink')];
  let rtl = document.documentElement.dir === 'rtl';
  const axes = (horizontal) => ({
    x: { grid: { display: horizontal, color: line }, border: { display: false }, reverse: rtl && !horizontal, ticks: { precision: 0 } },
    y: { grid: { display: !horizontal, color: line }, border: { display: false }, beginAtZero: true, position: rtl ? 'right' : 'left', ticks: { precision: 0 } },
  });

  document.querySelectorAll('canvas[data-chart]').forEach((c) => {
    rtl = (c.closest('[dir]')?.getAttribute('dir') || document.documentElement.dir) === 'rtl';
    const d = JSON.parse(c.dataset.series || '{}');
    const type = c.dataset.chart;
    let cfg;
    if (type === 'attendance') {
      cfg = { type: 'bar', data: { labels: d.labels, datasets: [
        { label: d.names[0], data: d.approved, backgroundColor: v('mint'), borderRadius: 8, stack: 's' },
        { label: d.names[1], data: d.pending, backgroundColor: v('lavender'), borderRadius: 8, stack: 's' },
        { label: d.names[2], data: d.rejected, backgroundColor: v('red-soft'), borderRadius: 8, stack: 's' },
      ] }, options: { maintainAspectRatio: false, scales: { ...axes(false), x: { ...axes(false).x, stacked: true }, y: { ...axes(false).y, stacked: true } } } };
    } else if (type === 'doughnut') {
      cfg = { type: 'doughnut', data: { labels: d.labels, datasets: [{ data: d.values, backgroundColor: (d.tones && d.tones.length) ? d.tones.map((t) => ({ mint: v('mint'), lavender: v('lavender'), amber: v('amber-ink'), red: v('red-ink'), neutral: v('ink-subtle') }[t])) : palette, borderColor: v('surface'), borderWidth: 3 }] },
        options: { maintainAspectRatio: false, cutout: '68%', plugins: { legend: { position: 'bottom', rtl } } } };
    } else {
      const horizontal = type === 'hbar';
      cfg = { type: 'bar', data: { labels: d.labels, datasets: [{ data: d.values, backgroundColor: horizontal ? v('lavender') : v('mint'), borderRadius: 10, maxBarThickness: 40 }] },
        options: { maintainAspectRatio: false, indexAxis: horizontal ? 'y' : 'x', plugins: { legend: { display: false } }, scales: axes(horizontal) } };
    }
    new Chart(c, cfg);
  });
})();
