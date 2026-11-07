<?php if (!$items): ?>
  <?= View::partial('partials/empty', ['icon' => 'search', 'text' => __('opp.none'), 'hint' => __('opp.none_hint')]) ?>
<?php else: ?>
<div class="row g-3">
  <?php foreach ($items as $o): $left = $o['Capacity'] - $o['AcceptedCount']; ?>
    <div class="col-md-6 col-xl-4"><article class="co-card h-100 app-oppcard">
      <div class="co-card__head"><span class="co-row__media co-row__media--mint app-media-sm"><?= e(initials($o['CompanyName'])) ?></span>
        <div class="co-row__text"><h2 class="co-row__title"><a class="stretched-link" href="<?= url('/student/opportunities/' . $o['OpportunityID']) ?>"><?= e($o['Title']) ?></a></h2><span class="co-row__sub"><?= e($o['CompanyName']) ?></span></div></div>
      <p class="co-caption app-body-text"><?= e(mb_strimwidth($o['Description'], 0, 140, '…')) ?></p>
      <div class="app-tags">
        <span class="co-badge"><?= icon('pin', 'co-ico--sm') ?><?= e($o['City']) ?></span>
        <span class="co-badge co-badge--lavender"><?= e($o['MajorRequirements']) ?></span>
        <?php if ($o['MyStatus']): ?><?= status_badge($o['MyStatus']) ?><?php else: ?><span class="co-badge<?= $left > 0 ? ' co-badge--mint' : '' ?>"><?= e($left > 0 ? __('opp.seats', ['n' => $left]) : __('opp.full')) ?></span><?php endif; ?>
      </div>
    </article></div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
