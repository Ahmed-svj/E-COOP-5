<?php $title = __('auth.forgot'); ?>
<section class="auth"><div class="auth__card co-card">
  <h1 class="auth__title"><?= e(__('auth.forgot')) ?></h1>
  <p class="co-caption"><?= e(__('reset.lead')) ?></p>
  <form method="post" action="<?= url('/forgot-password') ?>" class="app-form" data-validate novalidate>
    <?= csrf_field() ?>
    <div class="co-field"><label for="email"><?= e(__('auth.email')) ?></label><input class="co-input" id="email" name="email" type="email" dir="ltr" required autocomplete="email"></div>
    <button class="co-btn co-btn--primary" type="submit"><?= e(__('reset.send')) ?></button>
  </form>
  <p class="co-caption auth__switch"><a href="<?= url('/login') ?>"><?= e(__('auth.back_login')) ?></a></p>
</div></section>
