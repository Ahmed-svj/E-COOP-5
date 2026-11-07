<?php
$title = __('nav.settings');
$noErrorSummary = true;
$u = Auth::user();
$role = $u['Role'];
$p = $profile ?? [];
$lang = Lang::current();
$theme = current_theme();
$scripts = ['settings.js'];
$notifyRows = [
    'notify_attendance'   => ['settings.n_attendance', 'settings.n_attendance_d'],
    'notify_applications' => ['settings.n_applications', 'settings.n_applications_d'],
    'notify_reports'      => ['settings.n_reports', 'settings.n_reports_d'],
    'weekly_summary'      => ['settings.n_weekly', 'settings.n_weekly_d'],
];
$cvName = $role === 'Student' && !empty($p['CVFilePath']) ? 'CV_' . preg_replace('/\s+/', '_', $u['FullName']) . '.' . pathinfo($p['CVFilePath'], PATHINFO_EXTENSION) : null;
?>
<?= View::partial('partials/page-head', ['heading' => __('nav.settings'), 'sub' => __('settings.sub')]) ?>
<section class="co-card set-me">
  <button type="button" class="app-avatar-btn app-avatar-btn--lg" data-avatar-pick aria-label="<?= e(__('settings.change_photo')) ?>">
    <?= avatar_html($u, 'app-avatar-lg') ?><span class="app-avatar-btn__cam" aria-hidden="true"><?= icon('camera', 'co-ico--sm') ?></span>
  </button>
  <div class="set-me__who">
    <strong class="co-card__title"><bdi><?= e($u['FullName']) ?></bdi></strong>
    <span class="co-caption"><?= e(__('role.' . $role)) ?> · <bdi dir="ltr"><?= e($u['Email']) ?></bdi></span>
    <span class="co-caption"><?= e(__('settings.avatar_help')) ?></span>
  </div>
  <button type="button" class="co-btn co-btn--soft co-btn--sm set-me__btn" data-avatar-pick><?= icon('camera', 'co-ico--sm') ?><?= e(__('settings.change_photo')) ?></button>
</section>

