<?php
// Run once from terminal:  php database/create_admin.php "Admin Name" admin@jic.edu.sa "StrongPass123"
declare(strict_types=1);
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/bootstrap.php';

if ($argc !== 4) {
    fwrite(STDERR, "Usage: php database/create_admin.php \"Full Name\" email password\n");
    exit(1);
}
[$_, $name, $email, $pass] = $argv;
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 8 || !preg_match('/[A-Za-z]/', $pass) || !preg_match('/\d/', $pass)) {
    fwrite(STDERR, "Invalid email, or password is not 8+ characters with letters and numbers.\n");
    exit(1);
}
$db = Database::connect();
$db->beginTransaction();
$db->prepare("INSERT INTO users (FullName, Email, PasswordHash, Role) VALUES (?, ?, ?, 'Admin')")
   ->execute([$name, strtolower($email), password_hash($pass, PASSWORD_BCRYPT)]);
$uid = (int) $db->lastInsertId();
$db->prepare('INSERT INTO admins (UserID) VALUES (?)')->execute([$uid]);
$db->commit();
AuditService::log('ADMIN_CREATED_CLI', 'User', $uid, null, $uid);
echo "Admin created\n";
