<?php $title = __('nav.opportunities');
$new = $c['ApprovalStatus'] === 'Approved' ? '<a class="co-btn co-btn--primary" href="' . url('/company/opportunities/create') . '">' . icon('plus', 'co-ico--sm') . e(__('opp.new')) . '</a>' : ''; ?>
<?= View::partial('partials/page-head', ['heading' => __('nav.opportunities'), 'sub' => __('opp.company_sub'), 'actions' => $new]) ?>
<?php if (!$items): ?>
  <?= View::partial('partials/empty', ['icon' => 'brief', 'text' => __('opp.none_company'), 'action' => $new]) ?>
<?php else: ?>
<div class="co-table-wrap table-responsive"><table class="co-table">
  <thead><tr><th><?= e(__('opp.title')) ?></th><th><?= e(__('common.city')) ?></th><th><?= e(__('opp.seats_title')) ?></th><th><?= e(__('nav.applicants')) ?></th><th><?= e(__('common.status')) ?></th><th></th></tr></thead>
  <tbody><?php foreach ($items as $o): ?>
    <tr><td><strong><?= e($o['Title']) ?></strong><br><span class="co-caption"><?= e($o['MajorRequirements']) ?></span>
        <?php if ($o['Status'] === 'Rejected' && $o['RejectionReason']): ?><br><span class="co-caption app-reason"><?= e(__('common.reason')) ?>: <?= e($o['RejectionReason']) ?></span><?php endif; ?></td>
      <td><?= e($o['City']) ?></td><td class="num"><?= (int) $o['AcceptedCount'] ?> / <?= (int) $o['Capacity'] ?></td>
      <td><a href="<?= url('/company/applicants', ['opportunity' => $o['OpportunityID']]) ?>"><?= e(__('company.n_pending', ['n' => $o['PendingCount']])) ?></a></td>
      <td><?= status_badge($o['Status']) ?></td>
      <td class="app-actions"><?php if ($o['Status'] !== 'Closed'): ?><a class="co-btn co-btn--soft co-btn--sm" href="<?= url('/company/opportunities/' . $o['OpportunityID'] . '/edit') ?>"><?= e(__('common.edit')) ?></a><?php endif; ?>
        <?php if ($o['Status'] === 'Approved'): ?><form method="post" action="<?= url('/company/opportunities/' . $o['OpportunityID'] . '/close') ?>" data-confirm="<?= e(__('opp.close_confirm')) ?>"><?= csrf_field() ?><button class="co-btn co-btn--danger-ghost co-btn--sm"><?= e(__('opp.close')) ?></button></form><?php endif; ?></td></tr>
  <?php endforeach; ?></tbody>
</table></div>
<?php endif; ?>
