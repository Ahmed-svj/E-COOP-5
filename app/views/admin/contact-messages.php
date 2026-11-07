<?php $title = __('nav.contact_inbox'); ?>
<?= View::partial('partials/page-head', ['heading' => __('nav.contact_inbox'), 'sub' => __('admin.contact_sub')]) ?>
<?php if (!$items): ?><?= View::partial('partials/empty', ['icon' => 'mail', 'text' => __('admin.no_contact')]) ?>
<?php else: ?><div class="co-stack"><?php foreach ($items as $m): ?>
  <article class="co-card"><div class="co-card__head"><span class="co-avatar"><?= e(initials($m['SenderName'])) ?></span><div class="co-row__text"><h2 class="co-row__title"><?= e($m['Topic']) ?></h2><span class="co-row__sub"><?= e($m['SenderName']) ?> · <a href="mailto:<?= e($m['SenderEmail']) ?>" dir="ltr"><?= e($m['SenderEmail']) ?></a> · <?= e(fmt_date($m['SentAt'], true)) ?></span></div></div>
    <p class="app-body-text"><?= nl2br(e($m['Body'])) ?></p></article>
<?php endforeach; ?></div>
<?= View::partial('partials/pagination', compact('total', 'page', 'per')) ?><?php endif; ?>
