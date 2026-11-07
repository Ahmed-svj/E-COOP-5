<?php $title = __('nav.attendance'); ?>
<?= View::partial('partials/page-head', ['heading' => __('sup.att_title'), 'sub' => __('sup.att_sub'),
  'actions' => $status === 'Pending' && $items ? '<form method="post" action="' . url('/supervisor/attendance/approve-all') . '" data-confirm="' . e(__('att.approve_all_confirm')) . '">' . csrf_field() . '<button class="co-btn co-btn--primary">' . icon('ok', 'co-ico--sm') . e(__('att.approve_all')) . '</button></form>' : '']) ?>
<div class="co-chips mb-3"><?php foreach (['Pending', 'Approved', 'Rejected'] as $st): ?><a class="co-chip" aria-pressed="<?= $status === $st ? 'true' : 'false' ?>" href="<?= url('/supervisor/attendance', ['status' => $st]) ?>"><?= e(__('status.' . $st)) ?></a><?php endforeach; ?></div>
<?php if (!$items): ?><?= View::partial('partials/empty', ['icon' => 'ok', 'text' => __('sup.all_reviewed')]) ?>
<?php else: ?>
<div class="co-table-wrap table-responsive"><table class="co-table">
  <thead><tr><th><?= e(__('role.Student')) ?></th><th><?= e(__('role.Company')) ?></th><th><?= e(__('common.date')) ?></th><th><?= e(__('att.time')) ?></th><th><?= e(__('common.status')) ?></th><th></th></tr></thead>
  <tbody><?php foreach ($items as $r): ?>
    <tr><td><a href="<?= url('/supervisor/students/' . $r['StudentID']) ?>"><strong><?= e($r['FullName']) ?></strong></a></td><td><?= e($r['CompanyName']) ?></td><td class="num"><?= e(fmt_date($r['CodeDate'])) ?></td><td class="num"><?= e(date('H:i', strtotime($r['CheckInTime']))) ?></td>
      <td><?= status_badge($r['ApprovalStatus']) ?><?= $r['ReviewComment'] ? '<br><span class="co-caption">' . e($r['ReviewComment']) . '</span>' : '' ?></td>
      <td class="app-actions"><?php if ($r['ApprovalStatus'] === 'Pending'): ?>
        <button class="co-btn co-btn--danger-ghost co-btn--sm" type="button" data-bs-toggle="modal" data-bs-target="#rej-<?= (int) $r['AttendanceID'] ?>"><?= e(__('common.reject')) ?></button>
        <form method="post" action="<?= url('/supervisor/attendance/' . $r['AttendanceID'] . '/approve') ?>"><?= csrf_field() ?><button class="co-btn co-btn--primary co-btn--sm"><?= e(__('common.approve')) ?></button></form>
      <?php else: ?><?= View::partial('supervisor/_att-change', ['r' => $r, 'name' => $r['FullName']]) ?><?php endif; ?></td></tr>
  <?php endforeach; ?></tbody>
</table></div>
<?php foreach ($items as $r): if ($r['ApprovalStatus'] === 'Pending'): ?>
  <?= View::partial('partials/reject-modal', ['id' => 'rej-' . $r['AttendanceID'], 'action' => '/supervisor/attendance/' . $r['AttendanceID'] . '/reject', 'title' => __('att.reject_title', ['name' => $r['FullName'], 'date' => fmt_date($r['CodeDate'])]), 'field' => 'comment', 'label' => __('att.comment')]) ?>
<?php endif; endforeach; ?>
<?php endif; ?>
