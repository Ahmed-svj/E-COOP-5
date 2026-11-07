<?php
class Student extends Model
{
    protected static string $table = 'students';
    protected static string $pk = 'StudentID';

    public static function byUser(int $userId): ?array
    {
        return Database::one("SELECT s.*, u.FullName, u.Email, u.Phone FROM students s JOIN users u ON u.UserID = s.UserID WHERE s.UserID = ?", [$userId]);
    }

    public static function withUser(int $studentId): ?array
    {
        return Database::one("SELECT s.*, u.FullName, u.Email, u.Phone, u.UserID FROM students s JOIN users u ON u.UserID = s.UserID WHERE s.StudentID = ?", [$studentId]);
    }

    /** Profile is complete when phone, major, GPA and CV are present (required to apply). */
    public static function isComplete(array $s): bool
    {
        return !empty($s["Phone"]) && !empty($s["Major"]) && $s["GPA"] !== null && !empty($s["CVFilePath"]);
    }
}
