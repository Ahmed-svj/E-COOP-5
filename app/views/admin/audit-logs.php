<?php $title = __('nav.audit'); ?>
<?= View::partial('partials/page-head', ['heading' => __('nav.audit'), 'sub' => __('admin.audit_sub')]) ?>
<form class="app-filters" method="get"><label class="co-search app-filters__q"><?= icon('search') ?><input type="search" name="action" value="<?= e($action) ?>" placeholder="LOGIN, COMPANY_APPROVED…" aria-label="<?= e(__('admin.filter_action')) ?>"></label></form>
<div class="co-table-wrap table-responsive"><table class="co-table">
  <thead><tr><th><?= e(__('admin.time')) ?></th><th><?= e(__('admin.user')) ?></th><th><?= e(__('admin.action')) ?></th><th><?= e(__('admin.entity')) ?></th><th>IP</th><th><?= e(__('admin.details')) ?></th></tr></thead>
  <tbody><?php foreach ($logs as $l): ?><tr><td class="num"><?= e(date('Y-m-d H:i:s', strtotime($l['Timestamp']))) ?></td><td><?= e($l['FullName'] ?? '—') ?><?= $l['Role'] ? '<br><span class="co-caption">' . e(__('role.' . $l['Role'])) . '</span>' : '' ?></td>
    <td><span class="app-mono"><?= e($l['Action']) ?></span></td><td><?= e(trim(($l['EntityType'] ?? '') . ' ' . ($l['EntityID'] ?? ''))) ?></td><td class="num" dir="ltr"><?= e($l['IPAddress']) ?></td><td class="app-wrap"><?= e($l['Details'] ?? '') ?></td></tr><?php endforeach; ?></tbody>
</table></div>
<?= View::partial('partials/pagination', compact('total', 'page', 'per')) ?>
