<?php /** @var ?string $locked */ ?>
<?php if ($locked): ?>
  <div class="co-alert co-alert--red" role="alert"><?= icon('lock') ?><div><strong><?= e(__('att.locked', ['time' => date('H:i', strtotime($locked))])) ?></strong></div></div>
<?php endif; ?>
<form method="post" action="<?= url('/student/attendance') ?>" class="app-form" data-code-form>
  <?= csrf_field() ?>
  <div class="co-code<?= $locked ? ' co-code--locked' : '' ?>" role="group" aria-label="<?= e(__('att.code_label')) ?>">
    <?php for ($i = 1; $i <= 6; $i++): ?><input name="code[]" inputmode="numeric" pattern="[0-9]" maxlength="1" autocomplete="off" aria-label="<?= e(__('att.digit', ['n' => $i])) ?>"<?= $locked ? ' disabled' : '' ?> required><?php endfor; ?>
  </div>
  <p class="co-caption"><?= e(__('att.code_help')) ?></p>
  <div><button class="co-btn co-btn--primary" type="submit"<?= $locked ? ' disabled' : '' ?>><?= icon('ok', 'co-ico--sm') ?><?= e(__('att.checkin')) ?></button></div>
</form>
