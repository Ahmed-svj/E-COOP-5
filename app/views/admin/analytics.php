<?php $title = __('nav.analytics'); $scripts = ['vendor/chart.js/chart.umd.min.js', 'charts.js'];
$chart = fn (array $rows, string $labelKey, string $valueKey, bool $translate = false) => e(json_encode([
    'labels' => array_map(fn ($r) => $translate ? __('status.' . $r[$labelKey]) : $r[$labelKey], $rows),
    'values' => array_map(fn ($r) => (float) $r[$valueKey], $rows),
    'tones'  => $translate ? array_map(fn ($r) => status_tone($r[$labelKey]) ?: 'neutral', $rows) : [],
], JSON_UNESCAPED_UNICODE)); ?>
<?= View::partial('partials/page-head', ['heading' => __('nav.analytics'), 'sub' => __('admin.analytics_sub')]) ?>
<div class="app-kpis">
  <section class="co-card"><h2 class="co-card__title"><?= e(__('admin.students_total')) ?></h2><div class="co-metric"><?= (int) $k['students'] ?></div></section>
  <section class="co-card"><h2 class="co-card__title"><?= e(__('admin.placed')) ?></h2><div class="co-metric"><?= (int) $k['placed'] ?></div></section>
  <?php foreach ($scores as $s): ?><section class="co-card"><h2 class="co-card__title"><?= e(__('admin.avg_' . $s['EvaluatorType'])) ?></h2><div class="co-metric"><?= e($s['avg']) ?></div><p class="co-caption"><?= e(__('admin.n_evals', ['n' => $s['n']])) ?></p></section><?php endforeach; ?>
</div>
<div class="row g-3 mt-1">
  <div class="col-lg-8"><section class="co-card"><div class="co-card__head"><span class="co-tile"><?= icon('check') ?></span><h2 class="co-card__title"><?= e(__('admin.att_14')) ?></h2></div>
    <div class="app-chartbox"><canvas data-chart="attendance" data-series="<?= e(json_encode(['labels' => array_map(fn ($r) => date('j M', strtotime($r['d'])), $attendance), 'approved' => array_map('intval', array_column($attendance, 'approved')), 'pending' => array_map('intval', array_column($attendance, 'pending')), 'rejected' => array_map('intval', array_column($attendance, 'rejected')), 'names' => [__('status.Approved'), __('status.Pending'), __('status.Rejected')]], JSON_UNESCAPED_UNICODE)) ?>" role="img" aria-label="<?= e(__('admin.att_14')) ?>"></canvas></div></section></div>
  <div class="col-lg-4"><section class="co-card"><div class="co-card__head"><span class="co-tile co-tile--lavender"><?= icon('list') ?></span><h2 class="co-card__title"><?= e(__('admin.apps_status')) ?></h2></div>
    <div class="app-chartbox"><canvas data-chart="doughnut" data-series="<?= $chart($byStatus, 'Status', 'n', true) ?>" role="img" aria-label="<?= e(__('admin.apps_status')) ?>"></canvas></div></section></div>
  <div class="col-lg-6"><section class="co-card"><div class="co-card__head"><span class="co-tile co-tile--mint"><?= icon('pin') ?></span><h2 class="co-card__title"><?= e(__('admin.by_city')) ?></h2></div>
    <div class="app-chartbox"><canvas data-chart="bar" data-series="<?= $chart($byCity, 'City', 'n') ?>" role="img" aria-label="<?= e(__('admin.by_city')) ?>"></canvas></div></section></div>
  <div class="col-lg-6"><section class="co-card"><div class="co-card__head"><span class="co-tile"><?= icon('cap') ?></span><h2 class="co-card__title"><?= e(__('admin.by_major')) ?></h2></div>
    <div class="app-chartbox"><canvas data-chart="hbar" data-series="<?= $chart($byMajor, 'Major', 'n') ?>" role="img" aria-label="<?= e(__('admin.by_major')) ?>"></canvas></div></section></div>
</div>
