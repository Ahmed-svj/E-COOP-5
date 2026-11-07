<?php
class Admin extends Model
{
    protected static string $table = 'admins';
    protected static string $pk = 'AdminID';

    public static function byUser(int $userId): ?array
    {
        return Database::one("SELECT * FROM admins WHERE UserID = ?", [$userId]);
    }
}
