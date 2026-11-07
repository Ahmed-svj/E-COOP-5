<?php
/** Append-only audit trail (UPDATE/DELETE are blocked by DB triggers). */
class AuditService
{
    public static function log(string $action, ?string $entityType = null, ?int $entityId = null, ?string $details = null, ?int $userId = null): void
    {
        $userId ??= (session_status() === PHP_SESSION_ACTIVE ? ($_SESSION['user_id'] ?? null) : null);
        Database::query(
            'INSERT INTO audit_logs (UserID, Action, EntityType, EntityID, IPAddress, Details) VALUES (?, ?, ?, ?, ?, ?)',
            [$userId, $action, $entityType, $entityId, PHP_SAPI === 'cli' ? 'cli' : client_ip(), $details]
        );
    }
}
