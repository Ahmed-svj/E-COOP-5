<?php /** Reusable "reason required" dialog. @var string $id @var string $action @var string $title @var string $field */ ?>
<div class="modal fade" id="<?= e($id) ?>" tabindex="-1" aria-labelledby="<?= e($id) ?>-t" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><form class="modal-content app-modal" method="post" action="<?= url($action) ?>">
    <?= csrf_field() ?>
    <div class="modal-header"><h2 class="co-card__title" id="<?= e($id) ?>-t"><?= e($title) ?></h2>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= e(__('a11y.close')) ?>"></button></div>
    <div class="modal-body co-field">
      <label for="<?= e($id) ?>-r"><?= e($label ?? __('common.reason')) ?></label>
      <textarea class="co-input app-textarea" id="<?= e($id) ?>-r" name="<?= e($field ?? 'reason') ?>" required maxlength="1000" rows="3"></textarea>
      <span class="co-field__help"><?= e(__('common.reason_help')) ?></span>
    </div>
    <div class="modal-footer"><button type="button" class="co-btn co-btn--soft co-btn--sm" data-bs-dismiss="modal"><?= e(__('common.cancel')) ?></button>
      <button type="submit" class="co-btn co-btn--danger co-btn--sm"><?= e($submit ?? __('common.reject')) ?></button></div>
  </form></div>
</div>
