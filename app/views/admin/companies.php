<?php $title = __('nav.companies'); ?>
<?= View::partial('partials/page-head', ['heading' => __('nav.companies'), 'sub' => __('admin.companies_sub')]) ?>
<div class="co-chips mb-3"><?php foreach (['Pending', 'Approved', 'Rejected'] as $st): ?><a class="co-chip" aria-pressed="<?= $status === $st ? 'true' : 'false' ?>" href="<?= url('/admin/companies', ['status' => $st]) ?>"><?= e(__('status.' . $st)) ?></a><?php endforeach; ?></div>
<?php if (!$items): ?><?= View::partial('partials/empty', ['icon' => 'building', 'text' => __('admin.nothing_here')]) ?>
<?php else: ?><div class="co-table-wrap table-responsive"><table class="co-table">
  <thead><tr><th><?= e(__('role.Company')) ?></th><th><?= e(__('auth.contact_person')) ?></th><th><?= e(__('common.city')) ?></th><th><?= e(__('admin.registered')) ?></th><th><?= e(__('common.status')) ?></th><th></th></tr></thead>
  <tbody><?php foreach ($items as $c): ?>
    <tr><td><strong><?= e($c['CompanyName']) ?></strong><br><span class="co-caption"><?= e(__('admin.n_opps', ['n' => $c['Opps']])) ?></span></td><td><?= e($c['FullName']) ?><br><span class="co-caption" dir="ltr"><?= e($c['Email']) ?> · <?= e($c['Phone']) ?></span></td>
      <td><?= e($c['City']) ?></td><td class="num"><?= e(fmt_date($c['CreatedAt'])) ?></td><td><?= status_badge($c['ApprovalStatus']) ?><?= $c['RejectionReason'] ? '<br><span class="co-caption">' . e($c['RejectionReason']) . '</span>' : '' ?></td>
      <td class="app-actions"><?php if ($c['ApprovalStatus'] === 'Pending'): ?>
        <button class="co-btn co-btn--danger-ghost co-btn--sm" type="button" data-bs-toggle="modal" data-bs-target="#rej-<?= (int) $c['CompanyID'] ?>"><?= e(__('common.reject')) ?></button>
        <form method="post" action="<?= url('/admin/companies/' . $c['CompanyID'] . '/approve') ?>"><?= csrf_field() ?><button class="co-btn co-btn--primary co-btn--sm"><?= e(__('common.approve')) ?></button></form><?php endif; ?></td></tr>
  <?php endforeach; ?></tbody></table></div>
  <?php foreach ($items as $c): if ($c['ApprovalStatus'] === 'Pending'): ?><?= View::partial('partials/reject-modal', ['id' => 'rej-' . $c['CompanyID'], 'action' => '/admin/companies/' . $c['CompanyID'] . '/reject', 'title' => __('admin.reject_company', ['name' => $c['CompanyName']])]) ?><?php endif; endforeach; ?>
<?php endif; ?>
