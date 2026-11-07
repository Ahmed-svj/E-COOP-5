<?php $title = $o ? __('opp.edit') : __('opp.new'); $noErrorSummary = true; ?>
<?= View::partial('partials/page-head', ['heading' => $title, 'sub' => __('opp.form_sub')]) ?>
<form class="co-card app-form app-form--narrow" method="post" action="<?= url($o ? '/company/opportunities/' . $o['OpportunityID'] : '/company/opportunities') ?>" data-validate novalidate>
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-12 co-field<?= invalid('title') ?>"><label for="title"><?= e(__('opp.title')) ?></label><input class="co-input" id="title" name="title" value="<?= e(old('title', $o['Title'] ?? '')) ?>" required minlength="3" maxlength="190"><?= error_for('title') ?></div>
    <div class="col-md-6 co-field<?= invalid('major') ?>"><label for="major"><?= e(__('opp.major')) ?></label><select class="co-input" id="major" name="major" required><option value=""><?= e(__('common.choose')) ?></option><?php foreach (majors() as $m): ?><option<?= old('major', $o['MajorRequirements'] ?? '') === $m ? ' selected' : '' ?>><?= e($m) ?></option><?php endforeach; ?></select><?= error_for('major') ?></div>
    <div class="col-md-3 co-field<?= invalid('city') ?>"><label for="city"><?= e(__('common.city')) ?></label><select class="co-input" id="city" name="city" required><?php foreach (cities() as $ct): ?><option<?= old('city', $o['City'] ?? Auth::profile()['City']) === $ct ? ' selected' : '' ?>><?= e($ct) ?></option><?php endforeach; ?></select><?= error_for('city') ?></div>
    <div class="col-md-3 co-field<?= invalid('capacity') ?>"><label for="capacity"><?= e(__('opp.capacity')) ?></label><input class="co-input" id="capacity" name="capacity" type="number" min="1" max="500" dir="ltr" value="<?= e(old('capacity', $o['Capacity'] ?? '1')) ?>" required><?= error_for('capacity') ?></div>
    <div class="col-12 co-field<?= invalid('description') ?>"><label for="description"><?= e(__('opp.description')) ?></label><textarea class="co-input app-textarea" id="description" name="description" rows="7" required minlength="20" maxlength="5000"><?= e(old('description', $o['Description'] ?? '')) ?></textarea><?= error_for('description') ?: '<span class="co-field__help">' . e(__('opp.description_help')) . '</span>' ?></div>
  </div>
  <?php if ($o && $o['Status'] === 'Approved'): ?><div class="co-alert co-alert--amber"><?= icon('info') ?><div><?= e(__('opp.edit_reapproval')) ?></div></div><?php endif; ?>
  <div class="d-flex gap-2"><button class="co-btn co-btn--primary" type="submit"><?= e(__('opp.submit')) ?></button><a class="co-btn co-btn--soft" href="<?= url('/company/opportunities') ?>"><?= e(__('common.cancel')) ?></a></div>
</form>
