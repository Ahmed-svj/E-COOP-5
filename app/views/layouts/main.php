<?php $lang = Lang::current(); ?><!doctype html>
<html lang="<?= e($lang) ?>" dir="<?= e(Lang::dir()) ?>" data-theme="<?= e(current_theme()) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
<title><?= e(($title ?? __('app.tagline')) . ' · COOP') ?></title>
<meta name="description" content="<?= e(__('home.lead')) ?>">
<link rel="icon" href="<?= asset('img/favicon.png') ?>" type="image/png">
<link rel="apple-touch-icon" href="<?= asset('img/logo-mark.png') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset($lang === 'ar' ? 'vendor/bootstrap/bootstrap.rtl.min.css' : 'vendor/bootstrap/bootstrap.min.css') ?>">
<link rel="stylesheet" href="<?= asset('css/main.css') ?>">
<link rel="stylesheet" href="<?= asset('css/dashboard.css') ?>">
<link rel="stylesheet" href="<?= asset('css/auth.css') ?>">
<?php if ($lang === 'ar'): ?><link rel="stylesheet" href="<?= asset('css/rtl.css') ?>"><?php endif; ?>
</head>
<body class="site-body">
<a class="skip-link" href="#main"><?= e(__('a11y.skip')) ?></a>
<header class="site-header">
  <div class="site-wrap site-header__inner">
    <a class="site-logo" href="<?= url('/') ?>"><img src="<?= asset('img/logo.png') ?>" alt="" width="78" height="44"><span>COOP</span></a>
    <nav class="site-nav" aria-label="<?= e(__('a11y.main_nav')) ?>">
      <a href="<?= url('/') ?>"<?= is_active_path('/') ? ' aria-current="page"' : '' ?>><?= e(__('nav.home')) ?></a>
      <a href="<?= url('/about') ?>"<?= is_active_path('/about') ? ' aria-current="page"' : '' ?>><?= e(__('nav.about')) ?></a>
      <a href="<?= url('/faq') ?>"<?= is_active_path('/faq') ? ' aria-current="page"' : '' ?>><?= e(__('nav.faq')) ?></a>
      <a href="<?= url('/contact') ?>"<?= is_active_path('/contact') ? ' aria-current="page"' : '' ?>><?= e(__('nav.contact')) ?></a>
    </nav>
    <div class="site-header__end">
      <form method="post" action="<?= url('/theme') ?>" class="app-theme-form"><?= csrf_field() ?><button type="submit" class="co-iconbtn app-themebtn app-themebtn--sm" data-theme-toggle aria-label="<?= e(__('theme.toggle')) ?>" title="<?= e(__('theme.toggle')) ?>"><span class="app-themebtn__moon"><?= icon('moon') ?></span><span class="app-themebtn__sun"><?= icon('sun') ?></span></button></form>
      <a class="co-btn co-btn--soft co-btn--sm" href="<?= url('/lang/' . ($lang === 'ar' ? 'en' : 'ar')) ?>" lang="<?= $lang === 'ar' ? 'en' : 'ar' ?>"><?= icon('globe', 'co-ico--sm') ?><?= $lang === 'ar' ? 'English' : 'العربية' ?></a>
      <?php if (Auth::check()): ?>
        <a class="co-btn co-btn--primary co-btn--sm" href="<?= url('/dashboard') ?>"><?= e(__('nav.dashboard')) ?></a>
      <?php else: ?>
        <a class="co-btn co-btn--sm" href="<?= url('/login') ?>"><?= e(__('auth.login')) ?></a>
        <a class="co-btn co-btn--primary co-btn--sm d-none d-sm-inline-flex" href="<?= url('/register/student') ?>"><?= e(__('auth.register')) ?></a>
      <?php endif; ?>
    </div>
  </div>
</header>
<main id="main" class="site-main" tabindex="-1">
  <div class="site-wrap"><?= View::partial('partials/alerts', ['noErrorSummary' => $noErrorSummary ?? null]) ?></div>
  <?= $content ?>
</main>
<footer class="site-footer">
  <div class="site-wrap site-footer__inner">
    <span class="site-footer__brand"><img src="<?= asset('img/logo-mark.png') ?>" alt="" width="28" height="28">© <?= date('Y') ?> COOP · <?= e(__('footer.college')) ?></span>
    <span><a href="<?= url('/about') ?>"><?= e(__('nav.about')) ?></a> · <a href="<?= url('/faq') ?>"><?= e(__('nav.faq')) ?></a> · <a href="<?= url('/privacy') ?>"><?= e(__('nav.privacy')) ?></a> · <a href="<?= url('/contact') ?>"><?= e(__('nav.contact')) ?></a></span>
  </div>
</footer>
<script src="<?= asset('vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= asset('js/main.js') ?>"></script>
<script src="<?= asset('js/theme.js') ?>"></script>
<script src="<?= asset('js/validation.js') ?>"></script>
</body>
</html>
