<?php
namespace Controllers\Admin;

use Database;

class CompanyController extends Base
{
    /** UC-19 */
    public function index(): void
    {
        $status = in_array($_GET['status'] ?? '', ['Pending', 'Approved', 'Rejected'], true) ? $_GET['status'] : 'Pending';
        $items = Database::all(
            'SELECT c.*, u.FullName, u.Email, u.Phone, (SELECT COUNT(*) FROM coop_opportunities o WHERE o.CompanyID = c.CompanyID) AS Opps
               FROM companies c JOIN users u ON u.UserID = c.UserID WHERE c.ApprovalStatus = ? ORDER BY c.CreatedAt DESC', [$status]);
        $this->view('admin/companies', compact('items', 'status'));
    }

    public function approve(int $id): void
    {
        $this->decide($id, 'Approved', null);
    }

    public function reject(int $id): void
    {
        $reason = mb_substr(trim((string) ($_POST['reason'] ?? '')), 0, 1000);
        if ($reason === '') {
            flash('error', __('app.reason_required'));
            $this->back();
        }
        $this->decide($id, 'Rejected', $reason);
    }

    private function decide(int $id, string $status, ?string $reason): void
    {
        $c = \Company::find($id);
        if (!$c) {
            abort(404);
        }
        $stmt = Database::query("UPDATE companies SET ApprovalStatus = ?, ApprovedByAdminID = ?, RejectionReason = ? WHERE CompanyID = ? AND ApprovalStatus = 'Pending'",
            [$status, $this->adminId(), $reason, $id]);
        if ($stmt->rowCount() !== 1) {
            flash('error', __('err.not_pending'));
            $this->back();
        }
        \NotificationService::notify((int) $c['UserID'], __($status === 'Approved' ? 'n.company_approved' : 'n.company_rejected', ['reason' => $reason]), '/company/dashboard');
        \AuditService::log('COMPANY_' . strtoupper($status), 'Company', $id, $reason);
        flash('success', __('admin.decision_saved'));
        $this->back();
    }
}
