<?php
class UserPreference extends Model
{
    protected static string $table = 'user_preferences';
    protected static string $pk = 'UserID';

    public static function set(int $userId, string $key, bool $enabled): void
    {
        Database::query("INSERT INTO user_preferences (UserID, PreferenceKey, Enabled) VALUES (?, ?, ?)
                         ON DUPLICATE KEY UPDATE Enabled = VALUES(Enabled)", [$userId, $key, (int) $enabled]);
    }
}
