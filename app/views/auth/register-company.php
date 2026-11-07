<?php $title = __('auth.register_company'); $noErrorSummary = true; ?>
<section class="auth">
  <div class="auth__card auth__card--wide co-card">
    <div><span class="co-badge co-badge--lavender"><?= e(__('role.Company')) ?></span><h1 class="auth__title"><?= e(__('auth.register_company')) ?></h1>
      <p class="co-caption"><?= e(__('auth.company_review_note')) ?></p></div>
    <form method="post" action="<?= url('/register/company') ?>" class="app-form" data-validate novalidate>
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-md-6 co-field<?= invalid('company_name') ?>"><label for="company_name"><?= e(__('auth.company_name')) ?></label><input class="co-input" id="company_name" name="company_name" value="<?= e(old('company_name')) ?>" required maxlength="190" autocomplete="organization"><?= error_for('company_name') ?></div>
        <div class="col-md-6 co-field<?= invalid('city') ?>"><label for="city"><?= e(__('common.city')) ?></label>
          <select class="co-input" id="city" name="city" required><option value=""><?= e(__('common.choose')) ?></option>
          <?php foreach (cities() as $c): ?><option<?= old('city') === $c ? ' selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select><?= error_for('city') ?></div>
        <div class="col-md-6 co-field<?= invalid('full_name') ?>"><label for="full_name"><?= e(__('auth.contact_person')) ?></label><input class="co-input" id="full_name" name="full_name" value="<?= e(old('full_name')) ?>" required minlength="3" maxlength="150" autocomplete="name"><?= error_for('full_name') ?></div>
        <div class="col-md-6 co-field<?= invalid('phone') ?>"><label for="phone"><?= e(__('auth.phone')) ?></label><input class="co-input" id="phone" name="phone" dir="ltr" inputmode="numeric" placeholder="05XXXXXXXX" value="<?= e(old('phone')) ?>" required data-rule="phone"><?= error_for('phone') ?></div>
        <div class="col-12 co-field<?= invalid('email') ?>"><label for="email"><?= e(__('auth.work_email')) ?></label><input class="co-input" id="email" name="email" type="email" dir="ltr" value="<?= e(old('email')) ?>" required autocomplete="email"><?= error_for('email') ?></div>
        <div class="col-md-6 co-field<?= invalid('password') ?>"><label for="password"><?= e(__('auth.password')) ?></label><input class="co-input" id="password" name="password" type="password" dir="ltr" required data-rule="password" autocomplete="new-password"><?= error_for('password') ?: '<span class="co-field__help">' . e(__('v.password')) . '</span>' ?></div>
        <div class="col-md-6 co-field"><label for="password_confirmation"><?= e(__('auth.password_confirm')) ?></label><input class="co-input" id="password_confirmation" name="password_confirmation" type="password" dir="ltr" required data-match="password" autocomplete="new-password"></div>
        <div class="col-12 co-field<?= invalid('privacy') ?>"><label class="app-check"><input type="checkbox" name="privacy" value="1" required> <span><?= e(__('auth.privacy_accept')) ?> <a href="<?= url('/privacy') ?>"><?= e(__('auth.privacy_link')) ?></a></span></label><?= error_for('privacy') ?></div>
      </div>
      <button class="co-btn co-btn--primary" type="submit"><?= e(__('auth.create_account')) ?></button>
    </form>
    <p class="co-caption auth__switch"><?= e(__('auth.have_account')) ?> <a href="<?= url('/login') ?>"><?= e(__('auth.login')) ?></a> · <a href="<?= url('/register/supervisor') ?>"><?= e(__('auth.as_supervisor')) ?></a></p>
  </div>
</section>
