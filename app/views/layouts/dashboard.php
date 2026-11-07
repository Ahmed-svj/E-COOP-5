<?php
$user = Auth::user();
$role = $user['Role'];
$lang = Lang::current();
$dark = current_theme() === 'dark';
$unread = Notification::unreadCount((int) $user['UserID']);
$profile = Auth::profile();
$location = match ($role) {
    'Student'    => $profile['Major'] ?? '',
    'Company'    => ($profile['CompanyName'] ?? '') . ' · ' . ($profile['City'] ?? ''),
    'Supervisor' => $profile['Department'] ?? '',
    'Admin'      => __('role.Admin'),
};
$searchAction = match ($role) {
    'Student' => '/student/opportunities', 'Company' => '/company/applicants', 'Admin' => '/admin/users', default => '/supervisor/students',
};
?><!doctype html>
<html lang="<?= e($lang) ?>" dir="<?= e(Lang::dir()) ?>" data-theme="<?= $dark ? 'dark' : 'light' ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
<title><?= e(($title ?? __('app.tagline')) . ' · COOP') ?></title>
<link rel="icon" href="<?= asset('img/favicon.png') ?>" type="image/png">
<link rel="apple-touch-icon" href="<?= asset('img/logo-mark.png') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset($lang === 'ar' ? 'vendor/bootstrap/bootstrap.rtl.min.css' : 'vendor/bootstrap/bootstrap.min.css') ?>">
<link rel="stylesheet" href="<?= asset('css/main.css') ?>">
<link rel="stylesheet" href="<?= asset('css/dashboard.css') ?>">
<?php if ($lang === 'ar'): ?><link rel="stylesheet" href="<?= asset('css/rtl.css') ?>"><?php endif; ?>
</head>
<body class="app-body">
<a class="skip-link" href="#main"><?= e(__('a11y.skip')) ?></a>
<div class="app-canvas">
<div class="co-shell app-shell">
  <nav class="co-rail app-rail" aria-label="<?= e(__('a11y.main_nav')) ?>">
    <a href="<?= url('/dashboard') ?>" class="app-logo" aria-label="COOP"><img src="<?= asset('img/logo-mark.png') ?>" alt="" width="44" height="44"></a>
    <?php foreach (nav_items($role) as [$path, $label, $ico]): ?>
      <a href="<?= url($path) ?>" title="<?= e(__($label)) ?>" aria-label="<?= e(__($label)) ?>"<?= is_active_path($path) ? ' aria-current="page"' : '' ?>><?= icon($ico) ?><span class="app-rail__label"><?= e(__($label)) ?></span></a>
    <?php endforeach; ?>
    <a href="<?= url('/settings') ?>" class="app-rail__end" title="<?= e(__('nav.settings')) ?>" aria-label="<?= e(__('nav.settings')) ?>"<?= is_active_path('/settings') ? ' aria-current="page"' : '' ?>><?= icon('settings') ?><span class="app-rail__label"><?= e(__('nav.settings')) ?></span></a>
  </nav>

  <div class="app-main">
    <header class="co-topbar app-topbar">
      <div class="co-head">
        <span class="co-head__title app-brand"><img class="app-brand__logo" src="<?= asset('img/logo.png') ?>" alt="" width="62" height="35">COOP</span>
        <span class="co-head__loc"><?= icon('pin', 'co-ico--sm') ?><?= e($location) ?></span>
      </div>
      <form class="co-search app-search" role="search" action="<?= url($searchAction) ?>" method="get">
        <?= icon('search') ?>
        <input type="search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="<?= e(__('search.' . $role)) ?>" aria-label="<?= e(__('search.label')) ?>">
        <button type="submit" class="app-search__btn" aria-label="<?= e(__('search.label')) ?>"><?= icon('sliders') ?></button>
      </form>
      <div class="app-topbar__end">
        <form method="post" action="<?= url('/theme') ?>" class="app-theme-form"><?= csrf_field() ?><button type="submit" class="co-iconbtn app-themebtn" data-theme-toggle aria-label="<?= e(__('theme.toggle')) ?>" title="<?= e(__('theme.toggle')) ?>"><span class="app-themebtn__moon"><?= icon('moon') ?></span><span class="app-themebtn__sun"><?= icon('sun') ?></span></button></form>
        <a class="co-btn co-btn--soft co-btn--sm app-langbtn" href="<?= url('/lang/' . ($lang === 'ar' ? 'en' : 'ar')) ?>" lang="<?= $lang === 'ar' ? 'en' : 'ar' ?>" aria-label="<?= $lang === 'ar' ? 'Switch to English' : 'التبديل إلى العربية' ?>"><?= icon('globe', 'co-ico--sm') ?><span><?= $lang === 'ar' ? 'English' : 'العربية' ?></span></a>
        <a class="co-iconbtn<?= $unread ? ' co-iconbtn--dot' : '' ?>" id="notif-bell" href="<?= url('/notifications') ?>"
           aria-label="<?= e(__('nav.notifications')) ?><?= $unread ? ', ' . $unread . ' ' . e(__('notif.unread')) : '' ?>"><?= icon('bell') ?></a>
        <form id="avatar-form" class="app-avatar-form" method="post" action="<?= url('/settings/avatar') ?>" enctype="multipart/form-data">
          <?= csrf_field() ?>
          <input type="file" id="avatar-input" name="avatar" accept="image/jpeg,image/png,image/webp" class="visually-hidden" tabindex="-1" data-max="5242880">
        </form>
        <div class="dropdown co-userchip app-userchip">
          <button type="button" class="app-avatar-btn" data-avatar-pick title="<?= e(__('settings.change_photo')) ?>" aria-label="<?= e(__('settings.change_photo')) ?>">
            <?= avatar_html($user) ?><span class="app-avatar-btn__cam" aria-hidden="true"><?= icon('camera', 'co-ico--sm') ?></span>
          </button>
          <button class="app-userchip__toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="<?= e(__('a11y.account_menu')) ?>">
            <bdi class="app-userchip__name"><?= e($user['FullName']) ?></bdi><?= icon('down', 'co-ico--sm') ?>
          </button>
          <ul class="dropdown-menu dropdown-menu-end app-menu">
            <li class="app-menu__head"><strong><?= e($user['FullName']) ?></strong><span><?= e(__('role.' . $role)) ?> · <?= e($user['Email']) ?></span></li>
            <?php if ($role === 'Student'): ?><li><a class="dropdown-item" href="<?= url('/student/profile') ?>"><?= icon('user', 'co-ico--sm') ?><?= e(__('nav.profile')) ?></a></li><?php endif; ?>
            <?php if ($role === 'Company'): ?><li><a class="dropdown-item" href="<?= url('/company/profile') ?>"><?= icon('building', 'co-ico--sm') ?><?= e(__('nav.company_profile')) ?></a></li><?php endif; ?>
            <li><button class="dropdown-item" type="button" data-avatar-pick><?= icon('camera', 'co-ico--sm') ?><?= e(__('settings.change_photo')) ?></button></li>
            <li><a class="dropdown-item" href="<?= url('/settings') ?>"><?= icon('settings', 'co-ico--sm') ?><?= e(__('nav.settings')) ?></a></li>
            <li><a class="dropdown-item" href="<?= url('/lang/' . ($lang === 'ar' ? 'en' : 'ar')) ?>"><?= icon('globe', 'co-ico--sm') ?><?= $lang === 'ar' ? 'English' : 'العربية' ?></a></li>
            <li><hr class="dropdown-divider"></li>
            <li><form method="post" action="<?= url('/logout') ?>"><?= csrf_field() ?><button class="dropdown-item" type="submit"><?= icon('logout', 'co-ico--sm') ?><?= e(__('nav.logout')) ?></button></form></li>
          </ul>
        </div>
      </div>
    </header>

    <main id="main" class="app-content" tabindex="-1">
      <?= View::partial('partials/alerts', ['noErrorSummary' => $noErrorSummary ?? null]) ?>
      <?= $content ?>
    </main>
  </div>
</div>
</div>
<div class="app-toasts" id="toasts" aria-live="polite"></div>
<script src="<?= asset('vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= asset('js/main.js') ?>"></script>
<script src="<?= asset('js/theme.js') ?>"></script>
<script src="<?= asset('js/validation.js') ?>"></script>
<script src="<?= asset('js/notifications.js') ?>"></script>
<script src="<?= asset('js/avatar.js') ?>"></script>
<?php foreach ($scripts ?? [] as $s): ?><script src="<?= asset(str_contains($s, '/') ? $s : 'js/' . $s) ?>"></script><?php endforeach; ?>
</body>
</html>
