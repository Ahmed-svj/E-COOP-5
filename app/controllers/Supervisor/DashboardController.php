<?php
namespace Controllers\Supervisor;

use Database;

class DashboardController extends Base
{
    /** UC-13 */
    public function index(): void
    {
        $sp = $this->sup();
        $sid = (int) $sp['SupervisorID'];
        $cohort = \ReportService::cohort($sid);
        $pendingAttendance = (int) Database::value(
            "SELECT COUNT(*) FROM attendance_records ar JOIN students s ON s.StudentID = ar.StudentID WHERE s.SupervisorID = ? AND ar.ApprovalStatus = 'Pending'", [$sid]);
        $pendingReports = (int) Database::value("SELECT COUNT(*) FROM final_reports WHERE SupervisorID = ? AND ReviewStatus = 'Pending'", [$sid]);
        $recent = Database::all(
            "SELECT ar.AttendanceID, ar.CheckInTime, dc.CodeDate, u.FullName, s.StudentID
               FROM attendance_records ar JOIN students s ON s.StudentID = ar.StudentID JOIN users u ON u.UserID = s.UserID
               JOIN daily_codes dc ON dc.DailyCodeID = ar.DailyCodeID
              WHERE s.SupervisorID = ? AND ar.ApprovalStatus = 'Pending' ORDER BY ar.CheckInTime DESC LIMIT 4", [$sid]);
        $load = count(Database::all('SELECT StudentID FROM students WHERE SupervisorID = ?', [$sid]));
        $this->view('supervisor/dashboard', compact('sp', 'cohort', 'pendingAttendance', 'pendingReports', 'recent', 'load'));
    }

    public function students(): void
    {
        $cohort = \ReportService::cohort((int) $this->sup()['SupervisorID']);
        $this->view('supervisor/students', compact('cohort'));
    }

    public function student(int $id): void
    {
        $s = $this->myStudent($id);
        $placement = $s['PlacementID'] ? Database::one(
            'SELECT p.*, o.Title, o.City, c.CompanyName FROM training_placements p JOIN coop_opportunities o ON o.OpportunityID = p.OpportunityID
               JOIN companies c ON c.CompanyID = o.CompanyID WHERE p.PlacementID = ?', [$s['PlacementID']]) : null;
        $records = Database::all(
            'SELECT ar.*, dc.CodeDate FROM attendance_records ar JOIN daily_codes dc ON dc.DailyCodeID = ar.DailyCodeID
              WHERE ar.StudentID = ? ORDER BY dc.CodeDate DESC', [$id]);
        $report = Database::one('SELECT * FROM final_reports WHERE StudentID = ?', [$id]);
        $evals = Database::all('SELECT * FROM evaluations WHERE StudentID = ?', [$id]);
        $week = \AttendanceService::week($id);
        $this->view('supervisor/student-details', compact('s', 'placement', 'records', 'report', 'evals', 'week'));
    }
}
