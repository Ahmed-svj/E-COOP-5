<?php $title = __('nav.notifications'); ?>
<?= View::partial('partials/page-head', ['heading' => __('nav.notifications'), 'sub' => __('notif.sub'),
  'actions' => $items ? '<form method="post" action="' . url('/notifications/read-all') . '">' . csrf_field() . '<button class="co-btn co-btn--soft">' . icon('ok', 'co-ico--sm') . e(__('notif.read_all')) . '</button></form>' : '']) ?>
<?php if (!$items): ?><?= View::partial('partials/empty', ['icon' => 'bell', 'text' => __('notif.none')]) ?>
<?php else: ?><div class="co-card"><div class="co-list">
  <?php foreach ($items as $n): ?>
    <a class="co-row app-row-link<?= $n['IsRead'] ? '' : ' app-unread' ?>" href="<?= url('/notifications/' . $n['NotificationID'] . '/open') ?>">
      <span class="co-tile<?= $n['IsRead'] ? '' : ' co-tile--lavender' ?>"><?= icon('bell') ?></span>
      <span class="co-row__text"><span class="co-row__title app-wrap" dir="auto"><?= e($n['Message']) ?></span><span class="co-row__sub"><bdi><?= e(fmt_date($n['SentDate'], true)) ?></bdi></span></span>
      <?php if (!$n['IsRead']): ?><span class="co-badge co-badge--lavender"><?= e(__('notif.new')) ?></span><?php endif; ?></a>
  <?php endforeach; ?>
</div></div>
<?= View::partial('partials/pagination', compact('total', 'page', 'per')) ?>
<?php endif; ?>
