<form class="co-card app-form app-form--narrow" method="post" action="<?= url($action) ?>" data-validate novalidate>
  <?= csrf_field() ?>
  <div class="co-field<?= invalid('score') ?>"><label for="score"><?= e(__('eval.score_label')) ?></label>
    <div class="app-score"><input type="range" min="0" max="100" step="1" value="<?= e(old('score', '80')) ?>" id="score-range" aria-hidden="true" tabindex="-1" data-sync="score">
    <input class="co-input app-score__num" id="score" name="score" type="number" min="0" max="100" dir="ltr" required value="<?= e(old('score', '80')) ?>"></div><?= error_for('score') ?: '<span class="co-field__help">' . e(__('eval.score_help')) . '</span>' ?></div>
  <div class="co-field<?= invalid('comments') ?>"><label for="comments"><?= e(__('eval.comments')) ?></label><textarea class="co-input app-textarea" id="comments" name="comments" rows="5" maxlength="3000"><?= e(old('comments')) ?></textarea><?= error_for('comments') ?></div>
  <div class="co-alert co-alert--amber"><?= icon('info') ?><div><?= e(__('eval.final')) ?></div></div>
  <div class="d-flex gap-2"><button class="co-btn co-btn--primary" type="submit"><?= e(__('eval.submit')) ?></button><a class="co-btn co-btn--soft" href="<?= url($cancel) ?>"><?= e(__('common.cancel')) ?></a></div>
</form>
