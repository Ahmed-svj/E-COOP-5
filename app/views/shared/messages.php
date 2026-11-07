<?php $title = __('nav.messages'); $scripts = ['messages.js']; $me = Auth::id(); ?>
<?= View::partial('partials/page-head', ['heading' => __('nav.messages'), 'sub' => __('msg.sub')]) ?>
<div class="app-chat">
  <aside class="co-card app-chat__list">
    <form method="post" action="<?= url('/messages/start') ?>" class="app-chat__new">
      <?= csrf_field() ?>
      <label class="visually-hidden" for="new-chat"><?= e(__('msg.new')) ?></label>
      <select class="co-input" id="new-chat" name="user_id" required><option value=""><?= e(__('msg.new')) ?></option>
        <?php foreach ($contacts as $ct): ?><option value="<?= (int) $ct['UserID'] ?>"><?= e($ct['FullName']) ?> · <?= e(__('role.' . $ct['Role'])) ?></option><?php endforeach; ?></select>
      <button class="co-iconbtn co-iconbtn--mint app-iconbtn-sm" type="submit" aria-label="<?= e(__('msg.start')) ?>"><?= icon('plus') ?></button>
    </form>
    <?php if (!$conversations): ?><p class="co-caption"><?= e(__('msg.none')) ?></p><?php endif; ?>
    <div class="co-list"><?php foreach ($conversations as $c): ?>
      <a class="co-row app-row-link" href="<?= url('/messages/' . $c['ConversationID']) ?>"<?= $active && (int) $active['ConversationID'] === (int) $c['ConversationID'] ? ' aria-current="page"' : '' ?>>
        <span class="co-avatar"><?= e(initials($c['Title'] ?: (string) $c['Others'])) ?></span>
        <span class="co-row__text"><span class="co-row__title"><?= e($c['Title'] ?: $c['Others']) ?></span><span class="co-row__sub app-ellipsis"><?= e($c['LastBody'] ?? __('msg.empty')) ?></span></span>
        <?php if ($c['Unread']): ?><span class="co-badge co-badge--lavender"><?= (int) $c['Unread'] ?></span><?php endif; ?></a>
    <?php endforeach; ?></div>
  </aside>
  <section class="co-card app-chat__thread">
    <?php if (!$active): ?><?= View::partial('partials/empty', ['icon' => 'chat', 'text' => __('msg.pick')]) ?>
    <?php else: ?>
      <div class="co-card__head"><span class="co-avatar"><?= e(initials($active['Title'] ?: (string) $active['Others'])) ?></span><h2 class="co-card__title"><?= e($active['Title'] ?: $active['Others']) ?></h2></div>
      <div class="app-chat__msgs" id="chat-msgs" data-conversation="<?= (int) $active['ConversationID'] ?>" data-last="<?= (int) (end($messages)['MessageID'] ?? 0) ?>" aria-live="polite">
        <?php foreach ($messages as $m): ?><div class="app-msg<?= (int) $m['SenderUserID'] === $me ? ' app-msg--mine' : '' ?>"><p><?= nl2br(e($m['Body'])) ?></p><span><?= e(date('H:i', strtotime($m['SentAt']))) ?></span></div><?php endforeach; ?>
      </div>
      <form method="post" action="<?= url('/messages/' . $active['ConversationID']) ?>" class="app-chat__form" id="chat-form">
        <?= csrf_field() ?><label class="visually-hidden" for="chat-body"><?= e(__('msg.write')) ?></label>
        <textarea class="co-input app-textarea" id="chat-body" name="body" rows="1" maxlength="4000" required placeholder="<?= e(__('msg.write')) ?>"></textarea>
        <button class="co-iconbtn co-iconbtn--mint" type="submit" aria-label="<?= e(__('msg.send')) ?>"><?= icon('send') ?></button>
      </form>
    <?php endif; ?>
  </section>
</div>
