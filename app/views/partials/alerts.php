<?php
$map = ['success' => ['mint', 'ok'], 'warning' => ['amber', 'info'], 'error' => ['red', 'info'], 'info' => ['', 'info']];
foreach ($map as $type => [$tone, $ico]):
    $msg = Session::pull($type);
    if (!$msg) continue; ?>
  <div class="co-alert<?= $tone ? ' co-alert--' . $tone : '' ?> app-flash" role="<?= $type === 'success' || $type === 'info' ? 'status' : 'alert' ?>">
    <?= icon($ico) ?><div><?= e($msg) ?></div>
    <button type="button" class="app-flash__close" data-dismiss-alert aria-label="<?= e(__('a11y.close')) ?>"><?= icon('x', 'co-ico--sm') ?></button>
  </div>
<?php endforeach; ?>
<?php if (errors() && empty($noErrorSummary)): ?>
  <div class="co-alert co-alert--red app-flash" role="alert"><?= icon('info') ?><div><strong><?= e(__('v.summary')) ?></strong></div></div>
<?php endif; ?>
