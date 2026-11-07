<?php
class CsrfMiddleware
{
    public static function handle(): void
    {
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!Csrf::verify($token)) {
            http_response_code(419);
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'csrf']);
                exit;
            }
            flash('error', __('csrf.expired'));
            header('Location: ' . safe_referer('/'));
            exit;
        }
    }
}
