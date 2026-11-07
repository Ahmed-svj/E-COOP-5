<?php $title = __('nav.opportunities'); ?>
<?= View::partial('partials/page-head', ['heading' => __('nav.opportunities'), 'sub' => __('admin.opps_sub')]) ?>
<div class="co-chips mb-3"><?php foreach (['Pending Approval', 'Approved', 'Rejected', 'Closed'] as $st): ?><a class="co-chip" aria-pressed="<?= $status === $st ? 'true' : 'false' ?>" href="<?= url('/admin/opportunities', ['status' => $st]) ?>"><?= e(__('status.' . $st)) ?></a><?php endforeach; ?></div>
<?php if (!$items): ?><?= View::partial('partials/empty', ['icon' => 'brief', 'text' => __('admin.nothing_here')]) ?>
<?php else: ?><div class="row g-3"><?php foreach ($items as $o): ?>
  <div class="col-lg-6"><article class="co-card h-100">
    <div class="co-card__head"><span class="co-row__media co-row__media--mint app-media-sm"><?= e(initials($o['CompanyName'])) ?></span><div class="co-row__text"><h2 class="co-row__title"><?= e($o['Title']) ?></h2><span class="co-row__sub"><?= e($o['CompanyName']) ?> · <?= e($o['City']) ?> · <?= e(fmt_date($o['PostedDate'])) ?></span></div><?= status_badge($o['Status']) ?></div>
    <p class="app-body-text co-caption"><?= nl2br(e(mb_strimwidth($o['Description'], 0, 400, '…'))) ?></p>
    <div class="app-tags"><span class="co-badge co-badge--lavender"><?= e($o['MajorRequirements']) ?></span><span class="co-badge"><?= e(__('opp.capacity')) ?>: <?= (int) $o['Capacity'] ?></span><span class="co-badge"><?= e(__('admin.n_apps', ['n' => $o['Apps']])) ?></span></div>
    <?php if ($o['RejectionReason']): ?><p class="co-caption app-reason"><?= e(__('common.reason')) ?>: <?= e($o['RejectionReason']) ?></p><?php endif; ?>
    <?php if ($o['Status'] === 'Pending Approval'): ?><div class="d-flex gap-2">
      <form method="post" action="<?= url('/admin/opportunities/' . $o['OpportunityID'] . '/approve') ?>"><?= csrf_field() ?><button class="co-btn co-btn--primary co-btn--sm"><?= e(__('common.approve')) ?></button></form>
      <button class="co-btn co-btn--danger-ghost co-btn--sm" type="button" data-bs-toggle="modal" data-bs-target="#rej-<?= (int) $o['OpportunityID'] ?>"><?= e(__('common.reject')) ?></button></div>
      <?= View::partial('partials/reject-modal', ['id' => 'rej-' . $o['OpportunityID'], 'action' => '/admin/opportunities/' . $o['OpportunityID'] . '/reject', 'title' => __('admin.reject_opp', ['title' => $o['Title']])]) ?>
    <?php endif; ?>
  </article></div>
<?php endforeach; ?></div><?php endif; ?>
