<?php
namespace Controllers\Supervisor;

use Database;

class AttendanceController extends Base
{
    public function index(): void
    {
        $status = in_array($_GET['status'] ?? '', ['Pending', 'Approved', 'Rejected'], true) ? $_GET['status'] : 'Pending';
        $items = Database::all(
            "SELECT ar.*, dc.CodeDate, u.FullName, s.StudentID, c.CompanyName
               FROM attendance_records ar JOIN students s ON s.StudentID = ar.StudentID JOIN users u ON u.UserID = s.UserID
               JOIN daily_codes dc ON dc.DailyCodeID = ar.DailyCodeID
               JOIN training_placements p ON p.PlacementID = ar.PlacementID JOIN coop_opportunities o ON o.OpportunityID = p.OpportunityID
               JOIN companies c ON c.CompanyID = o.CompanyID
              WHERE s.SupervisorID = ? AND ar.ApprovalStatus = ? ORDER BY dc.CodeDate DESC, u.FullName LIMIT 200",
            [$this->sup()['SupervisorID'], $status]);
        $this->view('supervisor/attendance', compact('items', 'status'));
    }

    /** UC-14 */
    public function approve(int $id): void
    {
        $this->decide($id, 'Approved', null);
        $this->back();
    }

    public function reject(int $id): void
    {
        $comment = mb_substr(trim((string) ($_POST['comment'] ?? '')), 0, 1000);
        if ($comment === '') {
            flash('error', __('att.comment_required'));
            $this->back();
        }
        $this->decide($id, 'Rejected', $comment);
        $this->back();
    }

    /** Change a decision that was already made (Approved <-> Rejected). Rejecting needs a comment. */
    public function change(int $id): void
    {
        $sp = $this->sup();
        $status = $_POST['status'] ?? '';
        $comment = mb_substr(trim((string) ($_POST['comment'] ?? '')), 0, 1000);
        if (!in_array($status, ['Approved', 'Rejected'], true)) {
            abort(400);
        }
        $rec = Database::one(
            'SELECT ar.*, s.UserID, dc.CodeDate FROM attendance_records ar JOIN students s ON s.StudentID = ar.StudentID
               JOIN daily_codes dc ON dc.DailyCodeID = ar.DailyCodeID WHERE ar.AttendanceID = ? AND s.SupervisorID = ?',
            [$id, $sp['SupervisorID']]);
        if (!$rec) {
            abort(404);
        }
        if ($rec['ApprovalStatus'] === $status) {
            flash('warning', __('att.same_status', ['status' => __('status.' . $status)]));
            $this->back();
        }
        if ($status === 'Rejected' && $comment === '') {
            flash('error', __('att.comment_required'));
            $this->back();
        }
        Database::query(
            'UPDATE attendance_records SET ApprovalStatus = ?, ReviewComment = ?, ReviewedBySupervisorID = ?, ReviewedAt = NOW() WHERE AttendanceID = ?',
            [$status, $comment !== '' ? $comment : null, $sp['SupervisorID'], $id]);
        \NotificationService::notify((int) $rec['UserID'],
            __('n.att_changed', ['date' => fmt_date($rec['CodeDate']), 'status' => __('status.' . $status)]), '/student/attendance');
        \AuditService::log('ATTENDANCE_CHANGED', 'Attendance', $id, $rec['ApprovalStatus'] . ' -> ' . $status . ($comment !== '' ? ': ' . $comment : ''));
        flash('success', __('att.changed', ['status' => __('status.' . $status)]));
        $this->back();
    }

    public function approveAll(): void
    {
        $ids = Database::all(
            "SELECT ar.AttendanceID FROM attendance_records ar JOIN students s ON s.StudentID = ar.StudentID
              WHERE s.SupervisorID = ? AND ar.ApprovalStatus = 'Pending'", [$this->sup()['SupervisorID']]);
        foreach ($ids as $r) {
            $this->decide((int) $r['AttendanceID'], 'Approved', null, false);
        }
        flash('success', __('att.approved_n', ['n' => count($ids)]));
        $this->back();
    }

    private function decide(int $id, string $status, ?string $comment, bool $flash = true): void
    {
        $sp = $this->sup();
        $rec = Database::one(
            'SELECT ar.*, s.UserID, dc.CodeDate FROM attendance_records ar JOIN students s ON s.StudentID = ar.StudentID
               JOIN daily_codes dc ON dc.DailyCodeID = ar.DailyCodeID WHERE ar.AttendanceID = ? AND s.SupervisorID = ?',
            [$id, $sp['SupervisorID']]);
        if (!$rec) {
            abort(404);
        }
        $stmt = Database::query(
            "UPDATE attendance_records SET ApprovalStatus = ?, ReviewComment = ?, ReviewedBySupervisorID = ?, ReviewedAt = NOW()
              WHERE AttendanceID = ? AND ApprovalStatus = 'Pending'", [$status, $comment, $sp['SupervisorID'], $id]);
        if ($stmt->rowCount() !== 1) {
            if ($flash) {
                flash('error', __('err.not_pending'));
            }
            return;
        }
        $msg = $status === 'Approved'
            ? __('n.att_approved', ['date' => fmt_date($rec['CodeDate'])])
            : __('n.att_rejected', ['date' => fmt_date($rec['CodeDate'])]);
        \NotificationService::notify((int) $rec['UserID'], $msg, '/student/attendance');
        \AuditService::log('ATTENDANCE_' . strtoupper($status), 'Attendance', $id, $comment);
        if ($flash) {
            flash('success', __($status === 'Approved' ? 'att.approved' : 'att.rejected'));
        }
    }
}
