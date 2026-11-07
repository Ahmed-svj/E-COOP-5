<?php
namespace Controllers\Company;

use Database;

class DailyCodeController extends Base
{
    public function index(): void
    {
        $c = $this->company();
        $placements = Database::all(
            "SELECT p.*, o.Title,
                    (SELECT COUNT(*) FROM students s WHERE s.PlacementID = p.PlacementID) AS Trainees,
                    (SELECT COUNT(*) FROM attendance_records ar JOIN daily_codes dc ON dc.DailyCodeID = ar.DailyCodeID WHERE ar.PlacementID = p.PlacementID AND dc.CodeDate = CURDATE()) AS CheckedIn
               FROM training_placements p JOIN coop_opportunities o ON o.OpportunityID = p.OpportunityID
              WHERE o.CompanyID = ? AND p.Status = 'Active' ORDER BY o.Title", [$c['CompanyID']]);
        foreach ($placements as &$p) {
            $p['code'] = \DailyCodeService::today((int) $p['PlacementID']);
            $p['students'] = Database::all(
                "SELECT u.FullName, ar.CheckInTime, ar.ApprovalStatus FROM students s JOIN users u ON u.UserID = s.UserID
                   LEFT JOIN attendance_records ar ON ar.StudentID = s.StudentID
                    AND ar.DailyCodeID = (SELECT DailyCodeID FROM daily_codes WHERE PlacementID = s.PlacementID AND CodeDate = CURDATE())
                  WHERE s.PlacementID = ? ORDER BY u.FullName", [$p['PlacementID']]);
        }
        unset($p);
        $this->view('company/daily-code', compact('placements'));
    }

    /** UC-11 */
    public function generate(): void
    {
        $this->requireApproved();
        $pid = (int) ($_POST['placement_id'] ?? 0);
        $ok = Database::value(
            "SELECT 1 FROM training_placements p JOIN coop_opportunities o ON o.OpportunityID = p.OpportunityID
              WHERE p.PlacementID = ? AND o.CompanyID = ? AND p.Status = 'Active'", [$pid, $this->company()['CompanyID']]);
        if (!$ok) {
            abort(404);
        }
        \DailyCodeService::generate($pid);
        flash('success', __('code.generated'));
        $this->redirect('/company/daily-code');
    }
}
