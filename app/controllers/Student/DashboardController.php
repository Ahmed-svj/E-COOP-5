<?php
namespace Controllers\Student;

use Auth;
use Controller;
use Database;

class DashboardController extends Controller
{
    public function index(): void
    {
        $s = Auth::profile();
        $sid = (int) $s['StudentID'];
        $summary = Database::one('SELECT ApprovedDays, PendingDays, RejectedDays FROM v_student_attendance_summary WHERE StudentID = ?', [$sid])
            ?? ['ApprovedDays' => 0, 'PendingDays' => 0, 'RejectedDays' => 0];
        $placement = $s['PlacementID'] ? Database::one(
            'SELECT p.*, o.Title, o.City, c.CompanyName FROM training_placements p
               JOIN coop_opportunities o ON o.OpportunityID = p.OpportunityID JOIN companies c ON c.CompanyID = o.CompanyID
              WHERE p.PlacementID = ?', [$s['PlacementID']]) : null;
        $week = \AttendanceService::week($sid);
        $checkedToday = (bool) Database::value(
            'SELECT 1 FROM attendance_records ar JOIN daily_codes dc ON dc.DailyCodeID = ar.DailyCodeID WHERE ar.StudentID = ? AND dc.CodeDate = CURDATE()', [$sid]);
        $applications = Database::all(
            'SELECT a.*, o.Title, o.City, c.CompanyName FROM applications a JOIN coop_opportunities o ON o.OpportunityID = a.OpportunityID
               JOIN companies c ON c.CompanyID = o.CompanyID WHERE a.StudentID = ? ORDER BY a.ApplicationID DESC LIMIT 3', [$sid]);
        $report = Database::one('SELECT * FROM final_reports WHERE StudentID = ?', [$sid]);
        $supervisor = $s['SupervisorID'] ? Database::one(
            'SELECT u.FullName, sp.Department FROM academic_supervisors sp JOIN users u ON u.UserID = sp.UserID WHERE sp.SupervisorID = ?', [$s['SupervisorID']]) : null;
        $recommended = $s['PlacementID'] ? [] : Database::all(
            'SELECT * FROM v_public_opportunities WHERE MajorRequirements LIKE ? AND AcceptedCount < Capacity ORDER BY PostedDate DESC LIMIT 3',
            ['%' . $s['Major'] . '%']);
        $notifications = Database::all('SELECT * FROM notifications WHERE UserID = ? ORDER BY SentDate DESC, NotificationID DESC LIMIT 3', [$s['UserID']]);
        $this->view('student/dashboard', compact('s', 'summary', 'placement', 'week', 'checkedToday', 'applications', 'report', 'supervisor', 'recommended', 'notifications'));
    }
}
