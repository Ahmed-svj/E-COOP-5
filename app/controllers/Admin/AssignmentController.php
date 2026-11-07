<?php
namespace Controllers\Admin;

use Database;

class AssignmentController extends Base
{
    /** UC-21 */
    public function index(): void
    {
        $students = Database::all(
            "SELECT s.StudentID, u.FullName, s.Major, s.SupervisorID, c.CompanyName, o.Title, p.PlacementID, p.Status AS PlacementStatus, p.EndDate,
                    (SELECT u2.FullName FROM academic_supervisors sp JOIN users u2 ON u2.UserID = sp.UserID WHERE sp.SupervisorID = s.SupervisorID) AS SupervisorName
               FROM students s JOIN users u ON u.UserID = s.UserID
               JOIN training_placements p ON p.PlacementID = s.PlacementID
               JOIN coop_opportunities o ON o.OpportunityID = p.OpportunityID JOIN companies c ON c.CompanyID = o.CompanyID
              ORDER BY s.SupervisorID IS NOT NULL, u.FullName");
        $supervisors = Database::all("SELECT * FROM v_supervisor_load WHERE ApprovalStatus = 'Approved' ORDER BY FullName");
        $this->view('admin/assign-supervisors', compact('students', 'supervisors'));
    }

    public function assign(): void
    {
        $studentId = (int) ($_POST['student_id'] ?? 0);
        $supId = (int) ($_POST['supervisor_id'] ?? 0);
        $s = \Student::withUser($studentId);
        $load = Database::one("SELECT * FROM v_supervisor_load WHERE SupervisorID = ? AND ApprovalStatus = 'Approved'", [$supId]);
        if (!$s || !$load || !$s['PlacementID']) {
            flash('error', __('err.not_found'));
            $this->back();
        }
        if ((int) $s['SupervisorID'] !== $supId && $load['CurrentLoad'] >= $load['MaxStudentLoad'] && empty($_POST['override'])) {
            flash('warning', __('assign.full', ['name' => $load['FullName']]));
            $this->back();
        }
        Database::query('UPDATE students SET SupervisorID = ? WHERE StudentID = ?', [$supId, $studentId]);
        // Keep an existing final report pointing at the current supervisor.
        Database::query('UPDATE final_reports SET SupervisorID = ? WHERE StudentID = ?', [$supId, $studentId]);
        $supUser = (int) Database::value('SELECT UserID FROM academic_supervisors WHERE SupervisorID = ?', [$supId]);
        \NotificationService::notify((int) $s['UserID'], __('n.supervisor_assigned', ['name' => $load['FullName']]), '/student/dashboard');
        \NotificationService::notify($supUser, __('n.student_assigned', ['name' => $s['FullName']]), "/supervisor/students/$studentId");
        \AuditService::log('SUPERVISOR_ASSIGNED', 'Student', $studentId, 'Supervisor ' . $supId);
        flash('success', __('assign.done'));
        $this->back();
    }

    /** All training placements with their trainees and progress. */
    public function placements(): void
    {
        $status = in_array($_GET['status'] ?? '', ['Active', 'Completed'], true) ? $_GET['status'] : '';
        $items = Database::all(
            "SELECT p.*, o.Title, o.City, o.Capacity, c.CompanyName,
                    (SELECT COUNT(*) FROM students s WHERE s.PlacementID = p.PlacementID) AS Trainees,
                    (SELECT COUNT(*) FROM students s WHERE s.PlacementID = p.PlacementID AND s.SupervisorID IS NULL) AS NoSupervisor,
                    (SELECT COUNT(*) FROM daily_codes dc WHERE dc.PlacementID = p.PlacementID) AS CodeDays,
                    (SELECT COUNT(*) FROM attendance_records ar WHERE ar.PlacementID = p.PlacementID AND ar.ApprovalStatus = 'Approved') AS ApprovedDays
               FROM training_placements p JOIN coop_opportunities o ON o.OpportunityID = p.OpportunityID JOIN companies c ON c.CompanyID = o.CompanyID
              " . ($status ? 'WHERE p.Status = ?' : '') . ' ORDER BY p.Status, p.EndDate', $status ? [$status] : []);
        foreach ($items as &$p) {
            $p['students'] = Database::all('SELECT s.StudentID, u.FullName, u.UserID FROM students s JOIN users u ON u.UserID = s.UserID WHERE s.PlacementID = ? ORDER BY u.FullName', [$p['PlacementID']]);
        }
        unset($p);
        $this->view('admin/placements', compact('items', 'status'));
    }

    public function complete(int $id): void
    {
        \PlacementService::complete($id);
        flash('success', __('assign.completed'));
        $this->back();
    }
}
