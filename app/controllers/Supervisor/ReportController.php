<?php
namespace Controllers\Supervisor;

use Database;

class ReportController extends Base
{
    public function index(): void
    {
        $items = Database::all(
            'SELECT fr.*, u.FullName, s.InstitutionalID FROM final_reports fr JOIN students s ON s.StudentID = fr.StudentID
               JOIN users u ON u.UserID = s.UserID WHERE fr.SupervisorID = ? ORDER BY fr.ReviewStatus = "Pending" DESC, fr.SubmittedAt DESC',
            [$this->sup()['SupervisorID']]);
        $this->view('supervisor/reports', compact('items'));
    }

    /** UC-15: approve or request revision (comment required for revision). */
    public function review(int $id): void
    {
        $sp = $this->sup();
        $r = Database::one('SELECT fr.*, s.UserID FROM final_reports fr JOIN students s ON s.StudentID = fr.StudentID WHERE fr.ReportID = ? AND fr.SupervisorID = ?',
            [$id, $sp['SupervisorID']]);
        if (!$r) {
            abort(404);
        }
        $decision = $_POST['decision'] ?? '';
        $comments = mb_substr(trim((string) ($_POST['comments'] ?? '')), 0, 3000);
        if (!in_array($decision, ['Approved', 'Revision Requested'], true)) {
            abort(400);
        }
        if ($decision === 'Revision Requested' && $comments === '') {
            flash('error', __('report.comment_required'));
            $this->back();
        }
        $stmt = Database::query("UPDATE final_reports SET ReviewStatus = ?, ReviewComments = ? WHERE ReportID = ? AND ReviewStatus = 'Pending'",
            [$decision, $comments ?: null, $id]);
        if ($stmt->rowCount() !== 1) {
            flash('error', __('err.not_pending'));
            $this->back();
        }
        \NotificationService::notify((int) $r['UserID'], __($decision === 'Approved' ? 'n.report_approved' : 'n.report_revision'), '/student/report');
        \AuditService::log('REPORT_' . ($decision === 'Approved' ? 'APPROVED' : 'REVISION'), 'FinalReport', $id, $comments ?: null);
        flash('success', __('report.reviewed'));
        $this->back();
    }
}
