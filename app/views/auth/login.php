<?php $title = __('auth.login'); $noErrorSummary = true; ?>
<section class="auth">
  <div class="auth__card co-card">
    <img class="auth__logo" src="<?= asset('img/logo.png') ?>" alt="COOP" width="140" height="79">
    <div><p class="co-greet m-0"><?= e(greeting()) ?></p><h1 class="auth__title"><?= e(__('auth.login_title')) ?></h1></div>
    <form method="post" action="<?= url('/login') ?>" class="app-form" data-validate novalidate>
      <?= csrf_field() ?>
      <div class="co-field"><label for="email"><?= e(__('auth.email')) ?></label>
        <input class="co-input" id="email" name="email" type="email" dir="ltr" autocomplete="username" value="<?= e(old('email')) ?>" required autofocus></div>
      <div class="co-field"><label for="password"><?= e(__('auth.password')) ?></label>
        <input class="co-input" id="password" name="password" type="password" dir="ltr" autocomplete="current-password" required></div>
      <div class="auth__row"><a href="<?= url('/forgot-password') ?>"><?= e(__('auth.forgot')) ?></a></div>
      <button class="co-btn co-btn--primary w-100 justify-content-center" type="submit"><?= e(__('auth.login')) ?></button>
    </form>
    <p class="co-caption auth__switch"><?= e(__('auth.no_account')) ?> <a href="<?= url('/register/student') ?>"><?= e(__('auth.as_student')) ?></a> · <a href="<?= url('/register/company') ?>"><?= e(__('auth.as_company')) ?></a> · <a href="<?= url('/register/supervisor') ?>"><?= e(__('auth.as_supervisor')) ?></a></p>
  </div>
  <aside class="auth__side" aria-hidden="true"><?= View::partial('auth/_side') ?></aside>
</section>
