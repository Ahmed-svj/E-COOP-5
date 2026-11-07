<?php
declare(strict_types=1);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Minimal .env loader (KEY=VALUE lines). Real environment variables win.
$envFile = BASE_PATH . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = array_map('trim', explode('=', $line, 2));
        $v = trim($v, "\"'");
        if (getenv($k) === false) {
            putenv("$k=$v");
            $_ENV[$k] = $v;
        }
    }
}

// Autoloader: Controllers\... live in app/controllers, everything else is a flat class name.
spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'Controllers\\')) {
        $path = BASE_PATH . '/app/controllers/' . str_replace('\\', '/', substr($class, 12)) . '.php';
        if (is_file($path)) {
            require $path;
        }
        return;
    }
    foreach (['core', 'models', 'services', 'middleware'] as $dir) {
        $path = BASE_PATH . "/app/$dir/$class.php";
        if (is_file($path)) {
            require $path;
            return;
        }
    }
});

require BASE_PATH . '/app/core/helpers.php';
require BASE_PATH . '/app/views/partials/icons.php';

$config = config();
date_default_timezone_set($config['timezone']);

if (PHP_SAPI !== 'cli') {
    set_exception_handler(function (Throwable $e): void {
        error_log('[' . date('c') . '] ' . $e . "\n", 3, BASE_PATH . '/storage/logs/error.log');
        http_response_code(500);
        if (config('debug')) {
            echo '<pre>' . e((string) $e) . '</pre>';
            return;
        }
        View::render('errors/500', [], 'main');
    });
    Security::headers();
    Session::start();
}
