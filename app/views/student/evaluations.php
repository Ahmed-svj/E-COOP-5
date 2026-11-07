<?php $title = __('nav.evaluations'); $byType = array_column($evals, null, 'EvaluatorType'); ?>
<?= View::partial('partials/page-head', ['heading' => __('nav.evaluations'), 'sub' => __('eval.sub')]) ?>
<div class="row g-3">
  <?php foreach (['Company' => 'building', 'Academic' => 'cap'] as $type => $ic): $ev = $byType[$type] ?? null; ?>
    <div class="col-md-6"><section class="co-card h-100">
      <div class="co-card__head"><span class="co-tile<?= $type === 'Academic' ? ' co-tile--lavender' : ' co-tile--mint' ?>"><?= icon($ic) ?></span><h2 class="co-card__title"><?= e(__('eval.' . $type)) ?></h2></div>
      <?php if ($ev): ?>
        <div class="app-ringrow"><?= View::partial('partials/ring', ['pct' => (int) $ev['Score'], 'caption' => __('eval.score')]) ?>
          <div><p class="m-0"><strong><?= e($ev['Evaluator']) ?></strong></p><p class="co-caption"><?= e(fmt_date($ev['EvaluatedAt'])) ?></p></div></div>
        <?php if ($ev['Comments']): ?><p class="app-body-text"><?= nl2br(e($ev['Comments'])) ?></p><?php endif; ?>
      <?php else: ?>
        <?= View::partial('partials/empty', ['icon' => 'clock', 'text' => __('eval.waiting'), 'hint' => __('eval.waiting_' . $type)]) ?>
      <?php endif; ?>
    </section></div>
  <?php endforeach; ?>
</div>
<section class="co-card mt-3"><div class="app-statrow">
  <div><span class="co-metric"><?= (int) ($summary['ApprovedDays'] ?? 0) ?></span><span class="co-caption"><?= e(__('eval.approved_days')) ?></span></div>
  <div><span class="co-metric"><?= $byType ? (int) round(array_sum(array_column($evals, 'Score')) / count($evals)) : '—' ?></span><span class="co-caption"><?= e(__('eval.average')) ?></span></div>
  <div><?= $report ? status_badge($report['ReviewStatus']) : '<span class="co-badge">' . e(__('report.not_submitted')) . '</span>' ?><span class="co-caption"><?= e(__('nav.report')) ?></span></div>
</div></section>
