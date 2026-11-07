<?php
$title = __('nav.home');
$approved = (int) $summary['ApprovedDays'];
$required = config('required_days');
$pct = $required ? (int) round(min(100, $approved * 100 / $required)) : 0;
$weekDone = count(array_filter($week, fn ($d) => $d['status'] !== null));
$scripts = ['attendance.js'];
?>
<p class="co-greet"><?= e(greeting()) ?></p>
<p class="co-name"><bdi><?= e(short_name($s['FullName'])) ?></bdi></p>

<?php if (!Student::isComplete($s)): ?>
  <div class="co-alert co-alert--amber mt-3" role="status"><?= icon('info') ?><div><strong><?= e(__('dash.complete_profile')) ?></strong><?= e(__('dash.complete_profile_d')) ?> <a href="<?= url('/student/profile') ?>"><?= e(__('nav.profile')) ?></a></div></div>
<?php endif; ?>

<div class="co-grid app-grid">
  <div class="co-stack">
    <section class="co-card">
      <div class="co-card__head"><span class="co-tile"><?= icon('bolt') ?></span><h2 class="co-card__title"><?= e(__('dash.att_week')) ?></h2><a href="<?= url('/student/attendance') ?>" aria-label="<?= e(__('nav.attendance')) ?>"><?= icon('chev', 'app-flip') ?></a></div>
      <div class="co-metric-row"><div><div class="co-metric"><?= $weekDone ?> <small>/ 5 <?= e(__('common.days')) ?></small></div><p class="co-caption"><?= e(__('dash.sun_thu')) ?></p></div>
        <div class="co-delta-wrap"><span class="co-delta"><?= (int) $summary['PendingDays'] ?></span><span class="co-caption"><?= e(__('dash.pending_review')) ?></span></div></div>
      <?= View::partial('partials/bars', ['week' => $week]) ?>
    </section>

    <section class="co-card">
      <div class="co-card__head"><span class="co-tile"><?= icon('clock') ?></span><h2 class="co-card__title"><?= e(__('dash.progress')) ?></h2></div>
      <div class="app-ringrow">
        <?= View::partial('partials/ring', ['pct' => $pct, 'caption' => $approved . ' / ' . $required . ' ' . __('common.days')]) ?>
        <div class="co-chips co-chips--stack co-chips--mint">
          <span class="co-chip" aria-checked="true"><?= icon('ok', 'co-ico--sm') ?><?= (int) $summary['ApprovedDays'] ?> <?= e(__('status.Approved')) ?></span>
          <span class="co-chip"><?= icon('clock', 'co-ico--sm') ?><?= (int) $summary['PendingDays'] ?> <?= e(__('status.Pending')) ?></span>
          <span class="co-chip"><?= icon('x', 'co-ico--sm') ?><?= (int) $summary['RejectedDays'] ?> <?= e(__('status.Rejected')) ?></span>
        </div>
      </div>
    </section>
  </div>

  <div class="co-stack">
    <section class="co-card">
      <div class="co-card__head"><span class="co-tile co-tile--mint"><?= icon('key') ?></span><h2 class="co-card__title"><?= e(__('dash.checkin')) ?></h2></div>
      <?php if (!$placement): ?>
        <p class="co-caption app-body-text"><?= e(__('att.no_placement')) ?></p>
        <div><a class="co-btn co-btn--primary co-btn--sm" href="<?= url('/student/opportunities') ?>"><?= e(__('dash.browse')) ?></a></div>
      <?php elseif ($checkedToday): ?>
        <div class="co-alert co-alert--mint" role="status"><?= icon('ok') ?><div><strong><?= e(__('dash.checked_today')) ?></strong><?= e(__('dash.checked_today_d')) ?></div></div>
      <?php else: ?>
        <?= View::partial('student/_code-form', ['locked' => $s['CodeLockoutUntil'] && strtotime($s['CodeLockoutUntil']) > time() ? $s['CodeLockoutUntil'] : null]) ?>
      <?php endif; ?>
    </section>

    <section class="co-card">
      <div class="co-card__head"><span class="co-tile"><?= icon('brief') ?></span><h2 class="co-card__title"><?= e($placement ? __('dash.my_placement') : __('dash.my_applications')) ?></h2><a href="<?= url('/student/applications') ?>" aria-label="<?= e(__('nav.applications')) ?>"><?= icon('chev', 'app-flip') ?></a></div>
      <?php if ($placement): ?>
        <div class="co-row"><span class="co-row__media co-row__media--mint"><?= e(initials($placement['CompanyName'])) ?></span><div class="co-row__text"><span class="co-row__title"><?= e($placement['Title']) ?></span><span class="co-row__sub"><?= e($placement['CompanyName']) ?> · <?= e($placement['City']) ?> · <?= e(fmt_date($placement['StartDate'])) ?> – <?= e(fmt_date($placement['EndDate'])) ?></span></div><?= status_badge($placement['Status']) ?></div>
        <div class="co-row"><span class="co-row__media"><?= icon('users') ?></span><div class="co-row__text"><span class="co-row__title"><?= e($supervisor['FullName'] ?? __('dash.no_supervisor')) ?></span><span class="co-row__sub"><?= e($supervisor ? __('role.Supervisor') . ' · ' . $supervisor['Department'] : __('dash.no_supervisor_d')) ?></span></div></div>
      <?php elseif ($applications): ?>
        <div class="co-list"><?php foreach ($applications as $a): ?>
          <div class="co-row"><span class="co-row__media"><?= e(initials($a['CompanyName'])) ?></span><div class="co-row__text"><span class="co-row__title"><?= e($a['Title']) ?></span><span class="co-row__sub"><?= e($a['CompanyName']) ?> · <?= e($a['City']) ?></span></div><?= status_badge($a['Status']) ?></div>
        <?php endforeach; ?></div>
      <?php else: ?>
        <?= View::partial('partials/empty', ['icon' => 'brief', 'text' => __('dash.no_apps'), 'action' => '<a class="co-btn co-btn--primary co-btn--sm" href="' . url('/student/opportunities') . '">' . e(__('dash.browse')) . '</a>']) ?>
      <?php endif; ?>
    </section>

    <?php if ($recommended): ?>
    <section class="co-card">
      <div class="co-card__head"><span class="co-tile co-tile--lavender"><?= icon('star') ?></span><h2 class="co-card__title"><?= e(__('dash.for_major')) ?></h2></div>
      <div class="co-list"><?php foreach ($recommended as $o): ?>
        <a class="co-row app-row-link" href="<?= url('/student/opportunities/' . $o['OpportunityID']) ?>"><span class="co-row__media co-row__media--mint"><?= e(initials($o['CompanyName'])) ?></span><div class="co-row__text"><span class="co-row__title"><?= e($o['Title']) ?></span><span class="co-row__sub"><?= e($o['CompanyName']) ?> · <?= e($o['City']) ?></span></div><?= icon('chev', 'app-flip') ?></a>
      <?php endforeach; ?></div>
    </section>
    <?php endif; ?>
  </div>

  <div class="co-stack">
    <section class="co-card">
      <?php
        $elapsed = 0;
        if ($placement) {
            $total = max(1, strtotime($placement['EndDate']) - strtotime($placement['StartDate']));
            $elapsed = (int) round(max(0, min(100, (time() - strtotime($placement['StartDate'])) * 100 / $total)));
        }
      ?>
      <div class="co-card__head"><span class="co-tile co-tile--lavender"><?= icon('doc') ?></span><h2 class="co-card__title"><?= e(__('nav.report')) ?></h2>
        <a class="co-iconbtn co-iconbtn--lavender app-iconbtn-sm" href="<?= url('/student/report') ?>" aria-label="<?= e(__('report.upload')) ?>"><?= icon('upload') ?></a></div>
      <?php if ($report): ?>
        <div><?= status_badge($report['ReviewStatus']) ?></div>
        <p class="co-caption"><?= e(__('report.submitted_on', ['date' => fmt_date($report['SubmittedAt'])])) ?></p>
      <?php else: ?>
        <div class="co-metric"><?= $elapsed ?>%</div>
        <div class="co-progress" role="progressbar" aria-valuenow="<?= $elapsed ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?= e(__('dash.time_elapsed')) ?>"><span style="width:<?= $elapsed ?>%"></span></div>
        <p class="co-caption"><?= e($placement ? __('dash.time_elapsed_due', ['date' => fmt_date($placement['EndDate'])]) : __('report.not_ready')) ?></p>
      <?php endif; ?>
    </section>

    <section class="co-card">
      <div class="co-card__head"><span class="co-tile"><?= icon('bell') ?></span><h2 class="co-card__title"><?= e(__('nav.notifications')) ?></h2><a href="<?= url('/notifications') ?>" aria-label="<?= e(__('nav.notifications')) ?>"><?= icon('chev', 'app-flip') ?></a></div>
      <?php if (!$notifications): ?>
        <?= View::partial('partials/empty', ['icon' => 'bell', 'text' => __('notif.none')]) ?>
      <?php else: ?><div class="co-list"><?php foreach ($notifications as $n): ?>
        <a class="co-row app-row-link<?= $n['IsRead'] ? '' : ' app-unread' ?>" href="<?= url('/notifications/' . $n['NotificationID'] . '/open') ?>">
          <span class="co-row__text"><span class="co-row__title app-wrap" dir="auto"><?= e($n['Message']) ?></span><span class="co-row__sub"><bdi><?= e(fmt_date($n['SentDate'], true)) ?></bdi></span></span></a>
      <?php endforeach; ?></div><?php endif; ?>
    </section>
  </div>
</div>
