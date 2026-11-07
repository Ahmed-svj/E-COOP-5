<?php
namespace Controllers\Admin;

use Database;

class OpportunityController extends Base
{
    /** UC-20 */
    public function index(): void
    {
        $status = in_array($_GET['status'] ?? '', ['Pending Approval', 'Approved', 'Rejected', 'Closed'], true) ? $_GET['status'] : 'Pending Approval';
        $items = Database::all(
            "SELECT o.*, c.CompanyName, (SELECT COUNT(*) FROM applications a WHERE a.OpportunityID = o.OpportunityID) AS Apps
               FROM coop_opportunities o JOIN companies c ON c.CompanyID = o.CompanyID WHERE o.Status = ? ORDER BY o.OpportunityID DESC", [$status]);
        $this->view('admin/opportunities', compact('items', 'status'));
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
        $o = Database::one('SELECT o.*, c.UserID FROM coop_opportunities o JOIN companies c ON c.CompanyID = o.CompanyID WHERE o.OpportunityID = ?', [$id]);
        if (!$o) {
            abort(404);
        }
        $stmt = Database::query("UPDATE coop_opportunities SET Status = ?, ApprovedByAdminID = ?, RejectionReason = ? WHERE OpportunityID = ? AND Status = 'Pending Approval'",
            [$status, $this->adminId(), $reason, $id]);
        if ($stmt->rowCount() !== 1) {
            flash('error', __('err.not_pending'));
            $this->back();
        }
        \NotificationService::notify((int) $o['UserID'],
            __($status === 'Approved' ? 'n.opp_approved' : 'n.opp_rejected', ['title' => $o['Title'], 'reason' => $reason]), '/company/opportunities');
        \AuditService::log('OPPORTUNITY_' . strtoupper($status), 'Opportunity', $id, $reason);
        flash('success', __('admin.decision_saved'));
        $this->back();
    }
}
