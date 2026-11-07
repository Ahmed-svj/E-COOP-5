<?php $title = __('nav.assign'); ?>
<?= View::partial('partials/page-head', ['heading' => __('admin.assign_title'), 'sub' => __('admin.assign_sub')]) ?>
<div class="row g-3">
  <div class="col-xl-8">
    <?php if (!$students): ?><?= View::partial('partials/empty', ['icon' => 'assign', 'text' => __('admin.no_placed')]) ?>
    <?php else: ?><div class="co-card"><div class="co-list"><?php foreach ($students as $s): ?>
      <div class="co-row app-row-wrap"><span class="co-row__media<?= $s['SupervisorID'] ? ' co-row__media--mint' : '' ?>"><?= e(initials($s['FullName'])) ?></span>
        <div class="co-row__text"><span class="co-row__title"><?= e($s['FullName']) ?></span><span class="co-row__sub"><?= e($s['Major']) ?> · <?= e($s['CompanyName']) ?> · <?= e($s['Title']) ?></span>
          <span class="co-row__sub"><?= $s['SupervisorName'] ? e(__('admin.current_sup', ['name' => $s['SupervisorName']])) : '<span class="co-badge co-badge--amber">' . e(__('admin.unassigned')) . '</span>' ?></span></div>
        <form class="co-row__actions" method="post" action="<?= url('/admin/assign') ?>"><?= csrf_field() ?><input type="hidden" name="student_id" value="<?= (int) $s['StudentID'] ?>">
          <label class="visually-hidden" for="sup-<?= (int) $s['StudentID'] ?>"><?= e(__('role.Supervisor')) ?></label>
          <select class="co-input app-select-sm" id="sup-<?= (int) $s['StudentID'] ?>" name="supervisor_id" required><option value=""><?= e(__('admin.choose_sup')) ?></option>
            <?php foreach ($supervisors as $sp): $full = $sp['CurrentLoad'] >= $sp['MaxStudentLoad']; ?><option value="<?= (int) $sp['SupervisorID'] ?>"<?= (int) $s['SupervisorID'] === (int) $sp['SupervisorID'] ? ' selected' : '' ?>><?= e($sp['FullName']) ?> (<?= (int) $sp['CurrentLoad'] ?>/<?= (int) $sp['MaxStudentLoad'] ?><?= $full ? ' · ' . e(__('admin.full')) : '' ?>)</option><?php endforeach; ?></select>
          <button class="co-btn co-btn--primary co-btn--sm" type="submit"><?= e(__('admin.assign')) ?></button></form>
        <?php if ($s['PlacementStatus'] === 'Active'): ?><form method="post" action="<?= url('/admin/placements/' . $s['PlacementID'] . '/complete') ?>" data-confirm="<?= e(__('admin.complete_confirm')) ?>"><?= csrf_field() ?><button class="co-btn co-btn--soft co-btn--sm" title="<?= e(__('admin.complete_hint', ['date' => fmt_date($s['EndDate'])])) ?>"><?= e(__('admin.complete')) ?></button></form><?php else: ?><?= status_badge($s['PlacementStatus']) ?><?php endif; ?>
      </div>
    <?php endforeach; ?></div></div><?php endif; ?>
  </div>
  <div class="col-xl-4"><section class="co-card">
    <div class="co-card__head"><span class="co-tile co-tile--lavender"><?= icon('users') ?></span><h2 class="co-card__title"><?= e(__('admin.sup_load')) ?></h2></div>
    <?php foreach ($supervisors as $sp): $p = (int) round($sp['CurrentLoad'] * 100 / max(1, $sp['MaxStudentLoad'])); ?>
      <div class="app-oppmini"><div class="co-progress-meta"><span class="co-row__title"><?= e($sp['FullName']) ?></span><span><?= (int) $sp['CurrentLoad'] ?> / <?= (int) $sp['MaxStudentLoad'] ?></span></div>
        <div class="co-progress co-progress--thin<?= $p < 90 ? ' co-progress--mint' : '' ?>" role="progressbar" aria-valuenow="<?= $p ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?= e($sp['FullName']) ?>"><span style="width:<?= min(100, $p) ?>%"></span></div>
        <span class="co-caption"><?= e($sp['Department']) ?><?= $p >= 90 ? ' · ' . e(__('admin.near_full')) : '' ?></span></div>
    <?php endforeach; ?>
  </section></div>
</div>
