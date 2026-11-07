<?php $title = __('nav.home'); $u = Auth::user(); $placedPct = (int) round($k['placed'] * 100 / max(1, $k['students'])); ?>
<p class="co-greet"><?= e(greeting()) ?></p>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-2"><div></div><a class="co-btn co-btn--soft co-btn--sm" href="<?= url('/admin/report') ?>"><?= icon('doc', 'co-ico--sm') ?><?= e(__('nav.system_report')) ?></a></div>
<p class="co-name"><bdi><?= e(short_name($u['FullName'])) ?></bdi></p>
<?php if ($k['pending_supervisors']): ?>
  <a class="co-alert co-alert--amber mt-3 app-row-link" href="<?= url('/admin/users', ['role' => 'Supervisor']) ?>"><?= icon('users') ?><div><strong><?= e(__('admin.supervisors_waiting', ['n' => $k['pending_supervisors']])) ?></strong><?= e(__('admin.supervisors_waiting_d')) ?></div></a>
<?php endif; ?>
<div class="app-kpis">
  <?php foreach ([['pending_companies', 'building', 'admin.k_companies', '/admin/companies', 'amber'], ['pending_opportunities', 'brief', 'admin.k_opps', '/admin/opportunities', 'lavender'], ['active_placements', 'cap', 'admin.k_active', '/admin/assign', 'mint'], ['unassigned', 'assign', 'admin.k_unassigned', '/admin/assign', '']] as [$key, $ic, $lbl, $href, $tone]): ?>
    <section class="co-card"><div class="co-card__head"><span class="co-tile<?= $tone && $tone !== 'amber' ? ' co-tile--' . $tone : '' ?>"><?= icon($ic) ?></span><h2 class="co-card__title"><?= e(__($lbl)) ?></h2><a href="<?= url($href) ?>" aria-label="<?= e(__($lbl)) ?>"><?= icon('chev', 'app-flip') ?></a></div><div class="co-metric"><?= (int) $k[$key] ?></div></section>
  <?php endforeach; ?>
</div>
<div class="co-grid app-grid">
  <div class="co-stack">
    <section class="co-card"><div class="co-card__head"><span class="co-tile"><?= icon('chart') ?></span><h2 class="co-card__title"><?= e(__('admin.placement_rate')) ?></h2></div>
      <div class="app-ringrow"><?= View::partial('partials/ring', ['pct' => $placedPct, 'caption' => $k['placed'] . ' / ' . $k['students']]) ?>
        <div class="co-chips co-chips--stack co-chips--mint"><?php foreach ($byStatus as $b): ?><span class="co-chip"<?= $b['Status'] === 'Accepted' ? ' aria-checked="true"' : '' ?>><?= (int) $b['n'] ?> <?= e(__('status.' . $b['Status'])) ?></span><?php endforeach; ?></div></div></section>
    <section class="co-card"><div class="co-card__head"><span class="co-tile co-tile--lavender"><?= icon('users') ?></span><h2 class="co-card__title"><?= e(__('admin.sup_load')) ?></h2><a href="<?= url('/admin/assign') ?>" aria-label="<?= e(__('nav.assign')) ?>"><?= icon('chev', 'app-flip') ?></a></div>
      <?php foreach ($loads as $l): $p = (int) round($l['CurrentLoad'] * 100 / max(1, $l['MaxStudentLoad'])); ?>
        <div class="app-oppmini"><div class="co-progress-meta"><span class="co-row__title"><?= e($l['FullName']) ?></span><span><?= (int) $l['CurrentLoad'] ?> / <?= (int) $l['MaxStudentLoad'] ?></span></div>
        <div class="co-progress co-progress--thin<?= $p < 90 ? ' co-progress--mint' : '' ?>" role="progressbar" aria-valuenow="<?= $p ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?= e($l['FullName']) ?>"><span style="width:<?= min(100, $p) ?>%"></span></div></div>
      <?php endforeach; ?></section>
  </div>
  <div class="co-stack">
    <section class="co-card"><div class="co-card__head"><span class="co-tile"><?= icon('building') ?></span><h2 class="co-card__title"><?= e(__('admin.companies_waiting')) ?></h2><a href="<?= url('/admin/companies') ?>" aria-label="<?= e(__('nav.companies')) ?>"><?= icon('chev', 'app-flip') ?></a></div>
      <?php if (!$companies): ?><?= View::partial('partials/empty', ['icon' => 'ok', 'text' => __('admin.nothing_waiting')]) ?><?php else: ?><div class="co-list"><?php foreach ($companies as $c): ?>
        <div class="co-row"><span class="co-row__media"><?= e(initials($c['CompanyName'])) ?></span><div class="co-row__text"><span class="co-row__title"><?= e($c['CompanyName']) ?></span><span class="co-row__sub"><?= e($c['City']) ?> · <?= e(fmt_date($c['CreatedAt'])) ?></span></div>
          <form method="post" action="<?= url('/admin/companies/' . $c['CompanyID'] . '/approve') ?>"><?= csrf_field() ?><button class="co-btn co-btn--primary co-btn--sm"><?= e(__('common.approve')) ?></button></form></div>
      <?php endforeach; ?></div><?php endif; ?></section>
    <section class="co-card"><div class="co-card__head"><span class="co-tile"><?= icon('brief') ?></span><h2 class="co-card__title"><?= e(__('admin.opps_waiting')) ?></h2><a href="<?= url('/admin/opportunities') ?>" aria-label="<?= e(__('nav.opportunities')) ?>"><?= icon('chev', 'app-flip') ?></a></div>
      <?php if (!$opps): ?><?= View::partial('partials/empty', ['icon' => 'ok', 'text' => __('admin.nothing_waiting')]) ?><?php else: ?><div class="co-list"><?php foreach ($opps as $o): ?>
        <div class="co-row"><span class="co-row__media co-row__media--mint"><?= e(initials($o['CompanyName'])) ?></span><div class="co-row__text"><span class="co-row__title"><?= e($o['Title']) ?></span><span class="co-row__sub"><?= e($o['CompanyName']) ?> · <?= e($o['City']) ?> · <?= (int) $o['Capacity'] ?></span></div>
          <form method="post" action="<?= url('/admin/opportunities/' . $o['OpportunityID'] . '/approve') ?>"><?= csrf_field() ?><button class="co-btn co-btn--primary co-btn--sm"><?= e(__('common.approve')) ?></button></form></div>
      <?php endforeach; ?></div><?php endif; ?></section>
  </div>
  <div class="co-stack">
    <section class="co-card"><div class="co-card__head"><span class="co-tile"><?= icon('shield') ?></span><h2 class="co-card__title"><?= e(__('nav.audit')) ?></h2><a href="<?= url('/admin/audit-logs') ?>" aria-label="<?= e(__('nav.audit')) ?>"><?= icon('chev', 'app-flip') ?></a></div>
      <div class="co-list"><?php foreach ($recentLogs as $l): ?><div class="app-logline"><span class="co-row__title app-mono"><?= e($l['Action']) ?></span><span class="co-row__sub"><?= e($l['FullName'] ?? '—') ?> · <?= e(date('j M H:i', strtotime($l['Timestamp']))) ?></span></div><?php endforeach; ?></div></section>
  </div>
</div>
