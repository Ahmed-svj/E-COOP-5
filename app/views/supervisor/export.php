<?php $title = __('nav.export'); ?>
<?= View::partial('partials/page-head', ['heading' => __('sup.export_title'), 'sub' => __('sup.export_sub'),
  'actions' => '<a class="co-btn co-btn--primary" href="' . url('/supervisor/export/csv') . '">' . icon('download', 'co-ico--sm') . e(__('sup.download_csv')) . '</a><button class="co-btn" type="button" data-print>' . icon('printer', 'co-ico--sm') . e(__('sup.print')) . '</button>']) ?>
<div class="co-table-wrap table-responsive app-print"><table class="co-table">
  <caption class="app-print-only"><?= e(__('sup.export_title')) ?> · <?= e($sp['FullName']) ?> · <?= e(fmt_date(date('Y-m-d'))) ?></caption>
  <thead><tr><th><?= e(__('auth.inst_id')) ?></th><th><?= e(__('role.Student')) ?></th><th><?= e(__('role.Company')) ?></th><th><?= e(__('status.Approved')) ?></th><th><?= e(__('status.Pending')) ?></th><th><?= e(__('status.Rejected')) ?></th><th><?= e(__('nav.report')) ?></th><th><?= e(__('eval.Company')) ?></th><th><?= e(__('eval.Academic')) ?></th></tr></thead>
  <tbody><?php foreach ($cohort as $r): ?><tr><td class="num" dir="ltr"><?= e($r['InstitutionalID']) ?></td><td><?= e($r['FullName']) ?></td><td><?= e($r['CompanyName'] ?? '—') ?></td><td class="num"><?= (int) $r['Approved'] ?></td><td class="num"><?= (int) $r['Pending'] ?></td><td class="num"><?= (int) $r['Rejected'] ?></td><td><?= e($r['ReportStatus'] ? __('status.' . $r['ReportStatus']) : '—') ?></td><td class="num"><?= e($r['CompanyScore'] ?? '—') ?></td><td class="num"><?= e($r['AcademicScore'] ?? '—') ?></td></tr><?php endforeach; ?></tbody>
</table></div>
