<?php
// 5 0 * * *  php /path/coop/cron/complete_placements.php
declare(strict_types=1);
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/bootstrap.php';
$n = PlacementService::completeDue();
echo date('c') . " completed $n placements\n";
