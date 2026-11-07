<?php $title = __('nav.privacy'); ?>
<section class="site-wrap narrow">
  <h1 class="section-title section-title--lg"><?= e(__('about.privacy_t')) ?></h1>
  <p class="hero__lead"><?= e(__('about.privacy')) ?></p>
  <?php foreach (['collect', 'use', 'access', 'keep', 'rights'] as $k): ?>
    <section class="co-card mt-3"><div class="co-card__head"><span class="co-tile co-tile--lavender"><?= icon($k === 'access' ? 'lock' : ($k === 'rights' ? 'user' : ($k === 'keep' ? 'clock' : 'shield'))) ?></span><h2 class="co-card__title"><?= e(__('privacy.' . $k . '_t')) ?></h2></div>
      <p class="app-body-text co-caption"><?= e(__('privacy.' . $k)) ?></p></section>
  <?php endforeach; ?>
  <p class="co-caption mt-4"><?= e(__('privacy.updated', ['date' => fmt_date('2026-11-01')])) ?></p>
</section>
