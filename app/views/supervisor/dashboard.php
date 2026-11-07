<?php $title = __('nav.home');
$max = (int) $sp['MaxStudentLoad']; $loadPct = (int) round($load * 100 / max(1, $max));
$completed = count(array_filter($cohort, fn ($r) => $r['PlacementStatus'] === 'Completed')); ?>
<p class="co-greet"><?= e(greeting()) ?></p>
<p class="co-name"><bdi><?= e(short_name($sp['FullName'])) ?></bdi></p>
<?php if (($sp['ApprovalStatus'] ?? 'Approved') !== 'Approved'): ?>
  <div class="co-alert <?= $sp['ApprovalStatus'] === 'Rejected' ? 'co-alert--red' : 'co-alert--amber' ?> mt-3" role="status"><?= icon('info') ?>
    <div><strong><?= e(__('sup.status_' . $sp['ApprovalStatus'])) ?></strong><?= e($sp['ApprovalStatus'] === 'Rejected' ? ($sp['RejectionReason'] ?? '') : __('sup.pending_d')) ?></div></div>
<?php endif; ?>
<div class="app-kpis">
  <section class="co-card"><div class="co-card__head"><span class="co-tile"><?= icon('users') ?></span><h2 class="co-card__title"><?= e(__('sup.k_students')) ?></h2></div><div class="co-metric"><?= $load ?> <small>/ <?= $max ?></small></div>
    <div class="co-progress co-progress--thin<?= $loadPct < 90 ? ' co-progress--mint' : '' ?>" role="progressbar" aria-valuenow="<?= $loadPct ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?= e(__('sup.load')) ?>"><span style="width:<?= min(100, $loadPct) ?>%"></span></div></section>
  <section class="co-card"><div class="co-card__head"><span class="co-tile co-tile--lavender"><?= icon('check') ?></span><h2 class="co-card__title"><?= e(__('sup.k_attendance')) ?></h2><a href="<?= url('/supervisor/attendance') ?>" aria-label="<?= e(__('nav.attendance')) ?>"><?= icon('chev', 'app-flip') ?></a></div><div class="co-metric"><?= $pendingAttendance ?></div><p class="co-caption"><?= e(__('dash.pending_review')) ?></p></section>
  <section class="co-card"><div class="co-card__head"><span class="co-tile co-tile--lavender"><?= icon('doc') ?></span><h2 class="co-card__title"><?= e(__('sup.k_reports')) ?></h2><a href="<?= url('/supervisor/reports') ?>" aria-label="<?= e(__('nav.reports')) ?>"><?= icon('chev', 'app-flip') ?></a></div><div class="co-metric"><?= $pendingReports ?></div><p class="co-caption"><?= e(__('dash.pending_review')) ?></p></section>
  <section class="co-card"><div class="co-card__head"><span class="co-tile co-tile--mint"><?= icon('star') ?></span><h2 class="co-card__title"><?= e(__('sup.k_completed')) ?></h2></div><div class="co-metric"><?= $completed ?></div><p class="co-caption"><?= e(__('sup.ready_eval')) ?></p></section>
</div>
<div class="row g-3 mt-1">
  <div class="col-lg-5"><section class="co-card h-100">
    <div class="co-card__head"><span class="co-tile co-tile--lavender"><?= icon('check') ?></span><h2 class="co-card__title"><?= e(__('sup.to_review')) ?></h2>
      <?php if ($recent): ?><form method="post" action="<?= url('/supervisor/attendance/approve-all') ?>" data-confirm="<?= e(__('att.approve_all_confirm')) ?>"><?= csrf_field() ?><button class="co-btn co-btn--soft co-btn--sm"><?= e(__('att.approve_all')) ?></button></form><?php endif; ?></div>
    <?php if (!$recent): ?><?= View::partial('partials/empty', ['icon' => 'ok', 'text' => __('sup.all_reviewed')]) ?>
    <?php else: ?><div class="co-list"><?php foreach ($recent as $r): ?>
      <div class="co-row"><span class="co-row__media"><?= e(initials($r['FullName'])) ?></span><div class="co-row__text"><span class="co-row__title"><?= e($r['FullName']) ?></span><span class="co-row__sub"><?= e(fmt_date($r['CodeDate'])) ?> · <?= e(date('H:i', strtotime($r['CheckInTime']))) ?></span></div>
        <form method="post" action="<?= url('/supervisor/attendance/' . $r['AttendanceID'] . '/approve') ?>"><?= csrf_field() ?><button class="co-btn co-btn--primary co-btn--sm"><?= e(__('common.approve')) ?></button></form></div>
    <?php endforeach; ?></div><?php endif; ?>
  </section></div>
  <div class="col-lg-7"><section class="co-card h-100">
    <div class="co-card__head"><span class="co-tile"><?= icon('chart') ?></span><h2 class="co-card__title"><?= e(__('sup.cohort')) ?></h2><a class="co-btn co-btn--soft co-btn--sm" href="<?= url('/supervisor/export/csv') ?>"><?= icon('download', 'co-ico--sm') ?>CSV</a></div>
    <?php if (!$cohort): ?><?= View::partial('partials/empty', ['icon' => 'users', 'text' => __('sup.no_students')]) ?>
    <?php else: $maxDays = max(1, max(array_map(fn ($r) => (int) $r['Approved'] + (int) $r['Pending'], $cohort))); ?>
      <div class="co-bars app-bars-tall" role="img" aria-label="<?= e(__('sup.cohort')) ?>">
        <?php foreach (array_slice($cohort, 0, 10) as $r): ?><div class="co-bar<?= (int) $r['Approved'] === 0 ? ' co-bar--lav' : '' ?>"><i style="height:<?= max(6, (int) round(((int) $r['Approved'] + (int) $r['Pending']) * 100 / $maxDays)) ?>%" title="<?= e($r['FullName'] . ': ' . (int) $r['Approved']) ?>"></i><b><?= e(initials($r['FullName'])) ?></b></div><?php endforeach; ?>
      </div>
      <p class="co-caption"><?= e(__('sup.cohort_d')) ?></p>
    <?php endif; ?>
  </section></div>
</div>
