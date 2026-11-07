<?php $title = __('nav.applications'); ?>
<?= View::partial('partials/page-head', ['heading' => __('nav.applications'), 'sub' => __('app.sub')]) ?>
<div class="co-chips mb-3" role="list">
  <?php foreach (['' => 'common.all', 'Pending' => 'status.Pending', 'Accepted' => 'status.Accepted', 'Rejected' => 'status.Rejected', 'Withdrawn' => 'status.Withdrawn'] as $v => $k): ?>
    <a role="listitem" class="co-chip" href="<?= url('/student/applications', $v ? ['status' => $v] : []) ?>" aria-pressed="<?= $status === $v ? 'true' : 'false' ?>"><?= e(__($k)) ?></a>
  <?php endforeach; ?>
</div>
<?php if (!$items): ?>
  <?= View::partial('partials/empty', ['icon' => 'list', 'text' => __('dash.no_apps'), 'action' => '<a class="co-btn co-btn--primary co-btn--sm" href="' . url('/student/opportunities') . '">' . e(__('dash.browse')) . '</a>']) ?>
<?php else: ?>
<div class="co-card"><div class="co-list">
  <?php foreach ($items as $a): ?>
    <div class="co-row app-row-wrap"><span class="co-row__media"><?= e(initials($a['CompanyName'])) ?></span>
      <div class="co-row__text"><a class="co-row__title" href="<?= url('/student/opportunities/' . $a['OpportunityID']) ?>"><?= e($a['Title']) ?></a>
        <span class="co-row__sub"><?= e($a['CompanyName']) ?> · <?= e($a['City']) ?> · <?= e(__('app.submitted', ['date' => fmt_date($a['SubmittedDate'])])) ?></span>
        <?php if ($a['Status'] === 'Rejected' && $a['RejectionReason']): ?><span class="co-row__sub app-reason"><?= e(__('common.reason')) ?>: <?= e($a['RejectionReason']) ?></span><?php endif; ?></div>
      <div class="co-row__actions"><?= status_badge($a['Status']) ?>
        <?php if ($a['Status'] === 'Pending'): ?><form method="post" action="<?= url('/student/applications/' . $a['ApplicationID'] . '/withdraw') ?>" data-confirm="<?= e(__('app.withdraw_confirm')) ?>"><?= csrf_field() ?><button class="co-btn co-btn--danger-ghost co-btn--sm"><?= e(__('app.withdraw')) ?></button></form><?php endif; ?>
      </div></div>
  <?php endforeach; ?>
</div></div>
<?php endif; ?>
