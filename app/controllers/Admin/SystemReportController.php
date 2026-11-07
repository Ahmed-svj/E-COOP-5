<?php
namespace Controllers\Admin;

use Database;

/** One page with everything the co-op office needs to know about the site. */
class SystemReportController extends Base
{
    public function index(): void
    {
        $this->view('admin/report', ['r' => self::collect()]);
    }

    public function csv(): void
    {
        $r = self::collect();
        $rows = [];
        foreach ($r['sections'] as $section => $pairs) {
            foreach ($pairs as $label => $value) {
                $rows[] = [__($section), __($label), is_array($value) ? implode(' / ', $value) : $value];
            }
        }
        foreach ($r['todo'] as $t) {
            $rows[] = [__('report.todo'), __($t['label']), $t['n']];
        }
        \AuditService::log('SYSTEM_REPORT_EXPORTED', null, null);
        \ReportService::csv('coop-system-report-' . date('Y-m-d') . '.csv', [__('report.section'), __('report.item'), __('report.value')], $rows);
    }

    public static function collect(): array
    {
        $n = fn (string $sql, array $p = []) => (int) Database::value($sql, $p);
        $by = function (string $sql): array {
            $out = [];
            foreach (Database::all($sql) as $row) {
                $out[$row['k']] = (int) $row['n'];
            }
            return $out;
        };
        $users = $by('SELECT Role AS k, COUNT(*) AS n FROM users GROUP BY Role');
        $att = $by('SELECT ApprovalStatus AS k, COUNT(*) AS n FROM attendance_records GROUP BY ApprovalStatus');
        $apps = $by('SELECT Status AS k, COUNT(*) AS n FROM applications GROUP BY Status');
        $opps = $by('SELECT Status AS k, COUNT(*) AS n FROM coop_opportunities GROUP BY Status');
        $comps = $by('SELECT ApprovalStatus AS k, COUNT(*) AS n FROM companies GROUP BY ApprovalStatus');
        $reports = $by('SELECT ReviewStatus AS k, COUNT(*) AS n FROM final_reports GROUP BY ReviewStatus');
        $students = $users['Student'] ?? 0;
        $placed = $n('SELECT COUNT(*) FROM students WHERE PlacementID IS NOT NULL');
        $attTotal = array_sum($att);
        $avg = $by("SELECT EvaluatorType AS k, ROUND(AVG(Score)) AS n FROM evaluations GROUP BY EvaluatorType");
        $dbSize = (float) Database::value("SELECT ROUND(SUM(data_length + index_length) / 1048576, 2) FROM information_schema.tables WHERE table_schema = DATABASE()");
        $storage = 0; $files = 0;
        foreach (['cv', 'reports', 'avatars'] as $d) {
            foreach (glob(BASE_PATH . "/storage/uploads/$d/*.*") ?: [] as $f) { $storage += filesize($f); $files++; }
        }
        $backups = glob(BASE_PATH . '/storage/backups/*.sql.gz') ?: [];
        $lastBackup = $backups ? date('Y-m-d H:i', max(array_map('filemtime', $backups))) : null;
        $scheduler = (string) (Database::one("SHOW VARIABLES LIKE 'event_scheduler'")['Value'] ?? '?');
        $loads = Database::all("SELECT * FROM v_supervisor_load WHERE ApprovalStatus = 'Approved' ORDER BY CurrentLoad DESC");

        $todo = array_values(array_filter([
            ['label' => 'report.t_companies', 'n' => $comps['Pending'] ?? 0, 'href' => '/admin/companies'],
            ['label' => 'report.t_supervisors', 'n' => $n("SELECT COUNT(*) FROM academic_supervisors WHERE ApprovalStatus = 'Pending'"), 'href' => '/admin/users?role=Supervisor'],
            ['label' => 'report.t_opps', 'n' => $opps['Pending Approval'] ?? 0, 'href' => '/admin/opportunities'],
            ['label' => 'report.t_unassigned', 'n' => $n('SELECT COUNT(*) FROM students WHERE PlacementID IS NOT NULL AND SupervisorID IS NULL'), 'href' => '/admin/assign'],
            ['label' => 'report.t_overdue', 'n' => $n("SELECT COUNT(*) FROM training_placements WHERE Status = 'Active' AND EndDate < CURDATE()"), 'href' => '/admin/placements'],
            ['label' => 'report.t_full_sups', 'n' => count(array_filter($loads, fn ($l) => $l['CurrentLoad'] >= $l['MaxStudentLoad'])), 'href' => '/admin/assign'],
            ['label' => 'report.t_contact', 'n' => $n('SELECT COUNT(*) FROM contact_messages WHERE SentAt >= NOW() - INTERVAL 7 DAY'), 'href' => '/admin/contact-messages'],
            ['label' => 'report.t_att_pending', 'n' => $att['Pending'] ?? 0, 'href' => '/admin/placements'],
            ['label' => 'report.t_reports_pending', 'n' => $reports['Pending'] ?? 0, 'href' => '/admin/placements'],
        ], fn ($t) => $t['n'] > 0));

        return [
            'generated' => date('Y-m-d H:i'),
            'headline' => [
                'report.h_students' => $students,
                'report.h_placed_pct' => $students ? (int) round($placed * 100 / $students) : 0,
                'report.h_att_rate' => $attTotal ? (int) round(($att['Approved'] ?? 0) * 100 / $attTotal) : 0,
                'report.h_avg' => count($avg) ? (int) round(array_sum($avg) / count($avg)) : 0,
            ],
            'todo' => $todo,
            'loads' => $loads,
            'top_companies' => Database::all(
                "SELECT c.CompanyName, COUNT(DISTINCT o.OpportunityID) AS Opps, COUNT(DISTINCT s.StudentID) AS Trainees,
                        (SELECT COUNT(*) FROM applications a JOIN coop_opportunities o2 ON o2.OpportunityID = a.OpportunityID WHERE o2.CompanyID = c.CompanyID) AS Apps
                   FROM companies c LEFT JOIN coop_opportunities o ON o.CompanyID = c.CompanyID
                   LEFT JOIN training_placements p ON p.OpportunityID = o.OpportunityID LEFT JOIN students s ON s.PlacementID = p.PlacementID
                  WHERE c.ApprovalStatus = 'Approved' GROUP BY c.CompanyID, c.CompanyName ORDER BY Trainees DESC, Apps DESC LIMIT 5"),
            'sections' => [
                'report.s_users' => [
                    'report.u_total' => array_sum($users),
                    'role.Student' => $students, 'role.Company' => $users['Company'] ?? 0,
                    'role.Supervisor' => $users['Supervisor'] ?? 0, 'role.Admin' => $users['Admin'] ?? 0,
                    'report.u_suspended' => $n("SELECT COUNT(*) FROM users WHERE AccountStatus = 'Suspended'"),
                    'report.u_active7' => $n('SELECT COUNT(*) FROM users WHERE LastLoginAt >= NOW() - INTERVAL 7 DAY'),
                    'report.u_new30' => $n('SELECT COUNT(*) FROM users WHERE CreatedAt >= NOW() - INTERVAL 30 DAY'),
                ],
                'report.s_companies' => [
                    'status.Approved' => $comps['Approved'] ?? 0, 'status.Pending' => $comps['Pending'] ?? 0, 'status.Rejected' => $comps['Rejected'] ?? 0,
                ],
                'report.s_opps' => [
                    'status.Approved' => $opps['Approved'] ?? 0, 'status.Pending Approval' => $opps['Pending Approval'] ?? 0,
                    'status.Rejected' => $opps['Rejected'] ?? 0, 'status.Closed' => $opps['Closed'] ?? 0,
                    'report.o_seats' => $n("SELECT COALESCE(SUM(Capacity), 0) FROM coop_opportunities WHERE Status = 'Approved'"),
                    'report.o_seats_free' => $n("SELECT COALESCE(SUM(v.Capacity - v.AcceptedCount), 0) FROM v_public_opportunities v"),
                ],
                'report.s_apps' => [
                    'report.a_total' => array_sum($apps),
                    'status.Pending' => $apps['Pending'] ?? 0, 'status.Accepted' => $apps['Accepted'] ?? 0,
                    'status.Rejected' => $apps['Rejected'] ?? 0, 'status.Withdrawn' => $apps['Withdrawn'] ?? 0,
                    'report.a_per_student' => $students ? round(array_sum($apps) / $students, 1) : 0,
                ],
                'report.s_placements' => [
                    'status.Active' => $n("SELECT COUNT(*) FROM training_placements WHERE Status = 'Active'"),
                    'status.Completed' => $n("SELECT COUNT(*) FROM training_placements WHERE Status = 'Completed'"),
                    'report.p_placed' => $placed, 'report.p_not_placed' => $students - $placed,
                    'report.p_no_sup' => $n('SELECT COUNT(*) FROM students WHERE PlacementID IS NOT NULL AND SupervisorID IS NULL'),
                ],
                'report.s_attendance' => [
                    'report.at_total' => $attTotal,
                    'status.Approved' => $att['Approved'] ?? 0, 'status.Pending' => $att['Pending'] ?? 0, 'status.Rejected' => $att['Rejected'] ?? 0,
                    'report.at_today' => $n('SELECT COUNT(*) FROM attendance_records ar JOIN daily_codes dc ON dc.DailyCodeID = ar.DailyCodeID WHERE dc.CodeDate = CURDATE()'),
                    'report.at_codes_today' => $n('SELECT COUNT(*) FROM daily_codes WHERE CodeDate = CURDATE()'),
                ],
                'report.s_reports' => [
                    'report.r_submitted' => array_sum($reports),
                    'status.Pending' => $reports['Pending'] ?? 0, 'status.Approved' => $reports['Approved'] ?? 0,
                    'status.Revision Requested' => $reports['Revision Requested'] ?? 0,
                    'eval.Company' => $avg['Company'] ?? '—', 'eval.Academic' => $avg['Academic'] ?? '—',
                    'report.e_total' => $n('SELECT COUNT(*) FROM evaluations'),
                ],
                'report.s_security' => [
                    'report.sec_failed7' => $n("SELECT COUNT(*) FROM audit_logs WHERE Action = 'LOGIN_FAILED' AND Timestamp >= NOW() - INTERVAL 7 DAY"),
                    'report.sec_locked' => $n('SELECT COUNT(*) FROM users WHERE LockoutUntil > NOW()'),
                    'report.sec_lock_events' => $n("SELECT COUNT(*) FROM audit_logs WHERE Action = 'ACCOUNT_LOCKED' AND Timestamp >= NOW() - INTERVAL 30 DAY"),
                    'report.sec_code_locks' => $n("SELECT COUNT(*) FROM audit_logs WHERE Action = 'CHECKIN_LOCKED' AND Timestamp >= NOW() - INTERVAL 30 DAY"),
                    'report.sec_denied' => $n("SELECT COUNT(*) FROM audit_logs WHERE Action = 'ACCESS_DENIED' AND Timestamp >= NOW() - INTERVAL 30 DAY"),
                    'report.sec_audit_total' => $n('SELECT COUNT(*) FROM audit_logs'),
                ],
                'report.s_system' => [
                    'report.sys_php' => PHP_VERSION,
                    'report.sys_mysql' => (string) Database::value('SELECT VERSION()'),
                    'report.sys_db' => $dbSize . ' MB',
                    'report.sys_files' => $files . ' · ' . ($storage >= 1048576 ? round($storage / 1048576, 1) . ' MB' : max(1, (int) round($storage / 1024)) . ' KB'),
                    'report.sys_backup' => $lastBackup ?? __('report.never'),
                    'report.sys_scheduler' => $scheduler,
                    'report.sys_mail' => getenv('MAIL_DRIVER') ?: 'log',
                    'report.sys_debug' => config('debug') ? 'ON' : 'OFF',
                    'report.sys_https' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'ON' : 'OFF',
                ],
            ],
        ];
    }
}
