<?php
namespace Controllers;

use Auth;
use Controller;
use Database;
use Upload;

class ProfileController extends Controller
{
    public function settings(): void
    {
        $prefs = [];
        foreach (\NotificationService::CATEGORIES as $key => $default) {
            $prefs[$key] = pref($key, $default);
        }
        $this->view('shared/settings', ['prefs' => $prefs, 'profile' => Auth::profile()]);
    }

    public function saveSettings(): void
    {
        $id = Auth::id();
        \UserPreference::set($id, 'email_notifications', !empty($_POST['email_notifications']));
        \UserPreference::set($id, 'dark_mode', !empty($_POST['dark_mode']));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        if ($phone !== '') {
            if (!preg_match('/^05[0-9]{8}$/', $phone)) {
                \Session::flash('errors', ['phone' => __('v.phone')]);
                $this->redirect('/settings');
            }
            Database::query('UPDATE users SET Phone = ? WHERE UserID = ?', [$phone, $id]);
        }
        flash('success', __('settings.saved'));
        $this->redirect('/settings');
    }

    public function changePassword(): void
    {
        $user = Auth::user();
        if (!password_verify((string) ($_POST['current_password'] ?? ''), $user['PasswordHash'])) {
            \Session::flash('errors', ['password_current' => __('settings.wrong_password')]);
            $this->redirect('/settings');
        }
        $d = $this->validate(['password' => 'required|password|confirmed']);
        Database::query('UPDATE users SET PasswordHash = ? WHERE UserID = ?', [password_hash($d['password'], PASSWORD_BCRYPT), $user['UserID']]);
        \Session::regenerate();
        \AuditService::log('PASSWORD_CHANGED', 'User', (int) $user['UserID']);
        flash('success', __('settings.password_changed'));
        $this->redirect('/settings');
    }

    /** Settings → Profile: the fields depend on the role. Changing the email needs the current password. */
    public function saveProfile(): void
    {
        $u = Auth::user();
        $role = $u['Role'];
        $p = Auth::profile();
        $_POST['email'] = strtolower(trim((string) ($_POST['email'] ?? '')));
        $rules = [
            'full_name' => 'required|min:3|max:150',
            'email'     => 'required|email|max:190' . ($role === 'Student' ? '|student_email' : ''),
            'phone'     => 'required|phone',
        ];
        $rules += match ($role) {
            'Student'    => ['gpa' => 'required|numeric|between:0,5', 'major' => 'required|max:150'],
            'Company'    => ['company_name' => 'required|min:2|max:190', 'city' => 'required|max:100'],
            'Supervisor' => ['department' => 'required|max:150'],
            default      => [],
        };
        $d = $this->validate($rules);

        $emailChanged = $d['email'] !== strtolower($u['Email']);
        if ($emailChanged) {
            $errors = [];
            if (Database::value('SELECT 1 FROM users WHERE Email = ? AND UserID <> ?', [$d['email'], $u['UserID']])) {
                $errors['email'] = __('v.unique');
            } elseif (!password_verify((string) ($_POST['current_password'] ?? ''), $u['PasswordHash'])) {
                $errors['current_password'] = __('settings.email_needs_password');
            }
            if ($errors) {
                \Session::flash('errors', $errors);
                \Session::flash('old', $d);
                $this->redirect('/settings');
            }
        }

        $cv = null;
        if ($role === 'Student' && !empty($_FILES['cv']) && $_FILES['cv']['error'] !== UPLOAD_ERR_NO_FILE) {
            [$cv, $err] = Upload::store($_FILES['cv'], 'cv');
            if ($err) {
                \Session::flash('errors', ['cv' => $err]);
                \Session::flash('old', $d);
                $this->redirect('/settings');
            }
        }

        Database::transaction(function () use ($u, $role, $p, $d, $cv) {
            Database::query('UPDATE users SET FullName = ?, Email = ?, Phone = ? WHERE UserID = ?', [$d['full_name'], $d['email'], $d['phone'], $u['UserID']]);
            match ($role) {
                'Student'    => Database::query('UPDATE students SET GPA = ?, Major = ?' . ($cv ? ', CVFilePath = ?' : '') . ' WHERE StudentID = ?',
                                    $cv ? [$d['gpa'], $d['major'], $cv, $p['StudentID']] : [$d['gpa'], $d['major'], $p['StudentID']]),
                'Company'    => Database::query('UPDATE companies SET CompanyName = ?, City = ? WHERE CompanyID = ?', [$d['company_name'], $d['city'], $p['CompanyID']]),
                'Supervisor' => Database::query('UPDATE academic_supervisors SET Department = ? WHERE SupervisorID = ?', [$d['department'], $p['SupervisorID']]),
                default      => null,
            };
        });
        if ($cv) {
            Upload::delete($p['CVFilePath'] ?? null);
        }
        \AuditService::log('PROFILE_UPDATED', 'User', (int) $u['UserID']);
        if ($emailChanged) {
            \Session::regenerate();
            \AuditService::log('EMAIL_CHANGED', 'User', (int) $u['UserID'], $u['Email'] . ' -> ' . $d['email']);
            \MailService::send($u['Email'], __('settings.email_changed_subject'), __('settings.email_changed_body', ['email' => $d['email']]));
            flash('success', __('profile.saved') . ' ' . __('settings.email_changed', ['email' => $d['email']]));
        } else {
            flash('success', __('profile.saved'));
        }
        $this->redirect('/settings');
    }

