<?php
namespace Controllers\Company;

use Auth;
use Database;

class EvaluationController extends Base
{
    public function trainees(): void
    {
        $items = Database::all(
            "SELECT s.StudentID, u.FullName, s.Major, o.Title, p.PlacementID, p.StartDate, p.EndDate, p.Status AS PlacementStatus,
                    SUM(ar.ApprovalStatus = 'Approved') AS Approved, COUNT(ar.AttendanceID) AS Total,
                    (SELECT Score FROM evaluations e WHERE e.StudentID = s.StudentID AND e.PlacementID = p.PlacementID AND e.EvaluatorType = 'Company') AS Score
               FROM students s JOIN users u ON u.UserID = s.UserID
               JOIN training_placements p ON p.PlacementID = s.PlacementID
               JOIN coop_opportunities o ON o.OpportunityID = p.OpportunityID
               LEFT JOIN attendance_records ar ON ar.StudentID = s.StudentID
              WHERE o.CompanyID = ?
              GROUP BY s.StudentID, u.FullName, s.Major, o.Title, p.PlacementID, p.StartDate, p.EndDate, p.Status
              ORDER BY u.FullName", [$this->company()['CompanyID']]);
        $this->view('company/trainees', compact('items'));
    }

    public function form(int $studentId): void
    {
        $t = $this->trainee($studentId);
        $existing = Database::one("SELECT * FROM evaluations WHERE StudentID = ? AND PlacementID = ? AND EvaluatorType = 'Company'", [$studentId, $t['PlacementID']]);
        $this->view('company/evaluate', compact('t', 'existing'));
    }

    /** UC-12: one company evaluation per student per placement, score 0–100. */
    public function store(int $studentId): void
    {
        $t = $this->trainee($studentId);
        $d = $this->validate(['score' => 'required|integer|between:0,100', 'comments' => 'nullable|max:3000']);
        try {
            $id = \Evaluation::insert([
                'StudentID' => $studentId, 'PlacementID' => $t['PlacementID'], 'EvaluatorUserID' => Auth::id(),
                'EvaluatorType' => 'Company', 'Score' => (int) $d['score'], 'Comments' => $d['comments'] ?: null,
            ]);
        } catch (\PDOException $e) {
            if ($e->getCode() !== '23000') {
                throw $e;
            }
            flash('error', __('eval.duplicate'));
            $this->redirect('/company/trainees');
        }
        \NotificationService::notify((int) $t['UserID'], __('n.evaluated_company'), '/student/evaluations');
        \AuditService::log('EVALUATION_COMPANY', 'Evaluation', $id);
        flash('success', __('eval.saved'));
        $this->redirect('/company/trainees');
    }

    private function trainee(int $studentId): array
    {
        $t = Database::one(
            'SELECT s.StudentID, s.PlacementID, u.FullName, u.UserID, s.Major, o.Title, p.Status AS PlacementStatus
               FROM students s JOIN users u ON u.UserID = s.UserID JOIN training_placements p ON p.PlacementID = s.PlacementID
               JOIN coop_opportunities o ON o.OpportunityID = p.OpportunityID
              WHERE s.StudentID = ? AND o.CompanyID = ?', [$studentId, $this->company()['CompanyID']]);
        if (!$t) {
            abort(404);
        }
        return $t;
    }
}
