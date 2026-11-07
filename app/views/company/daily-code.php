<?php $title = __('nav.daily_code'); ?>
<?= View::partial('partials/page-head', ['heading' => __('nav.daily_code'), 'sub' => __('code.sub')]) ?>
<?php if (!$placements): ?>
  <?= View::partial('partials/empty', ['icon' => 'key', 'text' => __('code.no_placements')]) ?>
<?php else: ?>
<div class="row g-3">
<?php foreach ($placements as $p): ?>
  <div class="col-lg-6"><section class="co-card h-100">
    <div class="co-card__head"><span class="co-tile co-tile--mint"><?= icon('key') ?></span><h2 class="co-card__title"><?= e($p['Title']) ?></h2><span class="co-caption"><?= e(fmt_date(date('Y-m-d'))) ?></span></div>
    <?php if ($p['code']): ?>
      <div class="co-code co-code--display app-code-lg" aria-label="<?= e(__('att.code_label')) ?> <?= e($p['code']['Code']) ?>"><?php foreach (str_split($p['code']['Code']) as $d): ?><span><?= e($d) ?></span><?php endforeach; ?></div>
      <p class="co-caption"><?= e(__('code.expires')) ?> · <?= e(__('code.checked', ['n' => $p['CheckedIn'], 't' => $p['Trainees']])) ?></p>
    <?php else: ?>
      <p class="co-caption app-body-text"><?= e(__('code.none_today')) ?></p>
      <form method="post" action="<?= url('/company/daily-code') ?>"><?= csrf_field() ?><input type="hidden" name="placement_id" value="<?= (int) $p['PlacementID'] ?>"><button class="co-btn co-btn--primary"><?= icon('key', 'co-ico--sm') ?><?= e(__('code.generate')) ?></button></form>
    <?php endif; ?>
    <div class="co-list"><?php foreach ($p['students'] as $st): ?>
      <div class="co-row"><span class="co-row__media"><?= e(initials($st['FullName'])) ?></span><div class="co-row__text"><span class="co-row__title"><?= e($st['FullName']) ?></span><span class="co-row__sub"><?= $st['CheckInTime'] ? e(__('code.checked_at', ['time' => date('H:i', strtotime($st['CheckInTime']))])) : e(__('code.not_yet')) ?></span></div><?= $st['ApprovalStatus'] ? status_badge($st['ApprovalStatus']) : '' ?></div>
    <?php endforeach; ?></div>
  </section></div>
<?php endforeach; ?>
</div>
<?php endif; ?>
