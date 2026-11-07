<?php
class NotificationService
{
    /** Email preference keys and their defaults (Settings → Notifications). */
    public const CATEGORIES = [
        'notify_attendance'   => true,
        'notify_applications' => true,
        'notify_reports'      => true,
        'weekly_summary'      => false,
    ];

    /** Which preference controls the email for a notification, from the page it links to. */
    public static function category(?string $link): string
    {
        $l = (string) $link;
        return match (true) {
            str_contains($l, 'attendance') || str_contains($l, 'daily-code')       => 'notify_attendance',
            (bool) preg_match('#report|evaluat|trainees#', $l)                     => 'notify_reports',
            default                                                                => 'notify_applications',
        };
    }

    /** Create an in-app notification and, if the user wants it, an email. */
    public static function notify(int $userId, string $message, ?string $link = null): void
    {
        Database::query('INSERT INTO notifications (UserID, Message, Link) VALUES (?, ?, ?)', [$userId, $message, $link]);

        $wantsEmail = Database::value(
            'SELECT Enabled FROM user_preferences WHERE UserID = ? AND PreferenceKey = ?',
            [$userId, self::category($link)]
        );
        if ($wantsEmail === null || (int) $wantsEmail === 1) {
            $email = Database::value('SELECT Email FROM users WHERE UserID = ?', [$userId]);
            if ($email) {
                MailService::send($email, 'COOP: ' . mb_substr($message, 0, 60), $message . ($link ? "\n\n" . full_url($link) : ''));
            }
        }
    }

    /** Notify every active admin. */
    public static function admins(string $message, ?string $link = null): void
    {
        foreach (Database::all("SELECT UserID FROM users WHERE Role = 'Admin' AND AccountStatus = 'Active'") as $a) {
            self::notify((int) $a['UserID'], $message, $link);
        }
    }
}
