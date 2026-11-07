<?php
namespace Controllers;

use AuditService;
use Auth;
use Controller;
use Database;
use NotificationService;
use Session;

class AuthController extends Controller
{
    public function loginForm(): void
    {
        $this->view('auth/login', [], 'main');
    }

    public function login(): void
    {
        $email = strtolower($this->input('email'));
        $error = Auth::attempt($email, (string) ($_POST['password'] ?? ''));
        if ($error) {
            Session::flash('old', ['email' => $email]);
            flash('error', $error);
            $this->redirect('/login');
        }
        $intended = Session::get('intended');
        Session::forget('intended');
        $this->redirect($intended && str_starts_with($intended, '/') && !str_starts_with($intended, '//') ? $intended : '/dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        flash('success', __('auth.logged_out'));
        $this->redirect('/login');
    }

    public function studentForm(): void
    {
        $this->view('auth/register-student', [], 'main');
    }

    public function registerStudent(): void
    {
        $_POST['email'] = strtolower(trim($_POST['email'] ?? ''));
        $d = $this->validate([
            'full_name'        => 'required|min:3|max:150',
            'email'            => 'required|email|student_email|max:190|unique:users,Email',
            'institutional_id' => 'required|max:20|unique:students,InstitutionalID',
            'major'            => 'required|max:150',
            'phone'            => 'required|phone',
            'password'         => 'required|password|confirmed',
            'privacy'          => 'required',
        ]);

        $userId = Database::transaction(function () use ($d) {
            $uid = \User::insert([
                'FullName' => $d['full_name'], 'Email' => $d['email'], 'Phone' => $d['phone'],
                'PasswordHash' => password_hash($d['password'], PASSWORD_BCRYPT), 'Role' => 'Student',
                'PrivacyAcceptedAt' => date('Y-m-d H:i:s'),
            ]);
            \Student::insert(['UserID' => $uid, 'InstitutionalID' => $d['institutional_id'], 'Major' => $d['major']]);
            return $uid;
        });
        AuditService::log('REGISTER_STUDENT', 'User', $userId, null, $userId);
        Auth::login($userId);
        flash('success', __('auth.welcome_student'));
        $this->redirect('/student/profile');
    }

    public function companyForm(): void
    {
        $this->view('auth/register-company', [], 'main');
    }

    public function registerCompany(): void
    {
        $_POST['email'] = strtolower(trim($_POST['email'] ?? ''));
        $d = $this->validate([
            'company_name' => 'required|min:2|max:190',
            'full_name'    => 'required|min:3|max:150',
            'email'        => 'required|email|max:190|unique:users,Email',
            'phone'        => 'required|phone',
            'city'         => 'required|max:100',
            'password'     => 'required|password|confirmed',
            'privacy'      => 'required',
        ]);
        $userId = Database::transaction(function () use ($d) {
            $uid = \User::insert([
                'FullName' => $d['full_name'], 'Email' => $d['email'], 'Phone' => $d['phone'],
                'PasswordHash' => password_hash($d['password'], PASSWORD_BCRYPT), 'Role' => 'Company',
                'PrivacyAcceptedAt' => date('Y-m-d H:i:s'),
            ]);
            \Company::insert(['UserID' => $uid, 'CompanyName' => $d['company_name'], 'City' => $d['city']]);
            return $uid;
        });
        AuditService::log('REGISTER_COMPANY', 'User', $userId, null, $userId);
        NotificationService::admins(__('n.company_registered', ['name' => $d['company_name']]), '/admin/companies');
        Auth::login($userId);
        flash('success', __('auth.welcome_company'));
        $this->redirect('/company/dashboard');
    }

    public function supervisorForm(): void
    {
        $this->view('auth/register-supervisor', [], 'main');
    }

    /** Academic supervisors sign up with a college email; the co-op office approves them before students are assigned. */
    public function registerSupervisor(): void
    {
        $_POST['email'] = strtolower(trim($_POST['email'] ?? ''));
        $d = $this->validate([
            'full_name'  => 'required|min:3|max:150',
            'email'      => 'required|email|student_email|max:190|unique:users,Email',
            'department' => 'required|max:150',
            'phone'      => 'required|phone',
            'password'   => 'required|password|confirmed',
            'privacy'    => 'required',
        ]);
        $userId = Database::transaction(function () use ($d) {
            $uid = \User::insert([
                'FullName' => $d['full_name'], 'Email' => $d['email'], 'Phone' => $d['phone'],
                'PasswordHash' => password_hash($d['password'], PASSWORD_BCRYPT), 'Role' => 'Supervisor',
                'PrivacyAcceptedAt' => date('Y-m-d H:i:s'),
            ]);
            \AcademicSupervisor::insert(['UserID' => $uid, 'Department' => $d['department'], 'ApprovalStatus' => 'Pending']);
            return $uid;
        });
        AuditService::log('REGISTER_SUPERVISOR', 'User', $userId, null, $userId);
        NotificationService::admins(__('n.supervisor_registered', ['name' => $d['full_name']]), '/admin/users?role=Supervisor');
        Auth::login($userId);
        flash('success', __('auth.welcome_supervisor'));
        $this->redirect('/supervisor/dashboard');
    }

    public function forgotForm(): void
    {
        $this->view('auth/forgot-password', [], 'main');
    }

    public function forgot(): void
    {
        $email = strtolower($this->input('email'));
        $user = \User::findByEmail($email);
        if ($user && $user['AccountStatus'] === 'Active') {
            $token = bin2hex(random_bytes(32));
            Database::query('UPDATE password_resets SET UsedAt = NOW() WHERE UserID = ? AND UsedAt IS NULL', [$user['UserID']]);
            Database::query(
                'INSERT INTO password_resets (UserID, TokenHash, ExpiresAt) VALUES (?, ?, NOW() + INTERVAL ? MINUTE)',
                [$user['UserID'], hash('sha256', $token), config('reset_valid_min')]
            );
            \MailService::send($email, __('reset.subject'), __('reset.body', ['link' => full_url('/reset-password', ['token' => $token])]));
            AuditService::log('PASSWORD_RESET_REQUESTED', 'User', (int) $user['UserID'], null, (int) $user['UserID']);
        }
        // Same answer either way so emails can't be enumerated.
        flash('success', __('reset.sent'));
        $this->redirect('/forgot-password');
    }

    public function resetForm(): void
    {
        $token = (string) ($_GET['token'] ?? '');
        if (!$this->validToken($token)) {
            flash('error', __('reset.invalid'));
            $this->redirect('/forgot-password');
        }
        $this->view('auth/reset-password', ['token' => $token], 'main');
    }

    public function reset(): void
    {
        $token = (string) ($_POST['token'] ?? '');
        $row = $this->validToken($token);
        if (!$row) {
            flash('error', __('reset.invalid'));
            $this->redirect('/forgot-password');
        }
        $d = $this->validate(['password' => 'required|password|confirmed']);
        Database::transaction(function () use ($row, $d) {
            Database::query('UPDATE users SET PasswordHash = ?, FailedLoginCount = 0, LockoutUntil = NULL WHERE UserID = ?',
                [password_hash($d['password'], PASSWORD_BCRYPT), $row['UserID']]);
            Database::query('UPDATE password_resets SET UsedAt = NOW() WHERE ResetID = ?', [$row['ResetID']]);
        });
        AuditService::log('PASSWORD_RESET', 'User', (int) $row['UserID'], null, (int) $row['UserID']);
        flash('success', __('reset.done'));
        $this->redirect('/login');
    }

    private function validToken(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }
        return Database::one('SELECT * FROM password_resets WHERE TokenHash = ? AND UsedAt IS NULL AND ExpiresAt > NOW()', [hash('sha256', $token)]);
    }
}
