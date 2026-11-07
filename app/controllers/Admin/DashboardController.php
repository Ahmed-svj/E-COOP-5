<?php
namespace Controllers\Admin;

use Database;

class DashboardController extends Base
{
    public function index(): void
    {
        $k = \ReportService::adminKpis();
        $companies = Database::all("SELECT c.*, u.Email FROM companies c JOIN users u ON u.UserID = c.UserID WHERE c.ApprovalStatus = 'Pending' ORDER BY c.CreatedAt LIMIT 4");
        $opps = Database::all("SELECT o.*, c.CompanyName FROM coop_opportunities o JOIN companies c ON c.CompanyID = o.CompanyID WHERE o.Status = 'Pending Approval' ORDER BY o.OpportunityID LIMIT 4");
        $byStatus = \ReportService::applicationsByStatus();
        $loads = Database::all("SELECT * FROM v_supervisor_load WHERE ApprovalStatus = 'Approved' ORDER BY CurrentLoad DESC LIMIT 4");
        $recentLogs = Database::all('SELECT l.*, u.FullName FROM audit_logs l LEFT JOIN users u ON u.UserID = l.UserID ORDER BY l.AuditLogID DESC LIMIT 6');
        $this->view('admin/dashboard', compact('k', 'companies', 'opps', 'byStatus', 'loads', 'recentLogs'));
    }
}
