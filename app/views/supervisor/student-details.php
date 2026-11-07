<?php $title = $s['FullName'];
$counts = ['Approved' => 0, 'Pending' => 0, 'Rejected' => 0]; foreach ($records as $r) { $counts[$r['ApprovalStatus']]++; }
$evalBy = array_column($evals, null, 'EvaluatorType'); $req = config('required_days'); ?>
<a class="co-btn co-btn--soft co-btn--sm mb-3" href="<?= url('/supervisor/students') ?>"><?= icon('chev', 'co-ico--sm app-back') ?><?= e(__('common.back')) ?></a>
<div class="app-pagehead"><div class="d-flex gap-3 align-items-center"><span class="co-row__media"><?= e(initials($s['FullName'])) ?></span><div><h1 class="app-pagehead__title"><?= e($s['FullName']) ?></h1><p class="app-pagehead__sub"><?= e($s['Major']) ?> · <span dir="ltr"><?= e($s['InstitutionalID']) ?></span> · <span dir="ltr"><?= e($s['Email']) ?></span></p></div></div>
  <div class="app-pagehead__actions"><?php if ($s['CVFilePath']): ?><a class="co-btn co-btn--soft co-btn--sm" href="<?= url('/files/cv/' . $s['StudentID']) ?>"><?= icon('download', 'co-ico--sm') ?>CV</a><?php endif; ?>
    <?php if (!isset($evalBy['Academic'])): ?><a class="co-btn co-btn--primary co-btn--sm" href="<?= url('/supervisor/evaluate/' . $s['StudentID']) ?>"><?= e(__('eval.evaluate')) ?></a><?php endif; ?></div></div>
<div class="co-grid app-grid">
  <div class="co-stack">
    <section class="co-card"><div class="co-card__head"><span class="co-tile"><?= icon('bolt') ?></span><h2 class="co-card__title"><?= e(__('dash.att_week')) ?></h2></div><?= View::partial('partials/bars', ['week' => $week]) ?></section>
    <section class="co-card"><div class="co-card__head"><span class="co-tile"><?= icon('clock') ?></span><h2 class="co-card__title"><?= e(__('dash.progress')) ?></h2></div>
      <div class="app-ringrow"><?= View::partial('partials/ring', ['pct' => (int) round(min(100, $counts['Approved'] * 100 / max(1, $req))), 'caption' => $counts['Approved'] . ' / ' . $req]) ?></div></section>
  </div>
  <div class="co-stack">
    <section class="co-card"><div class="co-card__head"><span class="co-tile co-tile--mint"><?= icon('brief') ?></span><h2 class="co-card__title"><?= e(__('dash.my_placement')) ?></h2></div>
      <?php if ($placement): ?><div class="co-row"><span class="co-row__media co-row__media--mint"><?= e(initials($placement['CompanyName'])) ?></span><div class="co-row__text"><span class="co-row__title"><?= e($placement['Title']) ?></span><span class="co-row__sub"><?= e($placement['CompanyName']) ?> · <?= e(fmt_date($placement['StartDate'])) ?> – <?= e(fmt_date($placement['EndDate'])) ?></span></div><?= status_badge($placement['Status']) ?></div>
      <?php else: ?><p class="co-caption"><?= e(__('att.no_placement')) ?></p><?php endif; ?></section>
    <section class="co-card"><div class="co-card__head"><span class="co-tile"><?= icon('list') ?></span><h2 class="co-card__title"><?= e(__('att.history')) ?></h2><a href="<?= url('/supervisor/attendance') ?>" aria-label="<?= e(__('nav.attendance')) ?>"><?= icon('chev', 'app-flip') ?></a></div>
      <?php if (!$records): ?><p class="co-caption"><?= e(__('att.none_yet')) ?></p><?php else: ?><div class="co-list"><?php foreach ($records as $r): ?>
        <div class="co-row app-row-wrap"><div class="co-row__text"><span class="co-row__title"><?= e(fmt_date($r['CodeDate'])) ?></span><span class="co-row__sub"><?= e(date('H:i', strtotime($r['CheckInTime']))) ?><?= $r['ReviewComment'] ? ' · ' . e($r['ReviewComment']) : '' ?></span></div>
        <div class="co-row__actions"><?= status_badge($r['ApprovalStatus']) ?><?php if ($r['ApprovalStatus'] === 'Pending'): ?><form method="post" action="<?= url('/supervisor/attendance/' . $r['AttendanceID'] . '/approve') ?>"><?= csrf_field() ?><button class="co-btn co-btn--primary co-btn--sm"><?= e(__('common.approve')) ?></button></form><?php else: ?><?= View::partial('supervisor/_att-change', ['r' => $r, 'name' => $s['FullName']]) ?><?php endif; ?></div></div>
      <?php endforeach; ?></div><?php endif; ?></section>
  </div>
  <div class="co-stack">
    <section class="co-card"><div class="co-card__head"><span class="co-tile co-tile--lavender"><?= icon('doc') ?></span><h2 class="co-card__title"><?= e(__('nav.report')) ?></h2></div>
      <?php if ($report): ?><div><?= status_badge($report['ReviewStatus']) ?></div><p class="co-caption"><?= e(__('report.submitted_on', ['date' => fmt_date($report['SubmittedAt'])])) ?></p><a class="co-btn co-btn--soft co-btn--sm align-self-start" href="<?= url('/files/report/' . $report['ReportID']) ?>"><?= icon('download', 'co-ico--sm') ?><?= e(__('common.download')) ?></a>
      <?php else: ?><p class="co-caption"><?= e(__('report.not_submitted')) ?></p><?php endif; ?></section>
    <section class="co-card"><div class="co-card__head"><span class="co-tile"><?= icon('star') ?></span><h2 class="co-card__title"><?= e(__('nav.evaluations')) ?></h2></div>
      <?php foreach (['Company', 'Academic'] as $tp): ?><div class="co-row"><div class="co-row__text"><span class="co-row__title"><?= e(__('eval.' . $tp)) ?></span></div><span class="co-metric app-metric-sm"><?= isset($evalBy[$tp]) ? (int) $evalBy[$tp]['Score'] : '—' ?></span></div><?php endforeach; ?></section>
  </div>
</div>
