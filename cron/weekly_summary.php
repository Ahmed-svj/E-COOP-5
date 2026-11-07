<?php
// 0 15 * * 4  php /path/coop/cron/weekly_summary.php   (Thursday afternoon)
// Emails a short weekly summary to users who turned on "Weekly summary" in Settings.
declare(strict_types=1);
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/bootstrap.php';
$users = Database::all(
    "SELECT u.UserID, u.Email, u.FullName FROM users u JOIN user_preferences p ON p.UserID = u.UserID
      WHERE p.PreferenceKey = 'weekly_summary' AND p.Enabled = 1 AND u.AccountStatus = 'Active'");
foreach ($users as $u) {
    $items = Database::all('SELECT Message FROM notifications WHERE UserID = ? AND SentDate >= NOW() - INTERVAL 7 DAY ORDER BY SentDate DESC LIMIT 20', [$u['UserID']]);
    $body = __('n.weekly_intro', ['name' => $u['FullName'], 'n' => count($items)]) . "\n\n"
        . ($items ? '- ' . implode("\n- ", array_column($items, 'Message')) : __('notif.none')) . "\n\n" . full_url('/notifications');
    MailService::send($u['Email'], __('n.weekly_subject'), $body);
}
echo date('c') . ' sent ' . count($users) . " weekly summaries\n";
