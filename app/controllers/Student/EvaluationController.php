<?php
namespace Controllers\Student;

use Auth;
use Controller;
use Database;

class EvaluationController extends Controller
{
    public function index(): void
    {
        $s = Auth::profile();
        $evals = Database::all('SELECT e.*, u.FullName AS Evaluator FROM evaluations e JOIN users u ON u.UserID = e.EvaluatorUserID WHERE e.StudentID = ? ORDER BY e.EvaluatorType', [$s['StudentID']]);
        $summary = Database::one('SELECT ApprovedDays, PendingDays, RejectedDays FROM v_student_attendance_summary WHERE StudentID = ?', [$s['StudentID']]);
        $report = Database::one('SELECT ReviewStatus FROM final_reports WHERE StudentID = ?', [$s['StudentID']]);
        $this->view('student/evaluations', compact('s', 'evals', 'summary', 'report'));
    }
}
