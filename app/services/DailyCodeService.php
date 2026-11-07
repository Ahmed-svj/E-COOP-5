<?php
class DailyCodeService
{
    public static function today(int $placementId): ?array
    {
        return Database::one(
            'SELECT * FROM daily_codes WHERE PlacementID = ? AND CodeDate = CURDATE() AND IsActive = 1 AND ExpiryTime > NOW()',
            [$placementId]
        );
    }

    /** One CSPRNG 6-digit code per placement per day, valid until 23:59:59. */
    public static function generate(int $placementId): array
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        try {
            Database::query(
                "INSERT INTO daily_codes (PlacementID, Code, CodeDate, ExpiryTime) VALUES (?, ?, CURDATE(), CONCAT(CURDATE(), ' 23:59:59'))",
                [$placementId, $code]
            );
            AuditService::log('DAILY_CODE_GENERATED', 'Placement', $placementId);
        } catch (PDOException $e) {
            if ($e->getCode() !== '23000') {
                throw $e;
            }
            // Already generated today (UNIQUE PlacementID + CodeDate): show the existing one.
        }
        return Database::one('SELECT * FROM daily_codes WHERE PlacementID = ? AND CodeDate = CURDATE()', [$placementId]);
    }

    /** Midnight job: deactivate expired codes. */
    public static function expireOld(): int
    {
        return Database::query("UPDATE daily_codes SET IsActive = 0, Status = 'Expired' WHERE IsActive = 1 AND ExpiryTime < NOW()")->rowCount();
    }
}
