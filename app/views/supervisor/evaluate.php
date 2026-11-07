<?php $title = __('eval.evaluate'); $noErrorSummary = true; ?>
<?= View::partial('partials/page-head', ['heading' => __('eval.academic_title', ['name' => $s['FullName']]), 'sub' => __('eval.academic_sub')]) ?>
<?php if ($existing): ?>
  <div class="co-card"><div class="app-ringrow"><?= View::partial('partials/ring', ['pct' => (int) $existing['Score'], 'caption' => __('eval.score')]) ?><p class="app-body-text"><?= nl2br(e($existing['Comments'] ?? '')) ?></p></div></div>
<?php elseif (!$ok): ?>
  <div class="co-alert co-alert--amber"><?= icon('info') ?><div><strong><?= e(__('eval.not_yet')) ?></strong><?= e($why) ?></div></div>
<?php else: ?>
  <?= View::partial('shared/_eval-form', ['action' => '/supervisor/evaluate/' . $s['StudentID'], 'cancel' => '/supervisor/students/' . $s['StudentID']]) ?>
<?php endif; ?>
