<?php
$pages = (int) ceil($total / $per);
if ($pages <= 1) return;
$q = $_GET;
?>
<nav class="app-pager" aria-label="<?= e(__('a11y.pagination')) ?>">
  <?php for ($i = 1; $i <= $pages; $i++): $q['page'] = $i; ?>
    <a class="co-chip" href="?<?= e(http_build_query($q)) ?>"<?= $i === $page ? ' aria-current="page" aria-pressed="true"' : '' ?>><?= $i ?></a>
  <?php endfor; ?>
</nav>
