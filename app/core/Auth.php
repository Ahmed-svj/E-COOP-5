<?php
class Auth
{
    private static ?array $user = null;
    private static bool $loaded = false;

    /**
     * Try to log in. Returns null on success or an error message.
     * Locks the account for 15 minutes after 5 failed attempts.
     */
    public static function attempt(string $email, string $password): ?string
    {
        $cfg = config();
        $user = User::findByEmail($email);

        if (!$user) {
            password_verify($password, '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG'); // even timing
            AuditService::log('LOGIN_FAILED', 'User', null, 'Unknown email: ' . mb_substr($email, 0, 100));
            return __('auth.failed');
        }

        if ($user['LockoutUntil'] && strtotime($user['LockoutUntil']) > time()) {
            $mins = (int) ceil((strtotime($user['LockoutUntil']) - time()) / 60);
            return __('auth.locked', ['minutes' => $mins]);
        }

        if (!password_verify($password, $user['PasswordHash'])) {
            $count = $user['FailedLoginCount'] + 1;
            $lock = null;
            if ($count >= $cfg['login_max_attempts']) {
                $lock = date('Y-m-d H:i:s', time() + $cfg['login_lockout_min'] * 60);
                $count = 0;
                AuditService::log('ACCOUNT_LOCKED', 'User', (int) $user['UserID'], null, (int) $user['UserID']);
            }
            Database::query('UPDATE users SET FailedLoginCount = ?, LockoutUntil = ? WHERE UserID = ?', [$count, $lock, $user['UserID']]);
            AuditService::log('LOGIN_FAILED', 'User', (int) $user['UserID'], null, (int) $user['UserID']);
            return $lock ? __('auth.locked', ['minutes' => $cfg['login_lockout_min']]) : __('auth.failed');
        }

        if ($user['AccountStatus'] !== 'Active') {
            AuditService::log('LOGIN_BLOCKED_SUSPENDED', 'User', (int) $user['UserID'], null, (int) $user['UserID']);
            return __('auth.suspended');
        }

        if (password_needs_rehash($user['PasswordHash'], PASSWORD_BCRYPT)) {
            Database::query('UPDATE users SET PasswordHash = ? WHERE UserID = ?', [password_hash($password, PASSWORD_BCRYPT), $user['UserID']]);
        }

        Database::query('UPDATE users SET FailedLoginCount = 0, LockoutUntil = NULL, LastLoginAt = NOW() WHERE UserID = ?', [$user['UserID']]);
        self::login((int) $user['UserID']);
        AuditService::log('LOGIN_SUCCESS', 'User', (int) $user['UserID']);
        return null;
    }

    public static function login(int $userId): void
    {
        Session::regenerate();
        Session::set('user_id', $userId);
        Csrf::rotate();
        self::$loaded = false;
        // Load saved language preference.
        $ar = Database::value("SELECT Enabled FROM user_preferences WHERE UserID = ? AND PreferenceKey = 'lang_ar'", [$userId]);
        if ($ar !== null) {
            Session::set('lang', $ar ? 'ar' : 'en');
        }
    }

    public static function logout(): void
    {
        if ($id = self::id()) {
            AuditService::log('LOGOUT', 'User', $id);
        }
        $lang = Session::get('lang');
        Session::destroy();
        Session::start();
        if ($lang) {
            Session::set('lang', $lang);
        }
    }

    /** The current user (fresh from DB each request, so suspension takes effect immediately). */
    public static function user(): ?array
    {
        if (!self::$loaded) {
            self::$loaded = true;
            $id = Session::get('user_id');
            self::$user = $id ? User::find((int) $id) : null;
            if (self::$user && self::$user['AccountStatus'] !== 'Active') {
                // Suspended users lose their active session.
                self::$user = null;
                Session::destroy();
                Session::start();
                Session::flash('error', __('auth.suspended'));
            }
        }
        return self::$user;
    }

    public static function id(): ?int
    {
        $u = self::user();
        return $u ? (int) $u['UserID'] : null;
    }

    public static function role(): ?string
    {
        return self::user()['Role'] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    /** Role profile row: students / companies / academic_supervisors / admins. */
    public static function profile(): ?array
    {
        static $profile = false;
        if ($profile !== false) {
            return $profile;
        }
        $u = self::user();
        if (!$u) {
            return $profile = null;
        }
        $profile = match ($u['Role']) {
            'Student'    => Student::byUser((int) $u['UserID']),
            'Company'    => Company::byUser((int) $u['UserID']),
            'Supervisor' => AcademicSupervisor::byUser((int) $u['UserID']),
            'Admin'      => Admin::byUser((int) $u['UserID']),
        };
        return $profile;
    }
}
