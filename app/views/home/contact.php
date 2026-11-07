<?php $title = __('nav.contact'); ?>
<section class="site-wrap narrow">
  <h1 class="section-title section-title--lg"><?= e(__('contact.title')) ?></h1>
  <p class="hero__lead"><?= e(__('contact.lead')) ?></p>
  <form class="co-card app-form" method="post" action="<?= url('/contact') ?>" data-validate novalidate>
    <?= csrf_field() ?>
    <input type="text" name="website" class="app-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
    <div class="row g-3">
      <div class="col-md-6 co-field<?= invalid('name') ?>"><label for="c-name"><?= e(__('contact.name')) ?></label><input class="co-input" id="c-name" name="name" value="<?= e(old('name')) ?>" required maxlength="150"><?= error_for('name') ?></div>
      <div class="col-md-6 co-field<?= invalid('email') ?>"><label for="c-email"><?= e(__('auth.email')) ?></label><input class="co-input" id="c-email" type="email" name="email" dir="ltr" value="<?= e(old('email')) ?>" required><?= error_for('email') ?></div>
      <div class="col-12 co-field<?= invalid('topic') ?>"><label for="c-topic"><?= e(__('contact.topic')) ?></label><input class="co-input" id="c-topic" name="topic" value="<?= e(old('topic')) ?>" required maxlength="150"><?= error_for('topic') ?></div>
      <div class="col-12 co-field<?= invalid('body') ?>"><label for="c-body"><?= e(__('contact.body')) ?></label><textarea class="co-input app-textarea" id="c-body" name="body" rows="5" required minlength="10" maxlength="5000"><?= e(old('body')) ?></textarea><?= error_for('body') ?></div>
    </div>
    <div><button class="co-btn co-btn--primary" type="submit"><?= icon('send', 'co-ico--sm') ?><?= e(__('contact.send')) ?></button></div>
  </form>
</section>