    /** Settings → Notifications: which emails to receive (saved as you flip them). */
    public function saveNotifications(): void
    {
        $id = Auth::id();
        foreach (array_keys(\NotificationService::CATEGORIES) as $key) {
            \UserPreference::set($id, $key, !empty($_POST[$key]));
        }
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $this->json(['ok' => true, 'message' => __('settings.saved')]);
        }
        flash('success', __('settings.saved'));
        $this->redirect('/settings');
    }

    public function removeCv(): void
    {
        $p = Auth::profile();
        if (Auth::role() !== 'Student' || empty($p['CVFilePath'])) {
            $this->redirect('/settings');
        }
        Upload::delete($p['CVFilePath']);
        Database::query('UPDATE students SET CVFilePath = NULL WHERE StudentID = ?', [$p['StudentID']]);
        \AuditService::log('CV_REMOVED', 'Student', (int) $p['StudentID']);
        flash('warning', __('settings.cv_removed'));
        $this->redirect('/settings');
    }

    /** Change the sign-in email: current password required, unique, students keep a college address. */
    public function changeEmail(): void
    {
        $user = Auth::user();
        if (!password_verify((string) ($_POST['email_password'] ?? ''), $user['PasswordHash'])) {
            \Session::flash('errors', ['email_password' => __('settings.wrong_password')]);
            \Session::flash('old', ['new_email' => $_POST['new_email'] ?? '']);
            $this->redirect('/settings');
        }
        $_POST['new_email'] = strtolower(trim((string) ($_POST['new_email'] ?? '')));
        $rules = 'required|email|max:190|unique:users,Email';
        if ($user['Role'] === 'Student') {
            $rules = 'required|email|student_email|max:190|unique:users,Email';
        }
        $d = $this->validate(['new_email' => $rules]);
        $old = $user['Email'];
        Database::query('UPDATE users SET Email = ? WHERE UserID = ?', [$d['new_email'], $user['UserID']]);
        \Session::regenerate();
        \AuditService::log('EMAIL_CHANGED', 'User', (int) $user['UserID'], $old . ' -> ' . $d['new_email']);
        \MailService::send($old, __('settings.email_changed_subject'), __('settings.email_changed_body', ['email' => $d['new_email']]));
        flash('success', __('settings.email_changed', ['email' => $d['new_email']]));
        $this->redirect('/settings');
    }

    public function avatar(): void
    {
        $ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);
        [$path, $err] = Upload::store($_FILES['avatar'] ?? null, 'avatars', Upload::IMAGES, 5 * 1024 * 1024);
        if ($err && $ajax) {
            $this->json(['ok' => false, 'error' => $err], 422);
        }
        if ($err) {
            flash('error', $err);
            $this->redirect('/settings');
        }
        Upload::delete(Auth::user()['AvatarPhoto']);
        Database::query('UPDATE users SET AvatarPhoto = ? WHERE UserID = ?', [$path, Auth::id()]);
        \AuditService::log('AVATAR_CHANGED', 'User', Auth::id());
        if ($ajax) {
            $this->json(['ok' => true, 'url' => url('/avatar/' . Auth::id(), ['v' => substr(md5($path), 0, 8)]), 'message' => __('settings.avatar_saved')]);
        }
        flash('success', __('settings.avatar_saved'));
        $this->redirect('/settings');
    }

    public function showAvatar(int $id): void
    {
        $path = Database::value('SELECT AvatarPhoto FROM users WHERE UserID = ?', [$id]);
        if (!$path) {
            abort(404);
        }
        header('Cache-Control: private, max-age=86400');
        Upload::send($path, 'avatar');
    }

    /** CV download: the student, a company they applied to, their supervisor, or an admin. */
    public function downloadCv(int $studentId): void
    {
        $s = \Student::withUser($studentId);
        if (!$s || !$s['CVFilePath']) {
            abort(404);
        }
        $p = Auth::profile();
        $allowed = match (Auth::role()) {
            'Student'    => (int) $p['StudentID'] === $studentId,
            'Company'    => (bool) Database::value(
                'SELECT 1 FROM applications a JOIN coop_opportunities o ON o.OpportunityID = a.OpportunityID WHERE a.StudentID = ? AND o.CompanyID = ?',
                [$studentId, $p['CompanyID']]
            ),
            'Supervisor' => (int) $s['SupervisorID'] === (int) $p['SupervisorID'],
            'Admin'      => true,
        };
        if (!$allowed) {
            abort(403);
        }
        Upload::send($s['CVFilePath'], 'CV_' . $s['InstitutionalID']);
    }

    public function downloadReport(int $reportId): void
    {
        $r = Database::one('SELECT fr.*, s.InstitutionalID FROM final_reports fr JOIN students s ON s.StudentID = fr.StudentID WHERE fr.ReportID = ?', [$reportId]);
        if (!$r) {
            abort(404);
        }
        $p = Auth::profile();
        $allowed = match (Auth::role()) {
            'Student'    => (int) $p['StudentID'] === (int) $r['StudentID'],
            'Supervisor' => (int) $p['SupervisorID'] === (int) $r['SupervisorID'],
            'Admin'      => true,
            default      => false,
        };
        if (!$allowed) {
            abort(403);
        }
        Upload::send($r['FilePath'], 'Report_' . $r['InstitutionalID']);
    }
}
