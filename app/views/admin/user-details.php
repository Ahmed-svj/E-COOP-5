<?php $title = $u['FullName']; ?>
<a class="co-btn co-btn--soft co-btn--sm mb-3" href="<?= url('/admin/users') ?>"><?= icon('chev', 'co-ico--sm app-back') ?><?= e(__('common.back')) ?></a>
<div class="app-pagehead"><div class="d-flex gap-3 align-items-center"><span class="co-row__media"><?= e(initials($u['FullName'])) ?></span>
  <div><h1 class="app-pagehead__title"><?= e($u['FullName']) ?></h1><p class="app-pagehead__sub"><?= e(__('role.' . $u['Role'])) ?> · <span dir="ltr"><?= e($u['Email']) ?></span></p></div></div>
  <div class="app-pagehead__actions"><span class="co-badge<?= $u['AccountStatus'] === 'Active' ? ' co-badge--mint' : '' ?>"><?= e(__('status.' . $u['AccountStatus'])) ?></span>
  <?php if ((int) $u['UserID'] !== Auth::id()): ?><form method="post" action="<?= url('/admin/users/' . $u['UserID'] . '/toggle') ?>" data-confirm="<?= e(__($u['AccountStatus'] === 'Active' ? 'admin.suspend_confirm' : 'admin.activate_confirm', ['name' => $u['FullName']])) ?>"><?= csrf_field() ?><button class="co-btn co-btn--sm <?= $u['AccountStatus'] === 'Active' ? 'co-btn--danger-ghost' : 'co-btn--primary' ?>"><?= e(__($u['AccountStatus'] === 'Active' ? 'admin.suspend' : 'admin.activate')) ?></button></form><?php endif; ?></div></div>
