<?php $title = __('nav.home'); ?>
<p class="co-greet"><?= e(greeting()) ?></p>
<p class="co-name"><bdi><?= e($c['CompanyName']) ?></bdi></p>

<?php if ($c['ApprovalStatus'] !== 'Approved'): ?>
  <div class="co-alert <?= $c['ApprovalStatus'] === 'Rejected' ? 'co-alert--red' : 'co-alert--amber' ?> mt-3" role="status"><?= icon('info') ?>
    <div><strong><?= e(__('company.status_' . $c['ApprovalStatus'])) ?></strong><?= e($c['ApprovalStatus'] === 'Rejected' ? ($c['RejectionReason'] ?? '') : __('company.pending_d')) ?></div></div>
<?php endif; ?>

<div class="app-kpis">
  <?php foreach ([['open', 'brief', 'company.k_open', ''], ['pending', 'users', 'company.k_pending', 'lavender'], ['trainees', 'cap', 'company.k_trainees', 'mint'], ['week_apps', 'bolt', 'company.k_week', '']] as [$k, $ic, $lbl, $tone]): ?>
    <section class="co-card"><div class="co-card__head"><span class="co-tile<?= $tone ? ' co-tile--' . $tone : '' ?>"><?= icon($ic) ?></span><h2 class="co-card__title"><?= e(__($lbl)) ?></h2></div><div class="co-metric"><?= (int) $stats[$k] ?></div></section>
  <?php endforeach; ?>
</div>

<div class="co-grid app-grid">
  <div class="co-stack">
    <section class="co-card">
      <div class="co-card__head"><span class="co-tile co-tile--mint"><?= icon('key') ?></span><h2 class="co-card__title"><?= e(__('dash.today_code')) ?></h2><a href="<?= url('/company/daily-code') ?>" aria-label="<?= e(__('nav.daily_code')) ?>"><?= icon('chev', 'app-flip') ?></a></div>
      <?php if (!$placements): ?><p class="co-caption app-body-text"><?= e(__('code.no_placements')) ?></p>
      <?php else: foreach ($placements as $p): ?>
        <div class="app-codeblock"><span class="co-row__title"><?= e($p['Title']) ?></span>
          <?php if ($p['TodayCode']): ?>
            <div class="co-code co-code--display" aria-label="<?= e(__('att.code_label')) ?> <?= e($p['TodayCode']) ?>"><?php foreach (str_split($p['TodayCode']) as $d): ?><span><?= e($d) ?></span><?php endforeach; ?></div>
            <span class="co-caption"><?= e(__('code.expires')) ?> · <?= e(__('code.checked', ['n' => $p['CheckedIn'], 't' => $p['Trainees']])) ?></span>
          <?php else: ?>
            <form method="post" action="<?= url('/company/daily-code') ?>"><?= csrf_field() ?><input type="hidden" name="placement_id" value="<?= (int) $p['PlacementID'] ?>"><button class="co-btn co-btn--primary co-btn--sm"><?= icon('key', 'co-ico--sm') ?><?= e(__('code.generate')) ?></button></form>
          <?php endif; ?></div>
      <?php endforeach; endif; ?>
    </section>
  </div>
  <div class="co-stack">
    <section class="co-card">
      <div class="co-card__head"><span class="co-tile"><?= icon('users') ?></span><h2 class="co-card__title"><?= e(__('company.new_applicants')) ?></h2><a href="<?= url('/company/applicants') ?>" aria-label="<?= e(__('nav.applicants')) ?>"><?= icon('chev', 'app-flip') ?></a></div>
      <?php if (!$applicants): ?><?= View::partial('partials/empty', ['icon' => 'users', 'text' => __('company.no_applicants')]) ?>
      <?php else: ?><div class="co-list"><?php foreach ($applicants as $a): ?>
        <a class="co-row app-row-link" href="<?= url('/company/applicants/' . $a['ApplicationID']) ?>"><span class="co-row__media"><?= e(initials($a['FullName'])) ?></span>
          <div class="co-row__text"><span class="co-row__title"><?= e($a['FullName']) ?></span><span class="co-row__sub"><?= e($a['Major']) ?> · <?= e(__('profile.gpa')) ?> <?= e($a['GPA']) ?> · <?= e($a['Title']) ?></span></div><?= status_badge('Pending') ?></a>
      <?php endforeach; ?></div><?php endif; ?>
    </section>
  </div>
  <div class="co-stack">
    <section class="co-card">
      <div class="co-card__head"><span class="co-tile co-tile--lavender"><?= icon('brief') ?></span><h2 class="co-card__title"><?= e(__('nav.opportunities')) ?></h2>
        <?php if ($c['ApprovalStatus'] === 'Approved'): ?><a class="co-iconbtn co-iconbtn--lavender app-iconbtn-sm" href="<?= url('/company/opportunities/create') ?>" aria-label="<?= e(__('opp.new')) ?>"><?= icon('plus') ?></a><?php endif; ?></div>
      <?php if (!$opportunities): ?><p class="co-caption app-body-text"><?= e(__('opp.none_company')) ?></p>
      <?php else: foreach ($opportunities as $o): $pct = (int) round($o['AcceptedCount'] * 100 / max(1, $o['Capacity'])); ?>
        <div class="app-oppmini"><div class="d-flex justify-content-between gap-2"><span class="co-row__title"><?= e($o['Title']) ?></span><?= status_badge($o['Status']) ?></div>
          <div class="co-progress co-progress--thin" role="progressbar" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?= e(__('opp.seats_title')) ?>"><span style="width:<?= $pct ?>%"></span></div>
          <div class="co-progress-meta"><span><?= e(__('opp.seats_title')) ?></span><span><?= (int) $o['AcceptedCount'] ?> / <?= (int) $o['Capacity'] ?></span></div></div>
      <?php endforeach; endif; ?>
    </section>
  </div>
</div>
