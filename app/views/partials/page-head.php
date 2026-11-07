<?php /** @var string $heading @var ?string $sub @var ?string $actions */ ?>
<div class="app-pagehead">
  <div>
    <h1 class="app-pagehead__title"><?= e($heading) ?></h1>
    <?php if (!empty($sub)): ?><p class="app-pagehead__sub"><?= e($sub) ?></p><?php endif; ?>
  </div>
  <?php if (!empty($actions)): ?><div class="app-pagehead__actions"><?= $actions ?></div><?php endif; ?>
</div>
