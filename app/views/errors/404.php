<?php $title = __('err.t404'); ?>
<section class="app-error">
  <div class="app-error__code">404</div>
  <h1 class="co-card__title"><?= e(__('err.t404')) ?></h1>
  <p class="co-caption"><?= e(__('err.d404')) ?></p>
  <a class="co-btn co-btn--primary" href="<?= url(Auth::check() ? '/dashboard' : '/') ?>"><?= e(__('err.home')) ?></a>
</section>
