<?php /** @var array $week from AttendanceService::week */
$cls = ['Approved' => '', 'Pending' => ' co-bar--lav', 'Rejected' => ' co-bar--red', null => ' co-bar--empty'];
$h = ['Approved' => 92, 'Pending' => 70, 'Rejected' => 40, null => 8];
$summary = [];
foreach ($week as $d) { $summary[] = $d['label'] . ': ' . ($d['status'] ? __('status.' . $d['status']) : __('att.none')); }
?>
<div class="co-bars" role="img" aria-label="<?= e(implode(', ', $summary)) ?>">
  <?php foreach ($week as $d): ?>
    <div class="co-bar<?= $cls[$d['status']] ?>"><i style="height:<?= $h[$d['status']] ?>%"></i><b><?= e($d['label']) ?></b></div>
  <?php endforeach; ?>
</div>
<div class="co-legend"><span><?= e(__('status.Approved')) ?></span><span class="lav"><?= e(__('status.Pending')) ?></span><span class="red"><?= e(__('status.Rejected')) ?></span></div>
