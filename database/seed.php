<?php
/**
 * Demo data for development and the capstone demo. Run AFTER schema.sql:
 *   php database/seed.php
 * Every demo account uses the password  Coop2026!
 */
declare(strict_types=1);
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/bootstrap.php';

if ((int) Database::value('SELECT COUNT(*) FROM users') > 0) {
    fwrite(STDERR, "The database already has users. Re-import schema.sql first for a clean seed.\n");
    exit(1);
}

$pw = password_hash('Coop2026!', PASSWORD_BCRYPT);
$user = function (string $name, string $email, string $role, ?string $phone = null, ?string $pass = null) use ($pw): int {
    return User::insert(['FullName' => $name, 'Email' => $email, 'Phone' => $phone, 'PasswordHash' => $pass ?? $pw, 'Role' => $role,
        'PrivacyAcceptedAt' => date('Y-m-d H:i:s'), 'LastLoginAt' => date('Y-m-d H:i:s', strtotime('-' . random_int(1, 72) . ' hours'))]);
};
$cv = function (string $title): string {
    // A tiny valid PDF so downloads work in the demo.
    $name = bin2hex(random_bytes(16)) . '.pdf';
    $text = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj 2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj "
        . "3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 595 842]/Contents 4 0 R/Resources<</Font<</F1 5 0 R>>>>>>endobj "
        . '4 0 obj<</Length ' . (strlen($title) + 30) . ">>stream\nBT /F1 24 Tf 72 760 Td (" . $title . ") Tj ET\nendstream endobj "
        . "5 0 obj<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF\n";
    file_put_contents(BASE_PATH . '/storage/uploads/' . (str_contains($title, 'Report') ? 'reports' : 'cv') . '/' . $name, $text);
    return (str_contains($title, 'Report') ? 'reports' : 'cv') . '/' . $name;
};

