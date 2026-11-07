<?php /** @var int $pct @var string $caption */
$pct = max(0, min(100, (int) $pct));
$c = 2 * M_PI * 56;
$len = $c * $pct / 100;
$mint = min($len, $c * 0.2);
$angle = deg2rad(360 * $pct / 100);
$kx = 66 + 56 * cos($angle); $ky = 66 + 56 * sin($angle);
?>
<div class="co-ring" role="img" aria-label="<?= e($pct . '% · ' . $caption) ?>">
  <svg viewBox="0 0 132 132"><circle class="co-ring__track" cx="66" cy="66" r="56"/>
  <?php if ($pct > 0): ?>
    <circle class="co-ring__lav" cx="66" cy="66" r="56" stroke-dasharray="<?= round($len, 1) ?> <?= round($c, 1) ?>"/>
    <circle class="co-ring__mint" cx="66" cy="66" r="56" stroke-dasharray="<?= round($mint, 1) ?> <?= round($c, 1) ?>" stroke-dashoffset="<?= round(-($len - $mint), 1) ?>"/>
    <circle class="co-ring__knob" cx="<?= round($kx, 1) ?>" cy="<?= round($ky, 1) ?>" r="8"/>
  <?php endif; ?></svg>
  <div class="co-ring__label"><span class="co-ring__value"><?= $pct ?>%</span><span class="co-caption"><?= e($caption) ?></span></div>
</div>
