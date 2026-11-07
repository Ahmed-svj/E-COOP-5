<?php $title = __('reset.title'); $noErrorSummary = true; ?>
<section class="auth"><div class="auth__card co-card">
  <h1 class="auth__title"><?= e(__('reset.title')) ?></h1>
  <form method="post" action="<?= url('/reset-password') ?>" class="app-form" data-validate novalidate>
    <?= csrf_field() ?><input type="hidden" name="token" value="<?= e($token) ?>">
    <div class="co-field<?= invalid('password') ?>"><label for="password"><?= e(__('reset.new')) ?></label><input class="co-input" id="password" name="password" type="password" dir="ltr" required data-rule="password" autocomplete="new-password"><?= error_for('password') ?: '<span class="co-field__help">' . e(__('v.password')) . '</span>' ?></div>
    <div class="co-field"><label for="password_confirmation"><?= e(__('auth.password_confirm')) ?></label><input class="co-input" id="password_confirmation" name="password_confirmation" type="password" dir="ltr" required data-match="password" autocomplete="new-password"></div>
    <button class="co-btn co-btn--primary" type="submit"><?= e(__('reset.save')) ?></button>
  </form>
</div></section>
