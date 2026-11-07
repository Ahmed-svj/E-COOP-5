<?php $title = __('app.tagline'); ?>
<section class="site-wrap hero">
  <div class="hero__text">
    <span class="co-badge co-badge--mint"><?= e(__('home.badge')) ?></span>
    <h1 class="hero__title"><?= e(__('home.title')) ?></h1>
    <p class="hero__lead"><?= e(__('home.lead')) ?></p>
    <div class="hero__actions">
      <a class="co-btn co-btn--primary" href="<?= url('/register/student') ?>"><?= icon('cap', 'co-ico--sm') ?><?= e(__('home.cta_student')) ?></a>
      <a class="co-btn" href="<?= url('/register/company') ?>"><?= icon('building', 'co-ico--sm') ?><?= e(__('home.cta_company')) ?></a>
      <a class="co-btn co-btn--soft" href="<?= url('/register/supervisor') ?>"><?= icon('users', 'co-ico--sm') ?><?= e(__('home.cta_supervisor')) ?></a>
    </div>
    <dl class="hero__stats">
      <div><dt><?= e(__('home.stat_companies')) ?></dt><dd><?= (int) $stats['companies'] ?></dd></div>
      <div><dt><?= e(__('home.stat_opps')) ?></dt><dd><?= (int) $stats['opportunities'] ?></dd></div>
      <div><dt><?= e(__('home.stat_placed')) ?></dt><dd><?= (int) $stats['placed'] ?></dd></div>
    </dl>
  </div>
  <div class="hero__art" aria-hidden="true">
    <div class="co-card hero__card hero__card--a">
      <div class="co-card__head"><span class="co-tile"><?= icon('bolt') ?></span><span class="co-card__title"><?= e(__('dash.att_week')) ?></span></div>
      <div class="co-metric">4 <small>/ 5</small></div>
      <div class="co-bars" style="height:80px"><div class="co-bar"><i style="height:60%"></i><b><?= e(__('day.Sun')) ?></b></div><div class="co-bar co-bar--lav"><i style="height:78%"></i><b><?= e(__('day.Mon')) ?></b></div><div class="co-bar"><i style="height:95%"></i><b><?= e(__('day.Tue')) ?></b></div><div class="co-bar co-bar--empty"><i style="height:8%"></i><b><?= e(__('day.Wed')) ?></b></div><div class="co-bar"><i style="height:70%"></i><b><?= e(__('day.Thu')) ?></b></div></div>
    </div>
    <div class="co-card hero__card hero__card--b">
      <div class="co-card__head"><span class="co-tile co-tile--mint"><?= icon('key') ?></span><span class="co-card__title"><?= e(__('dash.today_code')) ?></span></div>
      <div class="co-code co-code--display"><span>4</span><span>8</span><span>2</span><span>9</span><span>1</span><span>3</span></div>
    </div>
    <div class="co-card hero__card hero__card--c">
      <div class="co-row"><span class="co-row__media co-row__media--mint">NS</span><div class="co-row__text"><span class="co-row__title"><?= e(__('home.demo_role')) ?></span><span class="co-row__sub">Nova Systems · Jubail</span></div><?= status_badge('Accepted') ?></div>
    </div>
  </div>
</section>

<section class="site-wrap steps" aria-labelledby="how">
  <h2 id="how" class="section-title"><?= e(__('home.how')) ?></h2>
  <ol class="steps__list">
    <?php foreach ([['building', 'home.step1'], ['brief', 'home.step2'], ['key', 'home.step3'], ['star', 'home.step4']] as $i => [$ic, $k]): ?>
      <li class="co-card"><span class="co-tile<?= $i % 2 ? ' co-tile--lavender' : ' co-tile--mint' ?>"><?= icon($ic) ?></span>
        <h3 class="co-card__title"><?= e(__($k . '_t')) ?></h3><p class="co-caption steps__text"><?= e(__($k)) ?></p></li>
    <?php endforeach; ?>
  </ol>
</section>

<?php if ($latest): ?>
<section class="site-wrap" aria-labelledby="latest">
  <h2 id="latest" class="section-title"><?= e(__('home.latest')) ?></h2>
  <div class="row g-3">
    <?php foreach ($latest as $o): ?>
      <div class="col-md-4"><article class="co-card h-100">
        <div class="co-card__head"><span class="co-row__media co-row__media--mint app-media-sm"><?= e(initials($o['CompanyName'])) ?></span>
          <div class="co-row__text"><h3 class="co-row__title"><?= e($o['Title']) ?></h3><span class="co-row__sub"><?= e($o['CompanyName']) ?> · <?= e($o['City']) ?></span></div></div>
        <p class="co-caption steps__text"><?= e(mb_strimwidth($o['Description'], 0, 130, '…')) ?></p>
        <div class="app-tags"><span class="co-badge co-badge--lavender"><?= e($o['MajorRequirements']) ?></span><span class="co-badge"><?= e(__('opp.seats', ['n' => $o['Capacity'] - $o['AcceptedCount']])) ?></span></div>
      </article></div>
    <?php endforeach; ?>
  </div>
  <p class="mt-3"><a class="co-btn co-btn--soft" href="<?= url('/login') ?>"><?= e(__('home.sign_in_to_apply')) ?><?= icon('chev', 'co-ico--sm app-flip') ?></a></p>
</section>
<?php endif; ?>
