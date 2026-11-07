<?php
namespace Controllers\Admin;

use Auth;
use Database;

class UserController extends Base
{
    /** UC-18 */
    public function index(): void
    {
        $role = in_array($_GET['role'] ?? '', ['Student', 'Supervisor', 'Company', 'Admin'], true) ? $_GET['role'] : '';
        $q = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
        $where = ['1=1'];
        $params = [];
        if ($role) {
            $where[] = 'Role = ?';
            $params[] = $role;
        }
        if ($q !== '') {
            $where[] = '(FullName LIKE ? OR Email LIKE ?)';
            array_push($params, "%$q%", "%$q%");
        }
        [$offset, $per, $page] = paginate(20);
        $total = (int) Database::value('SELECT COUNT(*) FROM users WHERE ' . implode(' AND ', $where), $params);
        $users = Database::all('SELECT u.*, sp.SupervisorID, sp.ApprovalStatus AS SupApproval FROM users u LEFT JOIN academic_supervisors sp ON sp.UserID = u.UserID WHERE '
            . implode(' AND ', $where) . " ORDER BY (sp.ApprovalStatus = 'Pending') DESC, u.CreatedAt DESC LIMIT $per OFFSET $offset", $params);
        $this->view('admin/users', compact('users', 'role', 'q', 'total', 'page', 'per'));
    }

    public function show(int $id): void
    {
        $u = \User::find($id);
        if (!$u) {
            abort(404);
        }
        $profile = match ($u['Role']) {
            'Student'    => Database::one(
                "SELECT s.*, o.Title, c.CompanyName, p.StartDate, p.EndDate, p.Status AS PlacementStatus,
                        (SELECT u2.FullName FROM academic_supervisors sp JOIN users u2 ON u2.UserID = sp.UserID WHERE sp.SupervisorID = s.SupervisorID) AS SupervisorName,
                        (SELECT COUNT(*) FROM applications a WHERE a.StudentID = s.StudentID) AS Apps,
                        (SELECT COUNT(*) FROM attendance_records ar WHERE ar.StudentID = s.StudentID AND ar.ApprovalStatus = 'Approved') AS Days
                   FROM students s LEFT JOIN training_placements p ON p.PlacementID = s.PlacementID
                   LEFT JOIN coop_opportunities o ON o.OpportunityID = p.OpportunityID LEFT JOIN companies c ON c.CompanyID = o.CompanyID
                  WHERE s.UserID = ?", [$id]),
            'Company'    => Database::one(
                "SELECT c.*, (SELECT COUNT(*) FROM coop_opportunities o WHERE o.CompanyID = c.CompanyID) AS Opps,
                        (SELECT COUNT(*) FROM students s JOIN training_placements p ON p.PlacementID = s.PlacementID JOIN coop_opportunities o ON o.OpportunityID = p.OpportunityID WHERE o.CompanyID = c.CompanyID) AS Trainees
                   FROM companies c WHERE c.UserID = ?", [$id]),
            'Supervisor' => Database::one('SELECT l.*, sp.UserID FROM v_supervisor_load l JOIN academic_supervisors sp ON sp.SupervisorID = l.SupervisorID WHERE sp.UserID = ?', [$id]),
            default      => null,
        };
        $logs = Database::all('SELECT * FROM audit_logs WHERE UserID = ? ORDER BY AuditLogID DESC LIMIT 10', [$id]);
        $this->view('admin/user-details', compact('u', 'profile', 'logs'));
    }

    public function approveSupervisor(int $id): void
    {
        $sp = Database::one('SELECT sp.*, u.FullName FROM academic_supervisors sp JOIN users u ON u.UserID = sp.UserID WHERE sp.SupervisorID = ?', [$id]);
        if (!$sp) {
            abort(404);
        }
        $stmt = Database::query("UPDATE academic_supervisors SET ApprovalStatus = 'Approved', RejectionReason = NULL WHERE SupervisorID = ? AND ApprovalStatus = 'Pending'", [$id]);
        if ($stmt->rowCount() !== 1) {
            flash('error', __('err.not_pending'));
            $this->back();
        }
        \NotificationService::notify((int) $sp['UserID'], __('n.supervisor_approved'), '/supervisor/dashboard');
        \AuditService::log('SUPERVISOR_APPROVED', 'Supervisor', $id);
        flash('success', __('admin.supervisor_approved', ['name' => $sp['FullName']]));
        $this->back();
    }

    /** Rejecting a self-registered supervisor also turns the account off. */
    public function rejectSupervisor(int $id): void
    {
        $reason = mb_substr(trim((string) ($_POST['reason'] ?? '')), 0, 1000);
        if ($reason === '') {
            flash('error', __('app.reason_required'));
            $this->back();
        }
        $sp = Database::one('SELECT sp.*, u.FullName, u.Email FROM academic_supervisors sp JOIN users u ON u.UserID = sp.UserID WHERE sp.SupervisorID = ?', [$id]);
        if (!$sp) {
            abort(404);
        }
        $stmt = Database::query("UPDATE academic_supervisors SET ApprovalStatus = 'Rejected', RejectionReason = ? WHERE SupervisorID = ? AND ApprovalStatus = 'Pending'", [$reason, $id]);
        if ($stmt->rowCount() !== 1) {
            flash('error', __('err.not_pending'));
            $this->back();
        }
        Database::query("UPDATE users SET AccountStatus = 'Suspended' WHERE UserID = ?", [$sp['UserID']]);
        \MailService::send($sp['Email'], __('n.supervisor_rejected_subject'), __('n.supervisor_rejected', ['reason' => $reason]));
        \AuditService::log('SUPERVISOR_REJECTED', 'Supervisor', $id, $reason);
        flash('success', __('admin.supervisor_rejected', ['name' => $sp['FullName']]));
        $this->back();
    }

    public function toggle(int $id): void
    {
        if ($id === Auth::id()) {
            flash('error', __('admin.no_self_suspend'));
            $this->back();
        }
        $u = \User::find($id);
        if (!$u) {
            abort(404);
        }
        $new = $u['AccountStatus'] === 'Active' ? 'Suspended' : 'Active';
        \User::update($id, ['AccountStatus' => $new]);
        \AuditService::log($new === 'Suspended' ? 'USER_SUSPENDED' : 'USER_ACTIVATED', 'User', $id);
        flash('success', __($new === 'Suspended' ? 'admin.suspended' : 'admin.activated', ['name' => $u['FullName']]));
        $this->back();
    }

    public function createSupervisor(): void
    {
        $_POST['email'] = strtolower(trim($_POST['email'] ?? ''));
        $d = $this->validate([
            'full_name'  => 'required|min:3|max:150',
            'email'      => 'required|email|max:190|unique:users,Email',
            'department' => 'required|max:150',
            'max_load'   => 'required|integer|between:1,200',
            'password'   => 'required|password',
        ]);
        $uid = Database::transaction(function () use ($d) {
            $uid = \User::insert(['FullName' => $d['full_name'], 'Email' => $d['email'], 'PasswordHash' => password_hash($d['password'], PASSWORD_BCRYPT), 'Role' => 'Supervisor']);
            \AcademicSupervisor::insert(['UserID' => $uid, 'Department' => $d['department'], 'MaxStudentLoad' => (int) $d['max_load']]);
            return $uid;
        });
        \AuditService::log('SUPERVISOR_CREATED', 'User', $uid);
        flash('success', __('admin.supervisor_created'));
        $this->redirect('/admin/users?role=Supervisor');
    }
}
