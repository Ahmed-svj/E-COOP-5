<?php $title = $a['FullName']; ?>
<a class="co-btn co-btn--soft co-btn--sm mb-3" href="<?= url('/company/applicants', ['opportunity' => $a['OpportunityID']]) ?>"><?= icon('chev', 'co-ico--sm app-back') ?><?= e(__('common.back')) ?></a>
<div class="app-pagehead"><div class="d-flex gap-3 align-items-center"><span class="co-row__media"><?= e(initials($a['FullName'])) ?></span>
  <div><h1 class="app-pagehead__title"><?= e($a['FullName']) ?></h1><p class="app-pagehead__sub"><?= e(__('company.applied_for', ['title' => $a['Title'], 'date' => fmt_date($a['SubmittedDate'])])) ?></p></div></div>
  <div class="app-pagehead__actions"><?= status_badge($a['Status']) ?></div></div>
<div class="row g-3">
  <div class="col-lg-8 co-stack">
    <section class="co-card"><div class="co-card__head"><span class="co-tile"><?= icon('user') ?></span><h2 class="co-card__title"><?= e(__('company.applicant_profile')) ?></h2></div>
      <dl class="app-dl">
        <div><dt><?= e(__('auth.major')) ?></dt><dd><?= e($a['Major']) ?></dd></div>
        <div><dt><?= e(__('profile.gpa')) ?></dt><dd><?= e($a['GPA'] ?? '—') ?> / 5.00</dd></div>
        <div><dt><?= e(__('auth.inst_id')) ?></dt><dd dir="ltr"><?= e($a['InstitutionalID']) ?></dd></div>
        <div><dt><?= e(__('auth.email')) ?></dt><dd dir="ltr"><a href="mailto:<?= e($a['Email']) ?>"><?= e($a['Email']) ?></a></dd></div>
        <div><dt><?= e(__('auth.phone')) ?></dt><dd dir="ltr"><?= e($a['Phone'] ?? '—') ?></dd></div>
        <div><dt><?= e(__('company.other_pending')) ?></dt><dd><?= (int) $others ?></dd></div>
      </dl>
      <?php if ($a['CVFilePath']): ?><div><a class="co-btn co-btn--soft co-btn--sm" href="<?= url('/files/cv/' . $a['StudentID']) ?>"><?= icon('download', 'co-ico--sm') ?><?= e(__('company.download_cv')) ?></a></div><?php endif; ?>
    </section>
    <?php if ($a['Status'] === 'Rejected' && $a['RejectionReason']): ?><div class="co-alert co-alert--red"><?= icon('info') ?><div><strong><?= e(__('common.reason')) ?></strong><?= e($a['RejectionReason']) ?></div></div><?php endif; ?>
  </div>
  <div class="col-lg-4 co-stack">
    <section class="co-card"><div class="co-card__head"><span class="co-tile co-tile--mint"><?= icon('brief') ?></span><h2 class="co-card__title"><?= e($a['Title']) ?></h2></div>
      <p class="co-caption m-0"><?= e($a['MajorRequirements']) ?> · <?= e($a['OppCity']) ?></p>
      <div class="co-progress co-progress--mint co-progress--thin" role="progressbar" aria-valuenow="<?= (int) $a['AcceptedCount'] ?>" aria-valuemin="0" aria-valuemax="<?= (int) $a['Capacity'] ?>" aria-label="<?= e(__('opp.seats_title')) ?>"><span style="width:<?= (int) round($a['AcceptedCount'] * 100 / max(1, $a['Capacity'])) ?>%"></span></div>
      <div class="co-progress-meta"><span><?= e(__('opp.seats_title')) ?></span><span><?= (int) $a['AcceptedCount'] ?> / <?= (int) $a['Capacity'] ?></span></div>
      <?php if ($a['Status'] === 'Pending'): ?>
        <div class="d-flex gap-2 flex-wrap">
          <button class="co-btn co-btn--danger-ghost co-btn--sm" type="button" data-bs-toggle="modal" data-bs-target="#rej-<?= (int) $a['ApplicationID'] ?>"><?= e(__('common.reject')) ?></button>
          <?php if ($a['PlacementID']): ?>
            <form method="post" action="<?= url('/company/applications/' . $a['ApplicationID'] . '/accept') ?>" data-confirm="<?= e(__('app.accept_confirm', ['name' => $a['FullName']])) ?>"><?= csrf_field() ?><button class="co-btn co-btn--primary co-btn--sm"<?= $a['AcceptedCount'] >= $a['Capacity'] ? ' disabled' : '' ?>><?= e(__('common.accept')) ?></button></form>
          <?php else: ?>
            <form method="post" action="<?= url('/company/applications/' . $a['ApplicationID'] . '/accept') ?>" class="d-flex gap-2 flex-wrap align-items-end"><?= csrf_field() ?>
              <div class="co-field"><label for="sd"><?= e(__('app.start')) ?></label><input class="co-input" type="date" id="sd" name="start_date" required value="<?= date('Y-m-d', strtotime('next sunday')) ?>"></div>
              <div class="co-field"><label for="ed"><?= e(__('app.end')) ?></label><input class="co-input" type="date" id="ed" name="end_date" required value="<?= date('Y-m-d', strtotime('next sunday +10 weeks')) ?>"></div>
              <button class="co-btn co-btn--primary co-btn--sm" type="submit"><?= e(__('common.accept')) ?></button></form>
          <?php endif; ?>
        </div>
        <?= View::partial('partials/reject-modal', ['id' => 'rej-' . $a['ApplicationID'], 'action' => '/company/applications/' . $a['ApplicationID'] . '/reject', 'title' => __('app.reject_title', ['name' => $a['FullName']])]) ?>
      <?php endif; ?>
    </section>
    <section class="co-card"><div class="co-card__head"><span class="co-tile co-tile--lavender"><?= icon('chat') ?></span><h2 class="co-card__title"><?= e(__('nav.messages')) ?></h2></div>
      <form method="post" action="<?= url('/messages/start') ?>"><?= csrf_field() ?><input type="hidden" name="user_id" value="<?= (int) $a['UserID'] ?>"><button class="co-btn co-btn--soft co-btn--sm"<?= $a['Status'] === 'Accepted' ? '' : ' disabled' ?>><?= icon('chat', 'co-ico--sm') ?><?= e(__('company.message_trainee')) ?></button></form>
      <?php if ($a['Status'] !== 'Accepted'): ?><p class="co-caption m-0"><?= e(__('company.message_after_accept')) ?></p><?php endif; ?>
    </section>
  </div>
</div>
