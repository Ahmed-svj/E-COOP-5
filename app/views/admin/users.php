<?php $title = __('nav.users'); $noErrorSummary = true; ?>
<?= View::partial('partials/page-head', ['heading' => __('nav.users'), 'sub' => __('admin.users_sub'),
  'actions' => '<button class="co-btn co-btn--primary" type="button" data-bs-toggle="modal" data-bs-target="#new-sup">' . icon('plus', 'co-ico--sm') . e(__('admin.new_supervisor')) . '</button>']) ?>
<form class="app-filters" method="get">
  <label class="co-search app-filters__q"><?= icon('search') ?><input type="search" name="q" value="<?= e($q) ?>" placeholder="<?= e(__('search.Admin')) ?>" aria-label="<?= e(__('search.label')) ?>"></label>
  <div class="co-chips"><?php foreach (['' => 'common.all', 'Student' => 'role.Student', 'Company' => 'role.Company', 'Supervisor' => 'role.Supervisor', 'Admin' => 'role.Admin'] as $v => $lbl): ?><a class="co-chip" aria-pressed="<?= $role === $v ? 'true' : 'false' ?>" href="<?= url('/admin/users', array_filter(['role' => $v, 'q' => $q])) ?>"><?= e(__($lbl)) ?></a><?php endforeach; ?></div>
</form>
<div class="co-table-wrap table-responsive"><table class="co-table">
  <thead><tr><th><?= e(__('auth.full_name')) ?></th><th><?= e(__('auth.email')) ?></th><th><?= e(__('admin.role')) ?></th><th><?= e(__('admin.last_login')) ?></th><th><?= e(__('common.status')) ?></th><th></th></tr></thead>
  <tbody><?php foreach ($users as $u): ?>
    <tr><td><a href="<?= url('/admin/users/' . $u['UserID']) ?>"><strong><?= e($u['FullName']) ?></strong></a></td><td dir="ltr"><?= e($u['Email']) ?></td><td><?= e(__('role.' . $u['Role'])) ?></td><td class="num"><?= e(fmt_date($u['LastLoginAt'], true)) ?></td>
      <td><span class="co-badge<?= $u['AccountStatus'] === 'Active' ? ' co-badge--mint' : '' ?>"><?= e(__('status.' . $u['AccountStatus'])) ?></span><?= $u['SupApproval'] && $u['SupApproval'] !== 'Approved' ? ' ' . status_badge($u['SupApproval'] === 'Pending' ? 'Pending Approval' : 'Rejected') : '' ?></td>
      <td class="app-actions"><?php if ($u['SupApproval'] === 'Pending'): ?>
        <button class="co-btn co-btn--danger-ghost co-btn--sm" type="button" data-bs-toggle="modal" data-bs-target="#rejsup-<?= (int) $u['SupervisorID'] ?>"><?= e(__('common.reject')) ?></button>
        <form method="post" action="<?= url('/admin/supervisors/' . $u['SupervisorID'] . '/approve') ?>"><?= csrf_field() ?><button class="co-btn co-btn--primary co-btn--sm"><?= e(__('common.approve')) ?></button></form>
      <?php elseif ((int) $u['UserID'] !== Auth::id()): ?><form method="post" action="<?= url('/admin/users/' . $u['UserID'] . '/toggle') ?>" data-confirm="<?= e(__($u['AccountStatus'] === 'Active' ? 'admin.suspend_confirm' : 'admin.activate_confirm', ['name' => $u['FullName']])) ?>"><?= csrf_field() ?>
        <button class="co-btn co-btn--sm <?= $u['AccountStatus'] === 'Active' ? 'co-btn--danger-ghost' : 'co-btn--soft' ?>"><?= e(__($u['AccountStatus'] === 'Active' ? 'admin.suspend' : 'admin.activate')) ?></button></form><?php else: ?><span class="co-caption"><?= e(__('admin.you')) ?></span><?php endif; ?></td></tr>
  <?php endforeach; ?></tbody>
</table></div>
<?php foreach ($users as $u): if ($u['SupApproval'] === 'Pending'): ?>
  <?= View::partial('partials/reject-modal', ['id' => 'rejsup-' . $u['SupervisorID'], 'action' => '/admin/supervisors/' . $u['SupervisorID'] . '/reject', 'title' => __('admin.reject_supervisor', ['name' => $u['FullName']])]) ?>
<?php endif; endforeach; ?>
<?= View::partial('partials/pagination', compact('total', 'page', 'per')) ?>

<div class="modal fade<?= errors() ? ' app-open-on-load' : '' ?>" id="new-sup" tabindex="-1" aria-labelledby="new-sup-t" aria-hidden="true"><div class="modal-dialog modal-dialog-centered">
  <form class="modal-content app-modal" method="post" action="<?= url('/admin/users/supervisor') ?>" data-validate novalidate><?= csrf_field() ?>
    <div class="modal-header"><h2 class="co-card__title" id="new-sup-t"><?= e(__('admin.new_supervisor')) ?></h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= e(__('a11y.close')) ?>"></button></div>
    <div class="modal-body"><div class="row g-3">
      <div class="col-12 co-field<?= invalid('full_name') ?>"><label for="s-name"><?= e(__('auth.full_name')) ?></label><input class="co-input" id="s-name" name="full_name" value="<?= e(old('full_name')) ?>" required><?= error_for('full_name') ?></div>
      <div class="col-12 co-field<?= invalid('email') ?>"><label for="s-email"><?= e(__('auth.email')) ?></label><input class="co-input" id="s-email" name="email" type="email" dir="ltr" value="<?= e(old('email')) ?>" required><?= error_for('email') ?></div>
      <div class="col-8 co-field<?= invalid('department') ?>"><label for="s-dep"><?= e(__('admin.department')) ?></label><input class="co-input" id="s-dep" name="department" value="<?= e(old('department', 'Management Information Systems')) ?>" required><?= error_for('department') ?></div>
      <div class="col-4 co-field<?= invalid('max_load') ?>"><label for="s-max"><?= e(__('admin.max_load')) ?></label><input class="co-input" id="s-max" name="max_load" type="number" min="1" max="200" dir="ltr" value="<?= e(old('max_load', '20')) ?>" required><?= error_for('max_load') ?></div>
      <div class="col-12 co-field<?= invalid('password') ?>"><label for="s-pass"><?= e(__('admin.temp_password')) ?></label><input class="co-input" id="s-pass" name="password" type="text" dir="ltr" required data-rule="password" autocomplete="off"><?= error_for('password') ?: '<span class="co-field__help">' . e(__('v.password')) . '</span>' ?></div>
    </div></div>
    <div class="modal-footer"><button type="button" class="co-btn co-btn--soft co-btn--sm" data-bs-dismiss="modal"><?= e(__('common.cancel')) ?></button><button class="co-btn co-btn--primary co-btn--sm" type="submit"><?= e(__('admin.create')) ?></button></div>
  </form></div></div>
