<?php
// 0 0 * * *  php /path/coop/cron/expire_daily_codes.php   (backup for the MySQL event)
declare(strict_types=1);
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/bootstrap.php';
$n = DailyCodeService::expireOld();
echo date('c') . " expired $n daily codes\n";
