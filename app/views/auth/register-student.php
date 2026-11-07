<?php $title = __('auth.register_student'); $noErrorSummary = true; ?>
<section class="auth">
  <div class="auth__card auth__card--wide co-card">
    <div><span class="co-badge co-badge--mint"><?= e(__('role.Student')) ?></span><h1 class="auth__title"><?= e(__('auth.register_student')) ?></h1></div>
    <form method="post" action="<?= url('/register/student') ?>" class="app-form" data-validate novalidate>
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-12 co-field<?= invalid('full_name') ?>"><label for="full_name"><?= e(__('auth.full_name')) ?></label><input class="co-input" id="full_name" name="full_name" value="<?= e(old('full_name')) ?>" required minlength="3" maxlength="150" autocomplete="name"><?= error_for('full_name') ?></div>
        <div class="col-md-6 co-field<?= invalid('email') ?>"><label for="email"><?= e(__('auth.inst_email')) ?></label><input class="co-input" id="email" name="email" type="email" dir="ltr" value="<?= e(old('email')) ?>" required data-domain="<?= e(implode(',', student_domains())) ?>" autocomplete="email"><?= error_for('email') ?: '<span class="co-field__help">' . e(__('auth.inst_email_help', ['domain' => implode(', @', student_domains())])) . '</span>' ?></div>
        <div class="col-md-6 co-field<?= invalid('institutional_id') ?>"><label for="institutional_id"><?= e(__('auth.inst_id')) ?></label><input class="co-input" id="institutional_id" name="institutional_id" dir="ltr" value="<?= e(old('institutional_id')) ?>" required maxlength="20"><?= error_for('institutional_id') ?></div>
        <div class="col-md-6 co-field<?= invalid('major') ?>"><label for="major"><?= e(__('auth.major')) ?></label>
          <select class="co-input" id="major" name="major" required><option value=""><?= e(__('common.choose')) ?></option>
          <?php foreach (majors() as $m): ?><option<?= old('major') === $m ? ' selected' : '' ?>><?= e($m) ?></option><?php endforeach; ?></select><?= error_for('major') ?></div>
        <div class="col-md-6 co-field<?= invalid('phone') ?>"><label for="phone"><?= e(__('auth.phone')) ?></label><input class="co-input" id="phone" name="phone" dir="ltr" inputmode="numeric" placeholder="05XXXXXXXX" value="<?= e(old('phone')) ?>" required data-rule="phone" autocomplete="tel"><?= error_for('phone') ?></div>
        <div class="col-md-6 co-field<?= invalid('password') ?>"><label for="password"><?= e(__('auth.password')) ?></label><input class="co-input" id="password" name="password" type="password" dir="ltr" required data-rule="password" autocomplete="new-password"><?= error_for('password') ?: '<span class="co-field__help">' . e(__('v.password')) . '</span>' ?></div>
        <div class="col-md-6 co-field"><label for="password_confirmation"><?= e(__('auth.password_confirm')) ?></label><input class="co-input" id="password_confirmation" name="password_confirmation" type="password" dir="ltr" required data-match="password" autocomplete="new-password"></div>
        <div class="col-12 co-field<?= invalid('privacy') ?>"><label class="app-check"><input type="checkbox" name="privacy" value="1" required> <span><?= e(__('auth.privacy_accept')) ?> <a href="<?= url('/privacy') ?>"><?= e(__('auth.privacy_link')) ?></a></span></label><?= error_for('privacy') ?></div>
      </div>
      <button class="co-btn co-btn--primary" type="submit"><?= e(__('auth.create_account')) ?></button>
    </form>
    <p class="co-caption auth__switch"><?= e(__('auth.have_account')) ?> <a href="<?= url('/login') ?>"><?= e(__('auth.login')) ?></a> · <a href="<?= url('/register/company') ?>"><?= e(__('auth.as_company')) ?></a> · <a href="<?= url('/register/supervisor') ?>"><?= e(__('auth.as_supervisor')) ?></a></p>
  </div>
</section>