<div class="set-grid">
  <div class="set-col">
    <form class="co-card app-form set-card" method="post" action="<?= url('/settings/profile') ?>" enctype="multipart/form-data" data-validate novalidate data-original-email="<?= e($u['Email']) ?>">
      <?= csrf_field() ?>
      <div class="co-card__head"><span class="co-tile"><?= icon('user') ?></span><h2 class="co-card__title set-h"><?= e(__('settings.profile')) ?></h2></div>
      <div class="co-field<?= invalid('full_name') ?>"><label for="full_name"><?= e(__($role === 'Company' ? 'auth.contact_person' : 'auth.full_name')) ?></label>
        <input class="co-input" id="full_name" name="full_name" value="<?= e(old('full_name', $u['FullName'])) ?>" required minlength="3" maxlength="150" autocomplete="name"><?= error_for('full_name') ?></div>
      <div class="co-field<?= invalid('email') ?>"><label for="email"><?= e(__($role === 'Student' ? 'settings.uni_email' : 'auth.email')) ?></label>
        <input class="co-input" id="email" name="email" type="email" dir="ltr" value="<?= e(old('email', $u['Email'])) ?>" required autocomplete="email"<?= $role === 'Student' ? ' data-domain="' . e(implode(',', student_domains())) . '"' : '' ?>><?= error_for('email') ?></div>
      <div class="co-field set-confirm<?= invalid('current_password') ?>"<?= isset(errors()['current_password']) ? '' : ' hidden' ?> data-email-confirm>
        <label for="current_password"><?= e(__('settings.current_password')) ?></label>
        <input class="co-input" id="current_password" name="current_password" type="password" dir="ltr" autocomplete="current-password">
        <?= error_for('current_password') ?: '<span class="co-field__help">' . e(__('settings.email_needs_password')) . '</span>' ?></div>
      <div class="set-row">
        <div class="co-field<?= invalid('phone') ?>"><label for="phone"><?= e(__('settings.phone')) ?></label>
          <input class="co-input" id="phone" name="phone" dir="ltr" inputmode="numeric" value="<?= e(old('phone', $u['Phone'])) ?>" required data-rule="phone" autocomplete="tel"><?= error_for('phone') ?: '<span class="co-field__help">05XXXXXXXX</span>' ?></div>
        <?php if ($role === 'Student'): ?>
          <div class="co-field<?= invalid('gpa') ?>"><label for="gpa"><?= e(__('profile.gpa')) ?></label>
            <input class="co-input" id="gpa" name="gpa" type="number" step="0.01" min="0" max="5" dir="ltr" value="<?= e(old('gpa', $p['GPA'] ?? '')) ?>" required><?= error_for('gpa') ?: '<span class="co-field__help">0 – 5</span>' ?></div>
        <?php elseif ($role === 'Company'): ?>
          <div class="co-field<?= invalid('city') ?>"><label for="city"><?= e(__('common.city')) ?></label>
            <select class="co-input" id="city" name="city" required><?php foreach (cities() as $c): ?><option<?= old('city', $p['City'] ?? '') === $c ? ' selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select><?= error_for('city') ?></div>
        <?php endif; ?>
      </div>
      <?php if ($role === 'Student'): ?>
        <div class="co-field<?= invalid('major') ?>"><label for="major"><?= e(__('auth.major')) ?></label>
          <select class="co-input" id="major" name="major" required><?php foreach (majors() as $m): ?><option<?= old('major', $p['Major'] ?? '') === $m ? ' selected' : '' ?>><?= e($m) ?></option><?php endforeach; ?></select><?= error_for('major') ?></div>
        <div class="co-field<?= invalid('cv') ?>"><span class="set-label"><?= e(__('profile.cv')) ?></span>
          <?php if ($cvName): ?>
            <div class="set-file"><a href="<?= url('/files/cv/' . $p['StudentID']) ?>" class="set-file__name"><?= icon('doc', 'co-ico--sm') ?><bdi dir="ltr"><?= e($cvName) ?></bdi></a>
              <button type="submit" form="cv-remove" class="set-file__remove"><?= e(__('settings.remove')) ?></button></div>
          <?php endif; ?>
          <label class="set-upload" for="cv"><?= icon('upload', 'co-ico--sm') ?><span data-file-label><?= e($cvName ? __('settings.replace_cv') : __('settings.upload_cv')) ?></span></label>
          <input class="visually-hidden" id="cv" name="cv" type="file" accept=".pdf,.docx" data-max="<?= config('upload_max_bytes') ?>">
          <?= error_for('cv') ?: '<span class="co-field__help">' . e(__('upload.help')) . '</span>' ?></div>
      <?php elseif ($role === 'Company'): ?>
        <div class="co-field<?= invalid('company_name') ?>"><label for="company_name"><?= e(__('auth.company_name')) ?></label>
          <input class="co-input" id="company_name" name="company_name" value="<?= e(old('company_name', $p['CompanyName'] ?? '')) ?>" required maxlength="190"><?= error_for('company_name') ?></div>
      <?php elseif ($role === 'Supervisor'): ?>
        <div class="co-field<?= invalid('department') ?>"><label for="department"><?= e(__('admin.department')) ?></label>
          <input class="co-input" id="department" name="department" value="<?= e(old('department', $p['Department'] ?? '')) ?>" required maxlength="150"><?= error_for('department') ?></div>
      <?php endif; ?>
      <div class="set-actions"><button class="co-btn co-btn--primary" type="submit"><?= e(__('settings.save_profile')) ?></button></div>
    </form>
    <?php if ($cvName): ?><form id="cv-remove" method="post" action="<?= url('/settings/cv/remove') ?>" data-confirm="<?= e(__('settings.cv_remove_confirm')) ?>"><?= csrf_field() ?></form><?php endif; ?>
  </div>

  <div class="set-col">
    <form class="co-card set-card" method="post" action="<?= url('/settings/notifications') ?>" data-autosave>
      <?= csrf_field() ?>
      <div class="co-card__head"><span class="co-tile co-tile--mint"><?= icon('bell') ?></span><h2 class="co-card__title set-h"><?= e(__('nav.notifications')) ?></h2></div>
      <div class="co-list">
      <?php foreach ($notifyRows as $key => [$t, $d]): ?>
        <label class="co-row"><span class="co-row__text"><span class="co-row__title"><?= e(__($t)) ?></span><span class="co-row__sub app-wrap"><?= e(__($d)) ?></span></span>
          <input type="checkbox" role="switch" class="co-toggle" name="<?= e($key) ?>" value="1"<?= $prefs[$key] ? ' checked' : '' ?>></label>
      <?php endforeach; ?>
      </div>
      <noscript><button class="co-btn co-btn--sm" type="submit"><?= e(__('common.save')) ?></button></noscript>
    </form>

    <div class="co-card set-card">
      <div class="co-card__head"><span class="co-tile co-tile--lavender"><?= icon('sun') ?></span><h2 class="co-card__title set-h"><?= e(__('settings.appearance')) ?></h2></div>
      <div class="set-seg-group"><span class="set-label" id="lang-l"><?= e(__('settings.language')) ?></span>
        <div class="co-chips co-chips--mint" role="group" aria-labelledby="lang-l">
          <a class="co-chip" href="<?= url('/lang/en') ?>" lang="en" aria-pressed="<?= $lang === 'en' ? 'true' : 'false' ?>"<?= $lang === 'en' ? ' aria-current="true"' : '' ?>><?= icon('globe', 'co-ico--sm') ?>English</a>
          <a class="co-chip" href="<?= url('/lang/ar') ?>" lang="ar" aria-pressed="<?= $lang === 'ar' ? 'true' : 'false' ?>"<?= $lang === 'ar' ? ' aria-current="true"' : '' ?>><?= icon('globe', 'co-ico--sm') ?>العربية</a>
        </div></div>
      <div class="set-seg-group"><span class="set-label" id="theme-l"><?= e(__('settings.theme')) ?></span>
        <form class="co-chips co-chips--mint set-theme-form" role="group" aria-labelledby="theme-l" method="post" action="<?= url('/theme') ?>">
          <?= csrf_field() ?>
          <button class="co-chip" type="submit" name="theme" value="light" aria-pressed="<?= $theme === 'light' ? 'true' : 'false' ?>"<?= $theme === 'light' ? ' aria-current="true"' : '' ?>><?= icon('sun', 'co-ico--sm') ?><?= e(__('settings.light')) ?></button>
          <button class="co-chip" type="submit" name="theme" value="dark" aria-pressed="<?= $theme === 'dark' ? 'true' : 'false' ?>"<?= $theme === 'dark' ? ' aria-current="true"' : '' ?>><?= icon('moon', 'co-ico--sm') ?><?= e(__('settings.dark_short')) ?></button>
        </form></div>
    </div>

    <form class="co-card app-form set-card" method="post" action="<?= url('/settings/password') ?>" data-validate novalidate>
      <?= csrf_field() ?>
      <div class="co-card__head"><span class="co-tile"><?= icon('lock') ?></span><h2 class="co-card__title set-h"><?= e(__('settings.security')) ?></h2></div>
      <div class="co-field<?= invalid('password_current') ?>"><label for="cp"><?= e(__('settings.current_password')) ?></label><input class="co-input" id="cp" name="current_password" type="password" dir="ltr" required autocomplete="current-password"><?= error_for('password_current') ?></div>
      <div class="set-row">
        <div class="co-field<?= invalid('password') ?>"><label for="password"><?= e(__('reset.new')) ?></label><input class="co-input" id="password" name="password" type="password" dir="ltr" required data-rule="password" autocomplete="new-password"><?= error_for('password') ?></div>
        <div class="co-field"><label for="password_confirmation"><?= e(__('auth.password_confirm')) ?></label><input class="co-input" id="password_confirmation" name="password_confirmation" type="password" dir="ltr" required data-match="password" autocomplete="new-password"></div>
      </div>
      <div class="set-actions"><button class="co-btn co-btn--soft" type="submit"><?= e(__('settings.change_password')) ?></button></div>
    </form>
  </div>
