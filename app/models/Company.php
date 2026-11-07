<?php
class Company extends Model
{
    protected static string $table = 'companies';
    protected static string $pk = 'CompanyID';

    public static function byUser(int $userId): ?array
    {
        return Database::one("SELECT c.*, u.FullName, u.Email, u.Phone FROM companies c JOIN users u ON u.UserID = c.UserID WHERE c.UserID = ?", [$userId]);
    }
}
