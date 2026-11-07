<?php $title = __('nav.trainees'); ?>
<?= View::partial('partials/page-head', ['heading' => __('nav.trainees'), 'sub' => __('company.trainees_sub')]) ?>
<?php if (!$items): ?>
  <?= View::partial('partials/empty', ['icon' => 'cap', 'text' => __('company.no_trainees')]) ?>
<?php else: ?>
<div class="co-table-wrap table-responsive"><table class="co-table">
  <thead><tr><th><?= e(__('role.Student')) ?></th><th><?= e(__('opp.title')) ?></th><th><?= e(__('app.period')) ?></th><th><?= e(__('eval.approved_days')) ?></th><th><?= e(__('common.status')) ?></th><th><?= e(__('eval.score')) ?></th><th></th></tr></thead>
  <tbody><?php foreach ($items as $t): ?>
    <tr><td><strong><?= e($t['FullName']) ?></strong><br><span class="co-caption"><?= e($t['Major']) ?></span></td><td><?= e($t['Title']) ?></td>
      <td class="num"><?= e(fmt_date($t['StartDate'])) ?> – <?= e(fmt_date($t['EndDate'])) ?></td><td class="num"><?= (int) $t['Approved'] ?> / <?= (int) $t['Total'] ?></td>
      <td><?= status_badge($t['PlacementStatus']) ?></td><td class="num"><?= $t['Score'] !== null ? (int) $t['Score'] : '—' ?></td>
      <td class="app-actions"><?php if ($t['Score'] === null): ?><a class="co-btn co-btn--primary co-btn--sm" href="<?= url('/company/evaluate/' . $t['StudentID']) ?>"><?= e(__('eval.evaluate')) ?></a><?php endif; ?></td></tr>
  <?php endforeach; ?></tbody>
</table></div>
<?php endif; ?>