</div>

<section class="co-card mt-4">
  <div class="co-card__head"><span class="co-tile co-tile--lavender"><?= icon('info') ?></span><h2 class="co-card__title set-h"><?= e(__('settings.help')) ?></h2></div>
  <div class="set-help">
    <a class="co-row app-row-link" href="<?= url('/faq') ?>"><span class="co-tile co-tile--lavender"><?= icon('info') ?></span><span class="co-row__text"><span class="co-row__title"><?= e(__('nav.faq')) ?></span><span class="co-row__sub"><?= e(__('settings.help_faq')) ?></span></span><?= icon('chev', 'app-flip') ?></a>
    <a class="co-row app-row-link" href="<?= url('/messages') ?>"><span class="co-tile co-tile--mint"><?= icon('chat') ?></span><span class="co-row__text"><span class="co-row__title"><?= e(__('settings.help_office')) ?></span><span class="co-row__sub"><?= e(__('settings.help_office_d')) ?></span></span><?= icon('chev', 'app-flip') ?></a>
    <a class="co-row app-row-link" href="<?= url('/privacy') ?>"><span class="co-tile"><?= icon('shield') ?></span><span class="co-row__text"><span class="co-row__title"><?= e(__('nav.privacy')) ?></span><span class="co-row__sub"><?= e(__('settings.help_privacy')) ?></span></span><?= icon('chev', 'app-flip') ?></a>
  </div>
</section>
