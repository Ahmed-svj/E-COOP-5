<?php
class Security
{
    public static function headers(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        // No inline scripts anywhere; inline style attributes are allowed for bar heights and widths.
        header("Content-Security-Policy: default-src 'self'; script-src 'self'; "
            . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; "
            . "img-src 'self' data:; form-action 'self'; frame-ancestors 'none'; base-uri 'self'; object-src 'none'");
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
}