Database::transaction(function () use ($user, $cv) {
    // Admin
    $adminUser = $user('Hessa Al-Dossary', 'admin@ecoop.test', 'Admin', '0551000001');
    $adminId = Admin::insert(['UserID' => $adminUser]);

    // Supervisors
    $sup1User = $user('Dr. Khalid Omar', 'supervisor@ecoop.test', 'Supervisor', '0551000002');
    $sup1 = AcademicSupervisor::insert(['UserID' => $sup1User, 'Department' => 'Management Information Systems', 'MaxStudentLoad' => 20]);
    $sup2User = $user('Dr. Noura Al-Shehri', 'noura@ecoop.test', 'Supervisor', '0551000003');
    $sup2 = AcademicSupervisor::insert(['UserID' => $sup2User, 'Department' => 'Computer Science', 'MaxStudentLoad' => 3]);

    // Companies
    $c1User = $user('Omar Al-Zahrani', 'company@ecoop.test', 'Company', '0551000010');
    $c1 = Company::insert(['UserID' => $c1User, 'CompanyName' => 'Nova Systems', 'City' => 'Jubail', 'ApprovalStatus' => 'Approved', 'ApprovedByAdminID' => $adminId]);
    $c2User = $user('Lama Al-Otaibi', 'maaden@ecoop.test', 'Company', '0551000011');
    $c2 = Company::insert(['UserID' => $c2User, 'CompanyName' => "Ma'aden", 'City' => 'Ras Al-Khair', 'ApprovalStatus' => 'Approved', 'ApprovedByAdminID' => $adminId]);
    $c3User = $user('Yousef Al-Ghamdi', 'gulftech@ecoop.test', 'Company', '0551000012');
    Company::insert(['UserID' => $c3User, 'CompanyName' => 'Gulf Tech Services', 'City' => 'Dammam']);

    // Opportunities
    $desc = fn (string $s) => $s . "\n\nYou will work with a mentor four days a week (Sun–Thu, 7:30–15:30), join team meetings, and present a short project at the end. Bring your own laptop; tools and accounts are provided.";
    $o1 = Opportunity::insert(['CompanyID' => $c1, 'Title' => 'Backend Developer Intern', 'Description' => $desc('Build and test REST APIs in PHP and MySQL for our logistics platform, with code reviews from senior engineers.'),
        'MajorRequirements' => 'Computer Science', 'City' => 'Jubail', 'Capacity' => 3, 'Status' => 'Approved', 'ApprovedByAdminID' => $adminId, 'PostedDate' => date('Y-m-d', strtotime('-50 days'))]);
    $o2 = Opportunity::insert(['CompanyID' => $c1, 'Title' => 'IT Support Co-op', 'Description' => $desc('Support 400+ users: tickets, device setup, Active Directory and network basics.'),
        'MajorRequirements' => 'Information Technology', 'City' => 'Jubail', 'Capacity' => 2, 'Status' => 'Approved', 'ApprovedByAdminID' => $adminId, 'PostedDate' => date('Y-m-d', strtotime('-40 days'))]);
    $o3 = Opportunity::insert(['CompanyID' => $c2, 'Title' => 'ERP Support Co-op', 'Description' => $desc('Help the SAP team test changes, write user guides and clean master data.'),
        'MajorRequirements' => 'Management Information Systems', 'City' => 'Ras Al-Khair', 'Capacity' => 2, 'Status' => 'Approved', 'ApprovedByAdminID' => $adminId, 'PostedDate' => date('Y-m-d', strtotime('-120 days'))]);
    Opportunity::insert(['CompanyID' => $c2, 'Title' => 'Process Engineering Trainee', 'Description' => $desc('Shadow process engineers in the phosphate plant and help with daily reports.'),
        'MajorRequirements' => 'Chemical Engineering', 'City' => 'Ras Al-Khair', 'Capacity' => 4, 'Status' => 'Pending Approval']);
    Opportunity::insert(['CompanyID' => $c2, 'Title' => 'Cybersecurity Analyst Trainee', 'Description' => $desc('Monitor alerts with the SOC team and help run phishing-awareness campaigns.'),
        'MajorRequirements' => 'Cybersecurity', 'City' => 'Dammam', 'Capacity' => 2, 'Status' => 'Approved', 'ApprovedByAdminID' => $adminId, 'PostedDate' => date('Y-m-d', strtotime('-5 days'))]);

    // Placements
    $start = date('Y-m-d', strtotime('sunday -4 weeks'));
    $p1 = Placement::insert(['OpportunityID' => $o1, 'StartDate' => $start, 'EndDate' => date('Y-m-d', strtotime($start . ' +10 weeks'))]);
    $p2 = Placement::insert(['OpportunityID' => $o2, 'StartDate' => $start, 'EndDate' => date('Y-m-d', strtotime($start . ' +10 weeks'))]);
    $p3 = Placement::insert(['OpportunityID' => $o3, 'StartDate' => date('Y-m-d', strtotime('-110 days')), 'EndDate' => date('Y-m-d', strtotime('-10 days')), 'Status' => 'Completed']);

    // Students
    $mk = function (string $name, string $email, string $iid, string $major, string $gpa, ?int $sup, ?int $placement, string $phone) use ($user, $cv): array {
        $uid = $user($name, $email, 'Student', $phone);
        $sid = Student::insert(['UserID' => $uid, 'InstitutionalID' => $iid, 'Major' => $major, 'GPA' => $gpa, 'SupervisorID' => $sup,
            'PlacementID' => $placement, 'CVFilePath' => $cv('CV - ' . $name)]);
        return [$sid, $uid];
    };
    [$mohammed, $mohammedU] = $mk('Mohammed Ali Al-Qahtani', 'student@ecoop.test', '441001', 'Computer Science', '4.21', $sup1, $p1, '0552000001');
    [$sara, $saraU] = $mk('Sara Al-Mutairi', 'newstudent@ecoop.test', '441002', 'Computer Science', '4.65', null, null, '0552000002');
    [$abdullah] = $mk('Abdullah Al-Harbi', 'abdullah@ecoop.test', '441003', 'Information Technology', '3.80', null, $p2, '0552000003');
    [$khalid, $khalidU] = $mk('Yazan Al-Shammari', 'yazan@ecoop.test', '441004', 'Management Information Systems', '4.02', $sup1, $p3, '0552000004');
    [$reem] = $mk('Reem Al-Anazi', 'reem@ecoop.test', '441005', 'Cybersecurity', '4.40', null, null, '0552000005');
    [$lina] = $mk('Lina Al-Dosari', 'lina@ecoop.test', '441006', 'Computer Science', '4.10', null, null, '0552000006');

    // Applications
    $app = fn (int $s, int $o, string $st, string $ago, ?string $reason = null) => Application::insert(['StudentID' => $s, 'OpportunityID' => $o, 'Status' => $st, 'SubmittedDate' => date('Y-m-d', strtotime($ago)), 'RejectionReason' => $reason]);
    $app($mohammed, $o1, 'Accepted', '-36 days');
    $app($mohammed, $o3, 'Withdrawn', '-37 days');
    $app($abdullah, $o2, 'Accepted', '-33 days');
    $app($khalid, $o3, 'Accepted', '-115 days');
    $app($lina, $o1, 'Pending', '-2 days');
    $app($reem, $o1, 'Pending', '-1 day');

    // Daily codes + attendance for past working days
    $attend = function (int $placementId, array $studentIds, string $from, string $to, bool $leavePending) {
        $d = new DateTime($from);
        $end = new DateTime($to);
        while ($d < $end) {
            if ((int) $d->format('w') <= 4) {
                $date = $d->format('Y-m-d');
                Database::query("INSERT INTO daily_codes (PlacementID, Code, CodeDate, ExpiryTime, IsActive, Status) VALUES (?, ?, ?, ?, 0, 'Expired')",
                    [$placementId, str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT), $date, "$date 23:59:59"]);
                $code = (int) Database::connect()->lastInsertId();
                $recent = $d > new DateTime('-3 days');
                foreach ($studentIds as $sid) {
                    if (random_int(1, 10) === 1 && !$recent) {
                        continue; // absent
                    }
                    $status = ($leavePending && $recent) ? 'Pending' : (random_int(1, 25) === 1 ? 'Rejected' : 'Approved');
                    Database::query('INSERT INTO attendance_records (StudentID, PlacementID, DailyCodeID, CheckInTime, ApprovalStatus, ReviewComment, ReviewedAt) VALUES (?, ?, ?, ?, ?, ?, ?)',
                        [$sid, $placementId, $code, $date . ' 07:' . str_pad((string) random_int(30, 59), 2, '0', STR_PAD_LEFT) . ':00', $status,
                         $status === 'Rejected' ? 'Company reported you left at 10:00.' : null, $status === 'Pending' ? null : "$date 18:00:00"]);
                }
            }
            $d->modify('+1 day');
        }
    };
    $attend($p1, [$mohammed], $start, 'today', true);
    $attend($p2, [$abdullah], $start, 'today', true);
    $attend($p3, [$khalid], date('Y-m-d', strtotime('-110 days')), date('Y-m-d', strtotime('-10 days')), false);

    // Khalid: report approved, company evaluated, academic evaluation pending (supervisor can evaluate)
    FinalReport::insert(['StudentID' => $khalid, 'PlacementID' => $p3, 'SupervisorID' => $sup1, 'FilePath' => $cv('Final Report - Yazan'),
        'SubmittedAt' => date('Y-m-d H:i:s', strtotime('-8 days')), 'ReviewStatus' => 'Approved', 'ReviewComments' => 'Clear structure and good reflection on the SAP work.']);
    Evaluation::insert(['StudentID' => $khalid, 'PlacementID' => $p3, 'EvaluatorUserID' => $c2User, 'EvaluatorType' => 'Company', 'Score' => 91,
        'Comments' => 'Reliable, learned SAP quickly, wrote excellent user guides.', 'EvaluatedAt' => date('Y-m-d', strtotime('-9 days'))]);

    // Notifications
    $n = fn (int $u, string $m, string $l, int $read = 0) => Database::query('INSERT INTO notifications (UserID, Message, Link, IsRead, SentDate) VALUES (?, ?, ?, ?, NOW() - INTERVAL ? MINUTE)', [$u, $m, $l, $read, random_int(5, 3000)]);
    $n($mohammedU, 'Your application for “Backend Developer Intern” was accepted.', '/student/applications', 1);
    $n($mohammedU, 'Dr. Khalid Omar is now your academic supervisor.', '/student/dashboard');
    $n($sup1User, 'Mohammed Ali Al-Qahtani checked in and is waiting for review.', '/supervisor/attendance');
    $n($c1User, 'Lina Al-Dosari applied to “Backend Developer Intern”.', '/company/applicants?opportunity=' . $o1);
    $n($adminUser, 'New company to review: Gulf Tech Services.', '/admin/companies');
    $n($adminUser, 'A student was placed and needs an academic supervisor.', '/admin/assign');
    $n($khalidU, 'Your company evaluation is ready.', '/student/evaluations');

    // A conversation
    Database::query("INSERT INTO conversations (Type) VALUES ('Direct')");
    $conv = (int) Database::connect()->lastInsertId();
    Database::query('INSERT INTO conversation_members (ConversationID, UserID, LastReadAt) VALUES (?, ?, NOW() - INTERVAL 1 HOUR), (?, ?, NOW())', [$conv, $mohammedU, $conv, $sup1User]);
    $msg = fn (int $u, string $b, int $ago) => Database::query('INSERT INTO messages (ConversationID, SenderUserID, Body, SentAt) VALUES (?, ?, ?, NOW() - INTERVAL ? MINUTE)', [$conv, $u, $b, $ago]);
    $msg($mohammedU, 'Good morning Dr. Khalid, I started at Nova Systems this week. Is there a template for the final report?', 2900);
    $msg($sup1User, 'Welcome aboard, Mohammed. Use the college template on the co-op page; keep it under 20 pages.', 2850);
    $msg($sup1User, 'Remember to check in every day — I review attendance on Thursdays.', 30);

    ContactMessage::insert(['SenderName' => 'Fahad Al-Rashid', 'SenderEmail' => 'fahad@example.com', 'Topic' => 'Partnership', 'Body' => 'We are a logistics company in Jubail and would like to offer co-op seats next term. How do we start?']);

    AuditService::log('SEED', null, null, 'Demo data loaded', $adminUser);
});

echo "Seed complete. Every demo account uses the password Coop2026!\n";
echo "  admin@ecoop.test        COOP office admin\n";
echo "  supervisor@ecoop.test   Academic supervisor (Dr. Khalid Omar)\n";
echo "  company@ecoop.test      Company (Nova Systems)\n";
echo "  student@ecoop.test      Student placed in Backend Developer Intern\n";
echo "  newstudent@ecoop.test   Student with no placement (try applying)\n";
