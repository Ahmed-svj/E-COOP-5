<?php $title = __('nav.profile'); $noErrorSummary = true; ?>
<?= View::partial('partials/page-head', ['heading' => __('profile.title'), 'sub' => __('profile.sub')]) ?>
<div class="row g-3">
  <div class="col-lg-8">
    <form class="co-card app-form" method="post" action="<?= url('/student/profile') ?>" enctype="multipart/form-data" data-validate novalidate>
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-md-6 co-field<?= invalid('full_name') ?>"><label for="full_name"><?= e(__('auth.full_name')) ?></label><input class="co-input" id="full_name" name="full_name" value="<?= e(old('full_name', $s['FullName'])) ?>" required maxlength="150"><?= error_for('full_name') ?></div>
        <div class="col-md-6 co-field"><label for="email"><?= e(__('auth.inst_email')) ?></label><input class="co-input" id="email" value="<?= e($s['Email']) ?>" dir="ltr" disabled></div>
        <div class="col-md-6 co-field"><label for="iid"><?= e(__('auth.inst_id')) ?></label><input class="co-input" id="iid" value="<?= e($s['InstitutionalID']) ?>" dir="ltr" disabled></div>
        <div class="col-md-6 co-field<?= invalid('phone') ?>"><label for="phone"><?= e(__('auth.phone')) ?></label><input class="co-input" id="phone" name="phone" dir="ltr" inputmode="numeric" value="<?= e(old('phone', $s['Phone'])) ?>" required data-rule="phone"><?= error_for('phone') ?></div>
        <div class="col-md-6 co-field<?= invalid('major') ?>"><label for="major"><?= e(__('auth.major')) ?></label>
          <select class="co-input" id="major" name="major" required><?php foreach (majors() as $m): ?><option<?= old('major', $s['Major']) === $m ? ' selected' : '' ?>><?= e($m) ?></option><?php endforeach; ?></select><?= error_for('major') ?></div>
        <div class="col-md-6 co-field<?= invalid('gpa') ?>"><label for="gpa"><?= e(__('profile.gpa')) ?></label><input class="co-input" id="gpa" name="gpa" type="number" step="0.01" min="0" max="5" dir="ltr" value="<?= e(old('gpa', $s['GPA'])) ?>" required><?= error_for('gpa') ?: '<span class="co-field__help">' . e(__('profile.gpa_help')) . '</span>' ?></div>
        <div class="col-12 co-field<?= invalid('cv') ?>"><label for="cv"><?= e(__('profile.cv')) ?></label>
          <input class="co-input app-file" id="cv" name="cv" type="file" accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document" data-max="<?= config('upload_max_bytes') ?>">
          <?= error_for('cv') ?: '<span class="co-field__help">' . e(__('upload.help')) . '</span>' ?>
          <?php if ($s['CVFilePath']): ?><a class="co-btn co-btn--soft co-btn--sm mt-2 align-self-start" href="<?= url('/files/cv/' . $s['StudentID']) ?>"><?= icon('download', 'co-ico--sm') ?><?= e(__('profile.cv_current')) ?></a><?php endif; ?>
        </div>
      </div>
      <div><button class="co-btn co-btn--primary" type="submit"><?= e(__('common.save')) ?></button></div>
    </form>
  </div>
  <div class="col-lg-4">
    <section class="co-card">
      <div class="co-card__head"><span class="co-tile co-tile--mint"><?= icon('check') ?></span><h2 class="co-card__title"><?= e(__('profile.checklist')) ?></h2></div>
      <?php foreach (['Phone' => 'auth.phone', 'Major' => 'auth.major', 'GPA' => 'profile.gpa', 'CVFilePath' => 'profile.cv'] as $f => $k): $done = $s[$f] !== null && $s[$f] !== ''; ?>
        <div class="app-checkline"><?= $done ? '<span class="co-badge co-badge--mint">' . e(__('common.done')) . '</span>' : '<span class="co-badge co-badge--amber">' . e(__('common.missing')) . '</span>' ?><span><?= e(__($k)) ?></span></div>
      <?php endforeach; ?>
      <p class="co-caption"><?= e(__('profile.checklist_d')) ?></p>
    </section>
  </div>
</div>
