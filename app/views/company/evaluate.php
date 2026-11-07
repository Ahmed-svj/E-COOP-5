<?php $title = __('eval.evaluate'); $noErrorSummary = true; ?>
<?= View::partial('partials/page-head', ['heading' => __('eval.company_title', ['name' => $t['FullName']]), 'sub' => $t['Title']]) ?>
<?php if ($existing): ?>
  <div class="co-card"><div class="app-ringrow"><?= View::partial('partials/ring', ['pct' => (int) $existing['Score'], 'caption' => __('eval.score')]) ?><p class="app-body-text"><?= nl2br(e($existing['Comments'] ?? '')) ?></p></div></div>
<?php else: ?>
  <?= View::partial('shared/_eval-form', ['action' => '/company/evaluate/' . $t['StudentID'], 'cancel' => '/company/trainees']) ?>
<?php endif; ?>
