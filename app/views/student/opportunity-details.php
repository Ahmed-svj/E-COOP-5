<?php $title = $o['Title']; $left = $o['Capacity'] - $o['AcceptedCount']; ?>
<a class="co-btn co-btn--soft co-btn--sm mb-3" href="<?= url('/student/opportunities') ?>"><?= icon('chev', 'co-ico--sm app-back') ?><?= e(__('common.back')) ?></a>
<div class="row g-3">
  <div class="col-lg-8"><article class="co-card">
    <div class="co-card__head"><span class="co-row__media co-row__media--mint"><?= e(initials($o['CompanyName'])) ?></span>
      <div class="co-row__text"><h1 class="app-pagehead__title"><?= e($o['Title']) ?></h1><span class="co-row__sub"><?= e($o['CompanyName']) ?> · <?= e($o['City']) ?></span></div></div>
    <div class="app-tags"><span class="co-badge co-badge--lavender"><?= e($o['MajorRequirements']) ?></span><span class="co-badge"><?= e(__('opp.posted', ['date' => fmt_date($o['PostedDate'])])) ?></span></div>
    <div class="app-prose"><?= nl2br(e($o['Description'])) ?></div>
  </article></div>
  <div class="col-lg-4"><aside class="co-card">
    <div class="co-card__head"><span class="co-tile"><?= icon('users') ?></span><h2 class="co-card__title"><?= e(__('opp.seats_title')) ?></h2></div>
    <div class="co-metric"><?= max(0, $left) ?> <small>/ <?= (int) $o['Capacity'] ?></small></div>
    <div class="co-progress co-progress--mint co-progress--thin" role="progressbar" aria-valuenow="<?= (int) $o['AcceptedCount'] ?>" aria-valuemin="0" aria-valuemax="<?= (int) $o['Capacity'] ?>" aria-label="<?= e(__('opp.seats_title')) ?>"><span style="width:<?= (int) round($o['AcceptedCount'] * 100 / max(1, $o['Capacity'])) ?>%"></span></div>
    <?php if ($myApp): ?>
      <div class="co-alert" role="status"><?= icon('info') ?><div><strong><?= e(__('opp.applied_on', ['date' => fmt_date($myApp['SubmittedDate'])])) ?></strong><?= status_badge($myApp['Status']) ?></div></div>
    <?php elseif ($s['PlacementID']): ?>
      <div class="co-alert co-alert--amber"><?= icon('info') ?><div><?= e(__('apply.placed')) ?></div></div>
    <?php elseif (!$complete): ?>
      <div class="co-alert co-alert--amber"><?= icon('info') ?><div><strong><?= e(__('apply.incomplete')) ?></strong><a href="<?= url('/student/profile') ?>"><?= e(__('nav.profile')) ?></a></div></div>
      <button class="co-btn co-btn--primary" disabled><?= e(__('apply.button')) ?></button>
    <?php elseif ($left <= 0): ?>
      <button class="co-btn co-btn--primary" disabled><?= e(__('opp.full')) ?></button>
    <?php else: ?>
      <form method="post" action="<?= url('/student/opportunities/' . $o['OpportunityID'] . '/apply') ?>" data-confirm="<?= e(__('apply.confirm')) ?>">
        <?= csrf_field() ?><button class="co-btn co-btn--primary w-100 justify-content-center" type="submit"><?= icon('send', 'co-ico--sm') ?><?= e(__('apply.button')) ?></button>
      </form>
      <p class="co-caption"><?= e(__('apply.note')) ?></p>
    <?php endif; ?>
  </aside></div>
</div>
