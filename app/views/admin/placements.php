<?php $title = __('nav.placements'); ?>
<?= View::partial('partials/page-head', ['heading' => __('nav.placements'), 'sub' => __('admin.placements_sub')]) ?>
<div class="co-chips mb-3"><?php foreach (['' => 'common.all', 'Active' => 'status.Active', 'Completed' => 'status.Completed'] as $v => $k): ?><a class="co-chip" aria-pressed="<?= $status === $v ? 'true' : 'false' ?>" href="<?= url('/admin/placements', $v ? ['status' => $v] : []) ?>"><?= e(__($k)) ?></a><?php endforeach; ?></div>
<?php if (!$items): ?><?= View::partial('partials/empty', ['icon' => 'cap', 'text' => __('admin.nothing_here')]) ?>
<?php else: ?><div class="row g-3"><?php foreach ($items as $p):
  $total = max(1, strtotime($p['EndDate']) - strtotime($p['StartDate']));
  $pct = (int) round(max(0, min(100, (time() - strtotime($p['StartDate'])) * 100 / $total))); ?>
  <div class="col-lg-6"><article class="co-card h-100">
    <div class="co-card__head"><span class="co-row__media co-row__media--mint app-media-sm"><?= e(initials($p['CompanyName'])) ?></span>
      <div class="co-row__text"><h2 class="co-row__title"><?= e($p['Title']) ?></h2><span class="co-row__sub"><?= e($p['CompanyName']) ?> · <?= e($p['City']) ?> · <?= e(fmt_date($p['StartDate'])) ?> – <?= e(fmt_date($p['EndDate'])) ?></span></div><?= status_badge($p['Status']) ?></div>
    <div class="co-progress<?= $p['Status'] === 'Completed' ? ' co-progress--mint' : '' ?> co-progress--thin" role="progressbar" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?= e(__('dash.time_elapsed')) ?>"><span style="width:<?= $pct ?>%"></span></div>
    <div class="app-statrow app-statrow--sm">
      <div><span class="co-metric app-metric-sm"><?= (int) $p['Trainees'] ?></span><span class="co-caption"><?= e(__('nav.trainees')) ?></span></div>
      <div><span class="co-metric app-metric-sm"><?= (int) $p['CodeDays'] ?></span><span class="co-caption"><?= e(__('admin.code_days')) ?></span></div>
      <div><span class="co-metric app-metric-sm"><?= (int) $p['ApprovedDays'] ?></span><span class="co-caption"><?= e(__('eval.approved_days')) ?></span></div>
    </div>
    <div class="app-tags"><?php foreach ($p['students'] as $s): ?><a class="co-badge" href="<?= url('/admin/users/' . $s['UserID']) ?>"><?= e($s['FullName']) ?></a><?php endforeach; ?>
      <?php if ($p['NoSupervisor']): ?><a class="co-badge co-badge--amber" href="<?= url('/admin/assign') ?>"><?= e(__('admin.n_no_supervisor', ['n' => $p['NoSupervisor']])) ?></a><?php endif; ?></div>
    <?php if ($p['Status'] === 'Active'): ?><form method="post" action="<?= url('/admin/placements/' . $p['PlacementID'] . '/complete') ?>" data-confirm="<?= e(__('admin.complete_confirm')) ?>"><?= csrf_field() ?><button class="co-btn co-btn--soft co-btn--sm"><?= e(__('admin.complete')) ?></button></form><?php endif; ?>
  </article></div>
<?php endforeach; ?></div><?php endif; ?>
