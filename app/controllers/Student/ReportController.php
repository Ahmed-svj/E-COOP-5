<?php
namespace Controllers\Student;

use Auth;
use Controller;
use Database;
use Upload;

class ReportController extends Controller
{
    public function index(): void
    {
        $s = Auth::profile();
        $report = Database::one('SELECT * FROM final_reports WHERE StudentID = ?', [$s['StudentID']]);
        $placement = $s['PlacementID'] ? \Placement::find((int) $s['PlacementID']) : null;
        $this->view('student/report', compact('s', 'report', 'placement'));
    }

    /** UC-05: upload final report (one per student; re-upload allowed when revision is requested). */
    public function upload(): void
    {
        $s = Auth::profile();
        if (!$s['PlacementID'] || !$s['SupervisorID']) {
            flash('error', __('report.not_ready'));
            $this->redirect('/student/report');
        }
        $existing = Database::one('SELECT * FROM final_reports WHERE StudentID = ?', [$s['StudentID']]);
        if ($existing && $existing['ReviewStatus'] !== 'Revision Requested') {
            flash('error', __('report.locked'));
            $this->redirect('/student/report');
        }
        [$path, $err] = Upload::store($_FILES['report'] ?? null, 'reports');
        if ($err) {
            flash('error', $err);
            $this->redirect('/student/report');
        }
        if ($existing) {
            Upload::delete($existing['FilePath']);
            Database::query(
                "UPDATE final_reports SET FilePath = ?, SubmittedAt = NOW(), Status = 'Resubmitted', ReviewStatus = 'Pending' WHERE ReportID = ?",
                [$path, $existing['ReportID']]);
            $id = (int) $existing['ReportID'];
        } else {
            $id = \FinalReport::insert(['StudentID' => $s['StudentID'], 'PlacementID' => $s['PlacementID'], 'SupervisorID' => $s['SupervisorID'], 'FilePath' => $path]);
        }
        $supUser = (int) Database::value('SELECT UserID FROM academic_supervisors WHERE SupervisorID = ?', [$s['SupervisorID']]);
        \NotificationService::notify($supUser, __('n.report_submitted', ['name' => $s['FullName']]), '/supervisor/reports');
        \AuditService::log($existing ? 'REPORT_RESUBMITTED' : 'REPORT_SUBMITTED', 'FinalReport', $id);
        flash('success', __('report.uploaded'));
        $this->redirect('/student/report');
    }
}
