<?php $title = __('nav.company_profile'); $noErrorSummary = true; ?>
<?= View::partial('partials/page-head', ['heading' => __('nav.company_profile'), 'sub' => __('company.profile_sub')]) ?>
<div class="row g-3"><div class="col-lg-8">
<form class="co-card app-form" method="post" action="<?= url('/company/profile') ?>" data-validate novalidate>
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-md-6 co-field<?= invalid('company_name') ?>"><label for="company_name"><?= e(__('auth.company_name')) ?></label><input class="co-input" id="company_name" name="company_name" value="<?= e(old('company_name', $c['CompanyName'])) ?>" required maxlength="190"><?= error_for('company_name') ?></div>
    <div class="col-md-6 co-field<?= invalid('city') ?>"><label for="city"><?= e(__('common.city')) ?></label><select class="co-input" id="city" name="city"><?php foreach (cities() as $ct): ?><option<?= old('city', $c['City']) === $ct ? ' selected' : '' ?>><?= e($ct) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-6 co-field<?= invalid('full_name') ?>"><label for="full_name"><?= e(__('auth.contact_person')) ?></label><input class="co-input" id="full_name" name="full_name" value="<?= e(old('full_name', $c['FullName'])) ?>" required maxlength="150"><?= error_for('full_name') ?></div>
    <div class="col-md-6 co-field<?= invalid('phone') ?>"><label for="phone"><?= e(__('auth.phone')) ?></label><input class="co-input" id="phone" name="phone" dir="ltr" value="<?= e(old('phone', $c['Phone'])) ?>" required data-rule="phone"><?= error_for('phone') ?></div>
    <div class="col-12 co-field"><label for="em"><?= e(__('auth.work_email')) ?></label><input class="co-input" id="em" value="<?= e($c['Email']) ?>" dir="ltr" disabled></div>
  </div>
  <div><button class="co-btn co-btn--primary" type="submit"><?= e(__('common.save')) ?></button></div>
</form></div>
<div class="col-lg-4"><section class="co-card"><div class="co-card__head"><span class="co-tile"><?= icon('shield') ?></span><h2 class="co-card__title"><?= e(__('company.approval')) ?></h2></div>
  <div><?= status_badge($c['ApprovalStatus']) ?></div><p class="co-caption"><?= e(__('company.registered', ['date' => fmt_date($c['CreatedAt'])])) ?></p></section></div></div>
