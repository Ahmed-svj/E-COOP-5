<?php $title = __('nav.students'); $q = mb_strtolower(trim((string) ($_GET['q'] ?? '')));
if ($q !== '') { $cohort = array_values(array_filter($cohort, fn ($r) => str_contains(mb_strtolower($r['FullName'] . ' ' . $r['InstitutionalID'] . ' ' . $r['CompanyName']), $q))); } ?>
<?= View::partial('partials/page-head', ['heading' => __('nav.students'), 'sub' => __('sup.students_sub')]) ?>
<?php if (!$cohort): ?><?= View::partial('partials/empty', ['icon' => 'users', 'text' => __('sup.no_students')]) ?>
<?php else: ?>
<div class="co-table-wrap table-responsive"><table class="co-table">
  <thead><tr><th><?= e(__('role.Student')) ?></th><th><?= e(__('role.Company')) ?></th><th><?= e(__('eval.approved_days')) ?></th><th><?= e(__('status.Pending')) ?></th><th><?= e(__('nav.report')) ?></th><th><?= e(__('eval.score')) ?></th><th></th></tr></thead>
  <tbody><?php foreach ($cohort as $r): ?>
    <tr><td><strong><?= e($r['FullName']) ?></strong><br><span class="co-caption" dir="ltr"><?= e($r['InstitutionalID']) ?></span></td><td><?= e($r['CompanyName'] ?? '—') ?><br><?= $r['PlacementStatus'] ? status_badge($r['PlacementStatus']) : '' ?></td>
      <td class="num"><?= (int) $r['Approved'] ?></td><td class="num"><?= (int) $r['Pending'] ?></td><td><?= $r['ReportStatus'] ? status_badge($r['ReportStatus']) : '—' ?></td>
      <td class="num"><?= e($r['CompanyScore'] ?? '—') ?> / <?= e($r['AcademicScore'] ?? '—') ?></td>
      <td class="app-actions"><a class="co-btn co-btn--soft co-btn--sm" href="<?= url('/supervisor/students/' . $r['StudentID']) ?>"><?= e(__('common.view')) ?></a></td></tr>
  <?php endforeach; ?></tbody>
</table></div>
<?php endif; ?>
