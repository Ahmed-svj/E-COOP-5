<?php
class Lang
{
    private static array $strings = [];
    private static array $fallback = [];

    public static function current(): string
    {
        $l = $_SESSION['lang'] ?? 'en';
        return in_array($l, ['en', 'ar'], true) ? $l : 'en';
    }

    public static function dir(): string
    {
        return self::current() === 'ar' ? 'rtl' : 'ltr';
    }

    public static function get(string $key, array $replace = []): string
    {
        if (!self::$fallback) {
            self::$fallback = require BASE_PATH . '/app/lang/en.php';
            self::$strings = self::current() === 'ar' ? require BASE_PATH . '/app/lang/ar.php' : self::$fallback;
        }
        $s = self::$strings[$key] ?? self::$fallback[$key] ?? $key;
        foreach ($replace as $k => $v) {
            $s = str_replace(':' . $k, (string) $v, $s);
        }
        return $s;
    }
}
