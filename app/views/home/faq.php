<?php $title = __('nav.faq'); ?>
<section class="site-wrap narrow">
  <h1 class="section-title section-title--lg"><?= e(__('faq.title')) ?></h1>
  <p class="hero__lead"><?= e(__('faq.lead')) ?></p>
  <?php foreach (['students' => ['q1', 'q2', 'q3', 'q4', 'q5'], 'companies' => ['q6', 'q7', 'q8'], 'supervisors' => ['q9', 'q10']] as $group => $qs): ?>
    <h2 class="section-title"><?= e(__('faq.g_' . $group)) ?></h2>
    <div class="co-stack">
      <?php foreach ($qs as $q): ?>
        <details class="co-card app-faq"><summary class="co-card__title"><?= e(__('faq.' . $q)) ?></summary><p class="app-body-text co-caption"><?= e(__('faq.' . $q . '_a')) ?></p></details>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
  <div class="co-card mt-5 d-flex flex-row align-items-center gap-3 flex-wrap"><span class="co-tile co-tile--mint"><?= icon('mail') ?></span><p class="m-0 flex-grow-1"><?= e(__('faq.more')) ?></p><a class="co-btn co-btn--primary" href="<?= url('/contact') ?>"><?= e(__('nav.contact')) ?></a></div>
</section>
