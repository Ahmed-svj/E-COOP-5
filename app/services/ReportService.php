<?php
class ReportService
{
    public static function adminKpis(): array
    {
        return [
            'pending_companies'     => (int) Database::value("SELECT COUNT(*) FROM companies WHERE ApprovalStatus = 'Pending'"),
            'pending_supervisors'   => (int) Database::value("SELECT COUNT(*) FROM academic_supervisors WHERE ApprovalStatus = 'Pending'"),
            'pending_opportunities' => (int) Database::value("SELECT COUNT(*) FROM coop_opportunities WHERE Status = 'Pending Approval'"),
            'active_placements'     => (int) Database::value("SELECT COUNT(*) FROM students s JOIN training_placements p ON p.PlacementID = s.PlacementID WHERE p.Status = 'Active'"),
            'unassigned'            => (int) Database::value('SELECT COUNT(*) FROM students WHERE PlacementID IS NOT NULL AND SupervisorID IS NULL'),
            'students'              => (int) Database::value('SELECT COUNT(*) FROM students'),
            'placed'                => (int) Database::value('SELECT COUNT(*) FROM students WHERE PlacementID IS NOT NULL'),
        ];
    }

    public static function applicationsByStatus(): array
    {
        return Database::all('SELECT Status, COUNT(*) AS n FROM applications GROUP BY Status ORDER BY n DESC');
    }

    public static function placementsByCity(): array
    {
        return Database::all(
            'SELECT o.City, COUNT(s.StudentID) AS n FROM students s
               JOIN training_placements p ON p.PlacementID = s.PlacementID
               JOIN coop_opportunities o ON o.OpportunityID = p.OpportunityID
              GROUP BY o.City ORDER BY n DESC'
        );
    }

    public static function opportunitiesByMajor(): array
    {
        return Database::all("SELECT MajorRequirements AS Major, COUNT(*) AS n FROM coop_opportunities WHERE Status = 'Approved' GROUP BY MajorRequirements ORDER BY n DESC LIMIT 8");
    }

    public static function averageScores(): array
    {
        return Database::all('SELECT EvaluatorType, ROUND(AVG(Score), 1) AS avg, COUNT(*) AS n FROM evaluations GROUP BY EvaluatorType');
    }

    /** Supervisor cohort rows (used for the table and CSV export). */
    public static function cohort(int $supervisorId): array
    {
        return Database::all(
            "SELECT s.StudentID, u.FullName, s.InstitutionalID, s.Major, c.CompanyName, p.Status AS PlacementStatus,
                    SUM(ar.ApprovalStatus = 'Approved') AS Approved,
                    SUM(ar.ApprovalStatus = 'Pending') AS Pending,
                    SUM(ar.ApprovalStatus = 'Rejected') AS Rejected,
                    (SELECT ReviewStatus FROM final_reports fr WHERE fr.StudentID = s.StudentID) AS ReportStatus,
                    (SELECT Score FROM evaluations e WHERE e.StudentID = s.StudentID AND e.EvaluatorType = 'Company' LIMIT 1) AS CompanyScore,
                    (SELECT Score FROM evaluations e WHERE e.StudentID = s.StudentID AND e.EvaluatorType = 'Academic' LIMIT 1) AS AcademicScore
               FROM students s
               JOIN users u ON u.UserID = s.UserID
               LEFT JOIN training_placements p ON p.PlacementID = s.PlacementID
               LEFT JOIN coop_opportunities o ON o.OpportunityID = p.OpportunityID
               LEFT JOIN companies c ON c.CompanyID = o.CompanyID
               LEFT JOIN attendance_records ar ON ar.StudentID = s.StudentID
              WHERE s.SupervisorID = ?
              GROUP BY s.StudentID, u.FullName, s.InstitutionalID, s.Major, c.CompanyName, p.Status
              ORDER BY u.FullName",
            [$supervisorId]
        );
    }

    public static function csv(string $filename, array $header, array $rows): never
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows Arabic correctly
        fputcsv($out, $header);
        foreach ($rows as $r) {
            // Neutralise spreadsheet formulas (CSV injection).
            fputcsv($out, array_map(fn ($v) => is_string($v) && preg_match('/^[=+\-@]/', $v) ? "'" . $v : $v, array_values($r)));
        }
        fclose($out);
        exit;
    }
}