<div class="row g-3">
  <div class="col-lg-6 co-stack">
    <section class="co-card"><div class="co-card__head"><span class="co-tile"><?= icon('user') ?></span><h2 class="co-card__title"><?= e(__('admin.account')) ?></h2></div>
      <dl class="app-dl">
        <div><dt><?= e(__('auth.phone')) ?></dt><dd dir="ltr"><?= e($u['Phone'] ?? '—') ?></dd></div>
        <div><dt><?= e(__('admin.registered')) ?></dt><dd><?= e(fmt_date($u['CreatedAt'])) ?></dd></div>
        <div><dt><?= e(__('admin.last_login')) ?></dt><dd><?= e(fmt_date($u['LastLoginAt'], true)) ?></dd></div>
        <div><dt><?= e(__('admin.failed_logins')) ?></dt><dd><?= (int) $u['FailedLoginCount'] ?><?= $u['LockoutUntil'] && strtotime($u['LockoutUntil']) > time() ? ' · ' . e(__('admin.locked_until', ['time' => date('H:i', strtotime($u['LockoutUntil']))])) : '' ?></dd></div>
        <div><dt><?= e(__('admin.privacy_accepted')) ?></dt><dd><?= e(fmt_date($u['PrivacyAcceptedAt'])) ?></dd></div>
      </dl></section>
    <?php if ($profile): ?>
    <section class="co-card"><div class="co-card__head"><span class="co-tile co-tile--mint"><?= icon($u['Role'] === 'Company' ? 'building' : ($u['Role'] === 'Student' ? 'cap' : 'users')) ?></span><h2 class="co-card__title"><?= e(__('role.' . $u['Role'])) ?></h2></div>
      <dl class="app-dl">
      <?php if ($u['Role'] === 'Student'): ?>
        <div><dt><?= e(__('auth.inst_id')) ?></dt><dd dir="ltr"><?= e($profile['InstitutionalID']) ?></dd></div>
        <div><dt><?= e(__('auth.major')) ?></dt><dd><?= e($profile['Major']) ?> · <?= e(__('profile.gpa')) ?> <?= e($profile['GPA'] ?? '—') ?></dd></div>
        <div><dt><?= e(__('dash.my_placement')) ?></dt><dd><?= $profile['Title'] ? e($profile['Title'] . ' · ' . $profile['CompanyName']) . ' ' . status_badge($profile['PlacementStatus']) : '—' ?></dd></div>
        <div><dt><?= e(__('role.Supervisor')) ?></dt><dd><?= e($profile['SupervisorName'] ?? '—') ?></dd></div>
        <div><dt><?= e(__('nav.applications')) ?></dt><dd><?= (int) $profile['Apps'] ?></dd></div>
        <div><dt><?= e(__('eval.approved_days')) ?></dt><dd><?= (int) $profile['Days'] ?></dd></div>
        <?php if ($profile['CVFilePath']): ?><div><dt><?= e(__('profile.cv')) ?></dt><dd><a href="<?= url('/files/cv/' . $profile['StudentID']) ?>"><?= e(__('common.download')) ?></a></dd></div><?php endif; ?>
      <?php elseif ($u['Role'] === 'Company'): ?>
        <div><dt><?= e(__('auth.company_name')) ?></dt><dd><?= e($profile['CompanyName']) ?></dd></div>
        <div><dt><?= e(__('common.city')) ?></dt><dd><?= e($profile['City']) ?></dd></div>
        <div><dt><?= e(__('company.approval')) ?></dt><dd><?= status_badge($profile['ApprovalStatus']) ?></dd></div>
        <div><dt><?= e(__('nav.opportunities')) ?></dt><dd><?= (int) $profile['Opps'] ?></dd></div>
        <div><dt><?= e(__('nav.trainees')) ?></dt><dd><?= (int) $profile['Trainees'] ?></dd></div>
      <?php elseif ($u['Role'] === 'Supervisor'): ?>
        <div><dt><?= e(__('admin.department')) ?></dt><dd><?= e($profile['Department']) ?></dd></div>
        <div><dt><?= e(__('company.approval')) ?></dt><dd><?= status_badge($profile['ApprovalStatus'] === 'Pending' ? 'Pending Approval' : $profile['ApprovalStatus']) ?></dd></div>
        <div><dt><?= e(__('sup.load')) ?></dt><dd><?= (int) $profile['CurrentLoad'] ?> / <?= (int) $profile['MaxStudentLoad'] ?></dd></div>
      <?php endif; ?>
      </dl>
      <?php if ($u['Role'] === 'Supervisor' && $profile['ApprovalStatus'] === 'Pending'): ?>
        <div class="d-flex gap-2"><form method="post" action="<?= url('/admin/supervisors/' . $profile['SupervisorID'] . '/approve') ?>"><?= csrf_field() ?><button class="co-btn co-btn--primary co-btn--sm"><?= e(__('common.approve')) ?></button></form>
          <button class="co-btn co-btn--danger-ghost co-btn--sm" type="button" data-bs-toggle="modal" data-bs-target="#rejsup-<?= (int) $profile['SupervisorID'] ?>"><?= e(__('common.reject')) ?></button></div>
        <?= View::partial('partials/reject-modal', ['id' => 'rejsup-' . $profile['SupervisorID'], 'action' => '/admin/supervisors/' . $profile['SupervisorID'] . '/reject', 'title' => __('admin.reject_supervisor', ['name' => $u['FullName']])]) ?>
      <?php endif; ?></section>
    <?php endif; ?>
  </div>
  <div class="col-lg-6"><section class="co-card"><div class="co-card__head"><span class="co-tile co-tile--lavender"><?= icon('shield') ?></span><h2 class="co-card__title"><?= e(__('admin.recent_activity')) ?></h2><a href="<?= url('/admin/audit-logs') ?>" aria-label="<?= e(__('nav.audit')) ?>"><?= icon('chev', 'app-flip') ?></a></div>
    <?php if (!$logs): ?><p class="co-caption"><?= e(__('admin.no_activity')) ?></p><?php else: ?><div class="co-list"><?php foreach ($logs as $l): ?><div class="app-logline"><span class="co-row__title app-mono"><?= e($l['Action']) ?></span><span class="co-row__sub"><?= e(date('j M Y H:i', strtotime($l['Timestamp']))) ?> · <span dir="ltr"><?= e($l['IPAddress']) ?></span></span></div><?php endforeach; ?></div><?php endif; ?>
  </section></div>
</div>
