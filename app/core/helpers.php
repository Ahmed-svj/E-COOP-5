<?php
function config(?string $key = null): mixed
{
    static $cfg = null;
    $cfg ??= require BASE_PATH . '/app/config/config.php';
    return $key === null ? $cfg : ($cfg[$key] ?? null);
}

/** Escape for HTML output. */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = '/', array $query = []): string
{
    $u = config('url') . '/' . ltrim($path, '/');
    return $query ? $u . '?' . http_build_query($query) : $u;
}

function asset(string $path): string
{
    $file = BASE_PATH . '/public/assets/' . ltrim($path, '/');
    $v = is_file($file) ? substr((string) filemtime($file), -6) : '1';
    return url('assets/' . ltrim($path, '/')) . '?v=' . $v;
}

function redirect(string $path): never
{
    header('Location: ' . (preg_match('#^https?://#', $path) ? $path : url($path)));
    exit;
}

function abort(int $code): never
{
    http_response_code($code);
    $tpl = in_array($code, [403, 404, 500], true) ? (string) $code : '404';
    View::render('errors/' . $tpl, ['code' => $code], Auth::check() ? 'dashboard' : 'main');
    exit;
}

function __(string $key, array $replace = []): string
{
    return Lang::get($key, $replace);
}

function csrf_field(): string
{
    return Csrf::field();
}

function old(string $key, mixed $default = ''): string
{
    static $old = null;
    $old ??= Session::pull('old', []);
    return (string) ($old[$key] ?? $default);
}

function errors(): array
{
    static $errors = null;
    $errors ??= Session::pull('errors', []);
    return $errors;
}

function error_for(string $field): string
{
    $err = errors()[$field] ?? null;
    return $err ? '<span class="co-field__help">' . e($err) . '</span>' : '';
}

function invalid(string $field): string
{
    return isset(errors()[$field]) ? ' co-field--invalid' : '';
}

function flash(string $type, string $message): void
{
    Session::flash($type, $message);
}

/** Map a database status to a badge tone (see design system: Roles and screens). */
function status_tone(?string $status): string
{
    return match ($status) {
        'Approved', 'Accepted', 'Active'                          => 'mint',
        'Completed', 'Submitted', 'Resubmitted'                   => 'lavender',
        'Pending', 'Pending Approval', 'Revision Requested'       => 'amber',
        'Rejected'                                                => 'red',
        default                                                   => '',
    };
}

function status_badge(?string $status): string
{
    $tone = status_tone($status);
    return '<span class="co-badge' . ($tone ? ' co-badge--' . $tone : '') . '">' . e(__('status.' . $status)) . '</span>';
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/u', trim($name)) ?: [];
    $first = mb_substr($parts[0] ?? '', 0, 1);
    $last = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';
    return mb_strtoupper($first . $last);
}

function short_name(string $name): string
{
    $parts = preg_split('/\s+/u', trim($name)) ?: [];
    if (count($parts) < 2) {
        return $name;
    }
    return $parts[0] . ' ' . mb_substr(end($parts), 0, 1) . '.';
}

function greeting(): string
{
    $h = (int) date('G');
    return __($h < 12 ? 'greet.morning' : ($h < 17 ? 'greet.afternoon' : 'greet.evening'));
}

function fmt_date(?string $date, bool $time = false): string
{
    if (!$date) {
        return '—';
    }
    $ts = strtotime($date);
    return $time ? date('j M Y, H:i', $ts) : date('j M Y', $ts);
}

function is_active_path(string $prefix): bool
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    return $path === $prefix || ($prefix !== '/' && str_starts_with($path, $prefix . '/'));
}

function pref(string $key, bool $default = false): bool
{
    $id = Auth::id();
    if (!$id) {
        return $default;
    }
    $v = Database::value('SELECT Enabled FROM user_preferences WHERE UserID = ? AND PreferenceKey = ?', [$id, $key]);
    return $v === null ? $default : (bool) $v;
}

function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
}

/** Simple pagination helper: returns [offset, perPage, page]. */
function paginate(int $perPage = 20): array
{
    $page = max(1, (int) ($_GET['page'] ?? 1));
    return [($page - 1) * $perPage, $perPage, $page];
}

function majors(): array
{
    return ['Management Information Systems', 'Computer Science', 'Information Technology', 'Cybersecurity', 'Business Administration',
            'Accounting', 'Electrical Engineering', 'Mechanical Engineering', 'Chemical Engineering', 'Industrial Engineering'];
}

function cities(): array
{
    return ['Jubail', 'Dammam', 'Al Khobar', 'Dhahran', 'Ras Al-Khair', 'Riyadh', 'Jeddah', 'Yanbu', 'Al Ahsa', 'Qatif'];
}

/** Absolute URL for emails: APP_URL if set, otherwise the current request's scheme and host. */
function full_url(string $path = '/', array $query = []): string
{
    $u = url($path, $query);
    if (preg_match('#^https?://#', $u)) {
        return $u;
    }
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    return ($https ? 'https' : 'http') . '://' . $host . $u;
}


/** The signed-in user's avatar: photo if uploaded, else initials. data-me lets JS swap it everywhere. */
function avatar_html(array $user, string $extra = ''): string
{
    $cls = 'co-avatar' . ($extra ? ' ' . $extra : '');
    if (!empty($user['AvatarPhoto'])) {
        $v = substr(md5($user['AvatarPhoto']), 0, 8);
        return '<img class="' . $cls . '" data-me src="' . e(url('/avatar/' . $user['UserID'], ['v' => $v])) . '" alt="">';
    }
    return '<span class="' . $cls . '" data-me>' . e(initials($user['FullName'])) . '</span>';
}

/** The Referer if it is on this same site (host and port), else the fallback. */
function safe_referer(string $fallback = '/'): string
{
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    if ($ref === '') {
        return $fallback;
    }
    $p = parse_url($ref);
    $refHost = ($p['host'] ?? '') . (isset($p['port']) ? ':' . $p['port'] : '');
    return $refHost === ($_SERVER['HTTP_HOST'] ?? '') ? $ref : $fallback;
}


/** Current colour theme: the user's saved choice, else this visit's choice, else dark (the default). */
function current_theme(): string
{
    if (Auth::check()) {
        return pref('dark_mode', true) ? 'dark' : 'light';
    }
    $t = $_SESSION['theme'] ?? 'dark';
    return $t === 'light' ? 'light' : 'dark';
}

/** Institutional email domains students may use (STUDENT_EMAIL_DOMAIN, comma-separated). */
function student_domains(): array
{
    return array_values(array_filter(array_map('trim', explode(',', strtolower((string) config('student_email_domain'))))));
}
