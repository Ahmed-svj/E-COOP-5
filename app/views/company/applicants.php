<?php $title = __('nav.applicants'); ?>
<?= View::partial('partials/page-head', ['heading' => __('nav.applicants'), 'sub' => __('company.applicants_sub')]) ?>
<form class="app-filters" method="get">
  <select class="co-input" name="opportunity" aria-label="<?= e(__('nav.opportunities')) ?>" data-autosubmit-select><option value="0"><?= e(__('company.all_opps')) ?></option><?php foreach ($opps as $op): ?><option value="<?= (int) $op['OpportunityID'] ?>"<?= $oppId === (int) $op['OpportunityID'] ? ' selected' : '' ?>><?= e($op['Title']) ?></option><?php endforeach; ?></select>
  <div class="co-chips"><?php foreach (['Pending', 'Accepted', 'Rejected', 'Withdrawn'] as $st): ?><a class="co-chip" aria-pressed="<?= $status === $st ? 'true' : 'false' ?>" href="<?= url('/company/applicants', ['opportunity' => $oppId, 'status' => $st]) ?>"><?= e(__('status.' . $st)) ?></a><?php endforeach; ?></div>
</form>
<?php if (!$items): ?>
  <?= View::partial('partials/empty', ['icon' => 'users', 'text' => __('company.no_applicants')]) ?>
<?php else: ?>
<div class="co-card"><div class="co-list">
<?php foreach ($items as $a): ?>
  <div class="co-row app-row-wrap"><span class="co-row__media"><?= e(initials($a['FullName'])) ?></span>
    <div class="co-row__text"><a class="co-row__title" href="<?= url('/company/applicants/' . $a['ApplicationID']) ?>"><?= e($a['FullName']) ?></a>
      <span class="co-row__sub"><?= e($a['Major']) ?> · <?= e(__('profile.gpa')) ?> <?= e($a['GPA']) ?> · <?= e($a['Title']) ?> · <?= e(fmt_date($a['SubmittedDate'])) ?></span>
      <span class="co-row__sub" dir="ltr"><?= e($a['Email']) ?> · <?= e($a['Phone']) ?></span></div>
    <div class="co-row__actions">
      <?php if ($a['CVFilePath']): ?><a class="co-btn co-btn--soft co-btn--sm" href="<?= url('/files/cv/' . $a['StudentID']) ?>"><?= icon('download', 'co-ico--sm') ?>CV</a><?php endif; ?>
      <?php if ($a['Status'] === 'Pending'): ?>
        <button class="co-btn co-btn--danger-ghost co-btn--sm" type="button" data-bs-toggle="modal" data-bs-target="#rej-<?= (int) $a['ApplicationID'] ?>"><?= e(__('common.reject')) ?></button>
        <?php if ($a['PlacementID']): ?>
          <form method="post" action="<?= url('/company/applications/' . $a['ApplicationID'] . '/accept') ?>" data-confirm="<?= e(__('app.accept_confirm', ['name' => $a['FullName']])) ?>"><?= csrf_field() ?><button class="co-btn co-btn--primary co-btn--sm"<?= $a['AcceptedCount'] >= $a['Capacity'] ? ' disabled' : '' ?>><?= e(__('common.accept')) ?></button></form>
        <?php else: ?>
          <button class="co-btn co-btn--primary co-btn--sm" type="button" data-bs-toggle="modal" data-bs-target="#acc-<?= (int) $a['ApplicationID'] ?>"<?= $a['AcceptedCount'] >= $a['Capacity'] ? ' disabled' : '' ?>><?= e(__('common.accept')) ?></button>
        <?php endif; ?>
      <?php else: ?><?= status_badge($a['Status']) ?><?php endif; ?>
    </div></div>
  <?php if ($a['Status'] === 'Pending'): ?>
    <?= View::partial('partials/reject-modal', ['id' => 'rej-' . $a['ApplicationID'], 'action' => '/company/applications/' . $a['ApplicationID'] . '/reject', 'title' => __('app.reject_title', ['name' => $a['FullName']])]) ?>
    <?php if (!$a['PlacementID']): ?>
    <div class="modal fade" id="acc-<?= (int) $a['ApplicationID'] ?>" tabindex="-1" aria-labelledby="acc-<?= (int) $a['ApplicationID'] ?>-t" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered"><form class="modal-content app-modal" method="post" action="<?= url('/company/applications/' . $a['ApplicationID'] . '/accept') ?>">
        <?= csrf_field() ?>
        <div class="modal-header"><h2 class="co-card__title" id="acc-<?= (int) $a['ApplicationID'] ?>-t"><?= e(__('app.accept_title', ['name' => $a['FullName']])) ?></h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= e(__('a11y.close')) ?>"></button></div>
        <div class="modal-body"><p class="co-caption"><?= e(__('app.first_accept')) ?></p><div class="row g-3">
          <div class="col-6 co-field"><label for="sd-<?= (int) $a['ApplicationID'] ?>"><?= e(__('app.start')) ?></label><input class="co-input" type="date" id="sd-<?= (int) $a['ApplicationID'] ?>" name="start_date" required value="<?= date('Y-m-d', strtotime('next sunday')) ?>"></div>
          <div class="col-6 co-field"><label for="ed-<?= (int) $a['ApplicationID'] ?>"><?= e(__('app.end')) ?></label><input class="co-input" type="date" id="ed-<?= (int) $a['ApplicationID'] ?>" name="end_date" required value="<?= date('Y-m-d', strtotime('next sunday +10 weeks')) ?>"></div>
        </div></div>
        <div class="modal-footer"><button type="button" class="co-btn co-btn--soft co-btn--sm" data-bs-dismiss="modal"><?= e(__('common.cancel')) ?></button><button class="co-btn co-btn--primary co-btn--sm" type="submit"><?= e(__('common.accept')) ?></button></div>
      </form></div>
    </div>
    <?php endif; ?>
  <?php endif; ?>
<?php endforeach; ?>
</div></div>
<?php endif; ?>
