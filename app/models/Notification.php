<?php
class Notification extends Model
{
    protected static string $table = 'notifications';
    protected static string $pk = 'NotificationID';

    public static function unreadCount(int $userId): int
    {
        return (int) Database::value("SELECT COUNT(*) FROM notifications WHERE UserID = ? AND IsRead = 0", [$userId]);
    }
}
