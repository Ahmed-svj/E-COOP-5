<?php $title = __('nav.opportunities'); $scripts = ['opportunities.js']; ?>
<?= View::partial('partials/page-head', ['heading' => __('opp.browse'), 'sub' => __('opp.browse_sub')]) ?>
<form class="app-filters" method="get" action="<?= url('/student/opportunities') ?>" data-live-filter role="search">
  <label class="co-search app-filters__q"><?= icon('search') ?><input type="search" name="q" value="<?= e($q) ?>" placeholder="<?= e(__('search.Student')) ?>" aria-label="<?= e(__('search.label')) ?>"></label>
  <select class="co-input" name="city" aria-label="<?= e(__('common.city')) ?>"><option value=""><?= e(__('opp.all_cities')) ?></option><?php foreach ($cities as $c): ?><option<?= $city === $c['City'] ? ' selected' : '' ?>><?= e($c['City']) ?></option><?php endforeach; ?></select>
  <select class="co-input" name="major" aria-label="<?= e(__('auth.major')) ?>"><option value=""><?= e(__('opp.all_majors')) ?></option><?php foreach ($majors as $m): ?><option<?= $major === $m['Major'] ? ' selected' : '' ?>><?= e($m['Major']) ?></option><?php endforeach; ?></select>
  <noscript><button class="co-btn co-btn--primary" type="submit"><?= e(__('common.filter')) ?></button></noscript>
</form>
<p class="co-caption mb-2" aria-live="polite" data-count><?= e(__('opp.count', ['n' => count($items)])) ?></p>
<div data-results><?= View::partial('student/_opportunity-cards', ['items' => $items]) ?></div>
