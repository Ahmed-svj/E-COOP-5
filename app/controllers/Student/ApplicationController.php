<?php
namespace Controllers\Student;

use Auth;
use Controller;
use Database;

class ApplicationController extends Controller
{
    public function index(): void
    {
        $status = $this->input('status');
        $params = [Auth::profile()['StudentID']];
        $filter = '';
        if (in_array($status, ['Pending', 'Accepted', 'Rejected', 'Withdrawn'], true)) {
            $filter = ' AND a.Status = ?';
            $params[] = $status;
        }
        $items = Database::all(
            "SELECT a.*, o.Title, o.City, c.CompanyName FROM applications a
               JOIN coop_opportunities o ON o.OpportunityID = a.OpportunityID JOIN companies c ON c.CompanyID = o.CompanyID
              WHERE a.StudentID = ?$filter ORDER BY a.ApplicationID DESC",
            $params
        );
        $this->view('student/applications', compact('items', 'status'));
    }

    public function withdraw(int $id): void
    {
        $stmt = Database::query("UPDATE applications SET Status = 'Withdrawn' WHERE ApplicationID = ? AND StudentID = ? AND Status = 'Pending'",
            [$id, Auth::profile()['StudentID']]);
        if ($stmt->rowCount() === 1) {
            \AuditService::log('APPLICATION_WITHDRAWN', 'Application', $id);
            flash('success', __('app.withdrawn'));
        } else {
            flash('error', __('err.not_pending'));
        }
        $this->redirect('/student/applications');
    }
}
