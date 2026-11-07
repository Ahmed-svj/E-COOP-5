<?php $title = __('nav.system_report'); ?>
<?= View::partial('partials/page-head', ['heading' => __('sysrep.title'), 'sub' => __('sysrep.sub', ['date' => $r['generated']]),
  'actions' => '<a class="co-btn co-btn--primary" href="' . url('/admin/report/csv') . '">' . icon('download', 'co-ico--sm') . e(__('sup.download_csv')) . '</a><button class="co-btn" type="button" data-print>' . icon('printer', 'co-ico--sm') . e(__('sup.print')) . '</button>']) ?>
<div class="app-kpis">
  <?php foreach ($r['headline'] as $k => $v): $pct = in_array($k, ['report.h_placed_pct', 'report.h_att_rate'], true); ?>
    <section class="co-card"><h2 class="co-card__title"><?= e(__($k)) ?></h2><div class="co-metric"><?= $v ?><?= $pct ? '<small>%</small>' : '' ?></div>
      <?php if ($k === 'report.h_avg'): ?><p class="co-caption">/ 100</p><?php endif; ?><?php if ($pct): ?><div class="co-progress co-progress--mint co-progress--thin" role="progressbar" aria-valuenow="<?= $v ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?= e(__($k)) ?>"><span style="width:<?= $v ?>%"></span></div><?php endif; ?></section>
  <?php endforeach; ?>
</div>
<div class="row g-3 mt-1">
  <div class="col-lg-5"><section class="co-card h-100"><div class="co-card__head"><span class="co-tile co-tile--lavender"><?= icon('check') ?></span><h2 class="co-card__title"><?= e(__('report.todo')) ?></h2></div>
    <?php if (!$r['todo']): ?><?= View::partial('partials/empty', ['icon' => 'ok', 'text' => __('report.todo_none')]) ?>
    <?php else: ?><div class="co-list app-todo"><?php foreach ($r['todo'] as $t): ?>
      <a class="co-row app-row-link" href="<?= url($t['href']) ?>"><span class="co-tile co-tile--lavender"><?= (int) $t['n'] ?></span><span class="co-row__text"><span class="co-row__title app-wrap"><?= e(__($t['label'])) ?></span></span><?= icon('chev', 'app-flip') ?></a>
    <?php endforeach; ?></div><?php endif; ?>
  </section></div>
  <div class="col-lg-7"><section class="co-card h-100"><div class="co-card__head"><span class="co-tile"><?= icon('building') ?></span><h2 class="co-card__title"><?= e(__('report.top_companies')) ?></h2></div>
    <div class="table-responsive"><table class="co-table"><thead><tr><th><?= e(__('role.Company')) ?></th><th><?= e(__('nav.opportunities')) ?></th><th><?= e(__('report.s_apps')) ?></th><th><?= e(__('nav.trainees')) ?></th></tr></thead>
      <tbody><?php foreach ($r['top_companies'] as $c): ?><tr><td><strong><?= e($c['CompanyName']) ?></strong></td><td class="num"><?= (int) $c['Opps'] ?></td><td class="num"><?= (int) $c['Apps'] ?></td><td class="num"><?= (int) $c['Trainees'] ?></td></tr><?php endforeach; ?></tbody></table></div>
    <h3 class="co-card__title mt-2"><?= e(__('admin.sup_load')) ?></h3>
    <?php foreach ($r['loads'] as $l): $p = (int) round($l['CurrentLoad'] * 100 / max(1, $l['MaxStudentLoad'])); ?>
      <div class="app-oppmini"><div class="co-progress-meta"><span class="co-row__title"><?= e($l['FullName']) ?></span><span><?= (int) $l['CurrentLoad'] ?> / <?= (int) $l['MaxStudentLoad'] ?></span></div>
      <div class="co-progress co-progress--thin<?= $p < 90 ? ' co-progress--mint' : '' ?>" role="progressbar" aria-valuenow="<?= $p ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?= e($l['FullName']) ?>"><span style="width:<?= min(100, $p) ?>%"></span></div></div>
    <?php endforeach; ?>
  </section></div>
</div>
<div class="app-report-grid mt-3">
  <?php $icons = ['report.s_users' => 'users', 'report.s_companies' => 'building', 'report.s_opps' => 'brief', 'report.s_apps' => 'list', 'report.s_placements' => 'cap', 'report.s_attendance' => 'check', 'report.s_reports' => 'doc', 'report.s_security' => 'shield', 'report.s_system' => 'settings'];
  foreach ($r['sections'] as $section => $pairs): ?>
    <section class="co-card"><div class="co-card__head"><span class="co-tile<?= in_array($section, ['report.s_security', 'report.s_system'], true) ? ' co-tile--lavender' : '' ?>"><?= icon($icons[$section] ?? 'info') ?></span><h2 class="co-card__title"><?= e(__($section)) ?></h2></div>
      <dl class="app-kv"><?php foreach ($pairs as $label => $value): ?><div><dt><?= e(__($label)) ?></dt><dd dir="ltr"><?= e($value) ?></dd></div><?php endforeach; ?></dl></section>
  <?php endforeach; ?>
</div>
<p class="co-caption mt-3"><?= e(__('report.footnote')) ?></p>
