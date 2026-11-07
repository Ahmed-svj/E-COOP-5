<?php
class AuthMiddleware
{
    public static function handle(): void
    {
        if (!Auth::check()) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                http_response_code(401);
                exit;
            }
            Session::set('intended', $_SERVER['REQUEST_URI'] ?? '/');
            flash('warning', __('auth.login_first'));
            redirect('/login');
        }
    }

    public static function guest(): void
    {
        if (Auth::check()) {
            redirect('/dashboard');
        }
    }
}
