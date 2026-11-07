<?php
class User extends Model
{
    protected static string $table = 'users';
    protected static string $pk = 'UserID';

    public static function findByEmail(string $email): ?array
    {
        return Database::one("SELECT * FROM users WHERE Email = ? LIMIT 1", [strtolower(trim($email))]);
    }
}
