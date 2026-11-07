<?php
// 30 6 * * 0-4  php /path/coop/cron/attendance_reminders.php   (each training morning, Sun–Thu)
// Reminds placed students to check in, if "Attendance reminders" is on in their Settings.
declare(strict_types=1);
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/bootstrap.php';
$rows = Database::all(
    "SELECT s.StudentID, u.UserID FROM students s JOIN users u ON u.UserID = s.UserID
       JOIN training_placements p ON p.PlacementID = s.PlacementID
      WHERE p.Status = 'Active' AND u.AccountStatus = 'Active' AND CURDATE() BETWEEN p.StartDate AND p.EndDate
        AND COALESCE((SELECT Enabled FROM user_preferences WHERE UserID = u.UserID AND PreferenceKey = 'notify_attendance'), 1) = 1");
foreach ($rows as $r) {
    NotificationService::notify((int) $r['UserID'], __('n.attendance_reminder'), '/student/attendance');
}
echo date('c') . ' sent ' . count($rows) . " attendance reminders\n";
