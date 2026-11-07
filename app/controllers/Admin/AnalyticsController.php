<?php
namespace Controllers\Admin;

use Database;

class AnalyticsController extends Base
{
    /** UC-22 */
    public function index(): void
    {
        $k = \ReportService::adminKpis();
        $byStatus = \ReportService::applicationsByStatus();
        $byCity = \ReportService::placementsByCity();
        $byMajor = \ReportService::opportunitiesByMajor();
        $scores = \ReportService::averageScores();
        $attendance = Database::all(
            "SELECT dc.CodeDate AS d, SUM(ar.ApprovalStatus = 'Approved') AS approved, SUM(ar.ApprovalStatus = 'Pending') AS pending, SUM(ar.ApprovalStatus = 'Rejected') AS rejected
               FROM attendance_records ar JOIN daily_codes dc ON dc.DailyCodeID = ar.DailyCodeID
              WHERE dc.CodeDate >= CURDATE() - INTERVAL 14 DAY GROUP BY dc.CodeDate ORDER BY dc.CodeDate");
        $this->view('admin/analytics', compact('k', 'byStatus', 'byCity', 'byMajor', 'scores', 'attendance'));
    }
}
