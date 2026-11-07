<?php
namespace Controllers\Admin;

use Database;

class AuditLogController extends Base
{
    /** UC-23: read-only. */
    public function index(): void
    {
        $action = preg_replace('/[^A-Z_]/', '', strtoupper((string) ($_GET['action'] ?? '')));
        $where = $action ? 'WHERE l.Action LIKE ?' : '';
        $params = $action ? ["%$action%"] : [];
        [$offset, $per, $page] = paginate(30);
        $total = (int) Database::value("SELECT COUNT(*) FROM audit_logs l $where", $params);
        $logs = Database::all("SELECT l.*, u.FullName, u.Role FROM audit_logs l LEFT JOIN users u ON u.UserID = l.UserID $where ORDER BY l.AuditLogID DESC LIMIT $per OFFSET $offset", $params);
        $this->view('admin/audit-logs', compact('logs', 'action', 'total', 'page', 'per'));
    }
}
