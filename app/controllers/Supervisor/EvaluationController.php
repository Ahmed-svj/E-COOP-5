<?php
namespace Controllers\Supervisor;

use Auth;
use Database;

class EvaluationController extends Base
{
    public function form(int $id): void
    {
        $s = $this->myStudent($id);
        [$ok, $why] = $this->eligible($s);
        $existing = $s['PlacementID'] ? Database::one("SELECT * FROM evaluations WHERE StudentID = ? AND PlacementID = ? AND EvaluatorType = 'Academic'", [$id, $s['PlacementID']]) : null;
        $this->view('supervisor/evaluate', compact('s', 'ok', 'why', 'existing'));
    }

    /** UC-16: only after the placement is Completed and the report is approved. */
    public function store(int $id): void
    {
        $s = $this->myStudent($id);
        [$ok, $why] = $this->eligible($s);
        if (!$ok) {
            flash('error', $why);
            $this->redirect("/supervisor/evaluate/$id");
        }
        $d = $this->validate(['score' => 'required|integer|between:0,100', 'comments' => 'nullable|max:3000']);
        try {
            $eid = \Evaluation::insert([
                'StudentID' => $id, 'PlacementID' => $s['PlacementID'], 'EvaluatorUserID' => Auth::id(),
                'EvaluatorType' => 'Academic', 'Score' => (int) $d['score'], 'Comments' => $d['comments'] ?: null,
            ]);
        } catch (\PDOException $e) {
            if ($e->getCode() !== '23000') {
                throw $e;
            }
            flash('error', __('eval.duplicate'));
            $this->redirect("/supervisor/students/$id");
        }
        \NotificationService::notify((int) $s['UserID'], __('n.evaluated_academic'), '/student/evaluations');
        \AuditService::log('EVALUATION_ACADEMIC', 'Evaluation', $eid);
        flash('success', __('eval.saved'));
        $this->redirect("/supervisor/students/$id");
    }

    private function eligible(array $s): array
    {
        if (!$s['PlacementID']) {
            return [false, __('eval.no_placement')];
        }
        $p = \Placement::find((int) $s['PlacementID']);
        if ($p['Status'] !== 'Completed') {
            return [false, __('eval.not_completed')];
        }
        $rs = Database::value('SELECT ReviewStatus FROM final_reports WHERE StudentID = ?', [$s['StudentID']]);
        if ($rs !== 'Approved') {
            return [false, __('eval.report_first')];
        }
        return [true, null];
    }
}
