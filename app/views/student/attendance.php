<?php $title = __('nav.attendance'); $scripts = ['attendance.js'];
$counts = ['Approved' => 0, 'Pending' => 0, 'Rejected' => 0];
foreach ($records as $r) { $counts[$r['ApprovalStatus']]++; } ?>
<?= View::partial('partials/page-head', ['heading' => __('nav.attendance'), 'sub' => __('att.sub')]) ?>
<div class="row g-3">
  <div class="col-lg-5"><section class="co-card">
    <div class="co-card__head"><span class="co-tile co-tile--mint"><?= icon('key') ?></span><h2 class="co-card__title"><?= e(__('dash.checkin')) ?> · <?= e(fmt_date(date('Y-m-d'))) ?></h2></div>
    <?php if (!$s['PlacementID']): ?><p class="co-caption app-body-text"><?= e(__('att.no_placement')) ?></p>
    <?php elseif ($checkedToday): ?><div class="co-alert co-alert--mint" role="status"><?= icon('ok') ?><div><strong><?= e(__('dash.checked_today')) ?></strong><?= e(__('dash.checked_today_d')) ?></div></div>
    <?php else: ?><?= View::partial('student/_code-form', ['locked' => $lockedUntil]) ?><?php endif; ?>
  </section>
  <section class="co-card mt-3"><div class="app-statrow">
    <?php foreach ($counts as $k => $n): ?><div><span class="co-metric"><?= $n ?></span><?= status_badge($k) ?></div><?php endforeach; ?>
  </div></section></div>
  <div class="col-lg-7"><section class="co-card">
    <div class="co-card__head"><span class="co-tile"><?= icon('list') ?></span><h2 class="co-card__title"><?= e(__('att.history')) ?></h2></div>
    <?php if (!$records): ?><?= View::partial('partials/empty', ['icon' => 'check', 'text' => __('att.none_yet')]) ?>
    <?php else: ?><div class="table-responsive"><table class="co-table">
      <thead><tr><th><?= e(__('common.date')) ?></th><th><?= e(__('att.time')) ?></th><th><?= e(__('common.status')) ?></th><th><?= e(__('att.comment')) ?></th></tr></thead>
      <tbody><?php foreach ($records as $r): ?><tr><td class="num"><?= e(fmt_date($r['CodeDate'])) ?></td><td class="num"><?= e(date('H:i', strtotime($r['CheckInTime']))) ?></td><td><?= status_badge($r['ApprovalStatus']) ?></td><td><?= e($r['ReviewComment'] ?? '') ?></td></tr><?php endforeach; ?></tbody>
    </table></div><?php endif; ?>
  </section></div>
</div>
