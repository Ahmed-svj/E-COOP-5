<?php
class RoleMiddleware
{
    public static function handle(array $roles): void
    {
        AuthMiddleware::handle();
        if (!in_array(Auth::role(), $roles, true)) {
            AuditService::log('ACCESS_DENIED', null, null, $_SERVER['REQUEST_URI'] ?? '');
            abort(403);
        }
    }
}
