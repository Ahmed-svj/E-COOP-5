<?php $title = __('err.t500'); ?>
<section class="app-error">
  <div class="app-error__code">500</div>
  <h1 class="co-card__title"><?= e(__('err.t500')) ?></h1>
  <p class="co-caption"><?= e(__('err.d500')) ?></p>
  <a class="co-btn co-btn--primary" href="<?= url(Auth::check() ? '/dashboard' : '/') ?>"><?= e(__('err.home')) ?></a>
</section>
