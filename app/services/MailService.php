<?php
/**
 * Email sender. With MAIL_DRIVER=log (default) messages are written to storage/logs/mail.log,
 * which is what development and testing use. Set MAIL_DRIVER=mail to use PHP mail().
 * To use SMTP, install PHPMailer with Composer and replace send() (see README).
 */
class MailService
{
    public static function send(string $to, string $subject, string $body): bool
    {
        $driver = getenv('MAIL_DRIVER') ?: 'log';
        $from = getenv('MAIL_FROM') ?: 'no-reply@coop.local';
        if ($driver === 'mail') {
            $headers = "From: COOP <$from>\r\nContent-Type: text/plain; charset=UTF-8";
            return mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);
        }
        $entry = sprintf("[%s] To: %s\nSubject: %s\n\n%s\n%s\n", date('c'), $to, $subject, $body, str_repeat('-', 60));
        return (bool) file_put_contents(BASE_PATH . '/storage/logs/mail.log', $entry, FILE_APPEND | LOCK_EX);
    }
}
