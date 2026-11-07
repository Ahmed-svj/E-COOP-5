<?php
class AcademicSupervisor extends Model
{
    protected static string $table = 'academic_supervisors';
    protected static string $pk = 'SupervisorID';

    public static function byUser(int $userId): ?array
    {
        return Database::one("SELECT sp.*, u.FullName, u.Email FROM academic_supervisors sp JOIN users u ON u.UserID = sp.UserID WHERE sp.UserID = ?", [$userId]);
    }
}
