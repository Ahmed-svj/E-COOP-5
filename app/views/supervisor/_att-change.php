<?php /** Change-status button + dialog for one attendance record. @var array $r (AttendanceID, ApprovalStatus, CodeDate, ReviewComment) @var string $name */
$mid = 'chg-' . (int) $r['AttendanceID']; ?>
<button class="co-btn co-btn--soft co-btn--sm" type="button" data-bs-toggle="modal" data-bs-target="#<?= $mid ?>"><?= e(__('att.change')) ?></button>
<div class="modal fade" id="<?= $mid ?>" tabindex="-1" aria-labelledby="<?= $mid ?>-t" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><form class="modal-content app-modal" method="post" action="<?= url('/supervisor/attendance/' . $r['AttendanceID'] . '/status') ?>" data-att-change>
    <?= csrf_field() ?>
    <div class="modal-header"><h2 class="co-card__title" id="<?= $mid ?>-t"><?= e(__('att.change_title', ['name' => $name, 'date' => fmt_date($r['CodeDate'])])) ?></h2>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= e(__('a11y.close')) ?>"></button></div>
    <div class="modal-body app-form">
      <p class="co-caption m-0"><?= e(__('att.current_status')) ?>: <?= status_badge($r['ApprovalStatus']) ?></p>
      <fieldset class="co-field"><legend class="set-label"><?= e(__('att.change_to')) ?></legend>
        <div class="co-chips co-chips--mint">
          <?php foreach (['Approved', 'Rejected'] as $st): ?>
            <label class="co-chip app-radio-chip"><input type="radio" name="status" value="<?= $st ?>" required<?= $r['ApprovalStatus'] !== $st && $st === ($r['ApprovalStatus'] === 'Approved' ? 'Rejected' : 'Approved') ? ' checked' : '' ?>><?= e(__('status.' . $st)) ?></label>
          <?php endforeach; ?>
        </div></fieldset>
      <div class="co-field"><label for="<?= $mid ?>-c"><?= e(__('att.comment')) ?></label>
        <textarea class="co-input app-textarea" id="<?= $mid ?>-c" name="comment" rows="3" maxlength="1000"><?= e($r['ReviewComment'] ?? '') ?></textarea>
        <span class="co-field__help"><?= e(__('att.change_help')) ?></span></div>
    </div>
    <div class="modal-footer"><button type="button" class="co-btn co-btn--soft co-btn--sm" data-bs-dismiss="modal"><?= e(__('common.cancel')) ?></button>
      <button type="submit" class="co-btn co-btn--primary co-btn--sm"><?= e(__('att.save_change')) ?></button></div>
  </form></div>
</div>
