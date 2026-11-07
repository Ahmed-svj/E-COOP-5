<div class="app-empty">
  <span class="co-tile co-tile--lavender"><?= icon($icon ?? 'info') ?></span>
  <p class="app-empty__title"><?= e($text) ?></p>
  <?php if (!empty($hint)): ?><p class="co-caption"><?= e($hint) ?></p><?php endif; ?>
  <?php if (!empty($action)): ?><div><?= $action ?></div><?php endif; ?>
</div>
