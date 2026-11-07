<?php
namespace Controllers\Company;

use Database;

class DashboardController extends Base
{
    public function index(): void
    {
        $c = $this->company();
        $cid = (int) $c['CompanyID'];
        $stats = [
            'open'      => (int) Database::value("SELECT COUNT(*) FROM coop_opportunities WHERE CompanyID = ? AND Status = 'Approved'", [$cid]),
            'pending'   => (int) Database::value("SELECT COUNT(*) FROM applications a JOIN coop_opportunities o ON o.OpportunityID = a.OpportunityID WHERE o.CompanyID = ? AND a.Status = 'Pending'", [$cid]),
            'trainees'  => (int) Database::value("SELECT COUNT(*) FROM students s JOIN training_placements p ON p.PlacementID = s.PlacementID JOIN coop_opportunities o ON o.OpportunityID = p.OpportunityID WHERE o.CompanyID = ? AND p.Status = 'Active'", [$cid]),
            'week_apps' => (int) Database::value("SELECT COUNT(*) FROM applications a JOIN coop_opportunities o ON o.OpportunityID = a.OpportunityID WHERE o.CompanyID = ? AND a.SubmittedDate >= CURDATE() - INTERVAL 7 DAY", [$cid]),
        ];
        $applicants = Database::all(
            "SELECT a.ApplicationID, a.SubmittedDate, o.Title, o.OpportunityID, u.FullName, s.Major, s.GPA, s.StudentID, s.CVFilePath
               FROM applications a JOIN coop_opportunities o ON o.OpportunityID = a.OpportunityID
               JOIN students s ON s.StudentID = a.StudentID JOIN users u ON u.UserID = s.UserID
              WHERE o.CompanyID = ? AND a.Status = 'Pending' ORDER BY a.ApplicationID DESC LIMIT 4", [$cid]);
        $placements = Database::all(
            "SELECT p.*, o.Title, (SELECT COUNT(*) FROM students s WHERE s.PlacementID = p.PlacementID) AS Trainees,
                    (SELECT Code FROM daily_codes dc WHERE dc.PlacementID = p.PlacementID AND dc.CodeDate = CURDATE() AND dc.IsActive = 1) AS TodayCode,
                    (SELECT COUNT(*) FROM attendance_records ar JOIN daily_codes dc ON dc.DailyCodeID = ar.DailyCodeID WHERE ar.PlacementID = p.PlacementID AND dc.CodeDate = CURDATE()) AS CheckedIn
               FROM training_placements p JOIN coop_opportunities o ON o.OpportunityID = p.OpportunityID
              WHERE o.CompanyID = ? AND p.Status = 'Active'", [$cid]);
        $opportunities = Database::all(
            "SELECT o.*, (SELECT COUNT(*) FROM applications a WHERE a.OpportunityID = o.OpportunityID AND a.Status = 'Accepted') AS AcceptedCount
               FROM coop_opportunities o WHERE o.CompanyID = ? ORDER BY o.OpportunityID DESC LIMIT 4", [$cid]);
        $this->view('company/dashboard', compact('c', 'stats', 'applicants', 'placements', 'opportunities'));
    }
}
