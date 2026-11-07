<?php
class AttendanceService
{
    /**
     * Student check-in with today's code.
     * Returns ['ok' => bool, 'message' => string, 'type' => 'mint|amber|red'].
     */
    public static function checkIn(array $student, string $code): array
    {
        $cfg = config();
        $sid = (int) $student['StudentID'];

        if (!$student['PlacementID']) {
            return ['ok' => false, 'type' => 'red', 'message' => __('att.no_placement')];
        }
        $placement = Placement::find((int) $student['PlacementID']);
        if (!$placement || $placement['Status'] !== 'Active') {
            return ['ok' => false, 'type' => 'red', 'message' => __('att.placement_inactive')];
        }

        $fresh = Database::one('SELECT FailedCodeAttempts, CodeLockoutUntil FROM students WHERE StudentID = ?', [$sid]);
        if ($fresh['CodeLockoutUntil'] && strtotime($fresh['CodeLockoutUntil']) > time()) {
            return ['ok' => false, 'type' => 'red', 'message' => __('att.locked', ['time' => date('H:i', strtotime($fresh['CodeLockoutUntil']))])];
        }

        if (!preg_match('/^\d{6}$/', $code)) {
            return ['ok' => false, 'type' => 'amber', 'message' => __('att.six_digits')];
        }

        $row = Database::one(
            'SELECT DailyCodeID FROM daily_codes
              WHERE PlacementID = ? AND Code = ? AND CodeDate = CURDATE() AND IsActive = 1 AND ExpiryTime > NOW()',
            [$student['PlacementID'], $code]
        );

        if (!$row) {
            $attempts = (int) $fresh['FailedCodeAttempts'] + 1;
            if ($attempts >= $cfg['code_max_attempts']) {
                $until = date('Y-m-d H:i:s', time() + $cfg['code_lockout_min'] * 60);
                Database::query('UPDATE students SET FailedCodeAttempts = 0, CodeLockoutUntil = ? WHERE StudentID = ?', [$until, $sid]);
                AuditService::log('CHECKIN_LOCKED', 'Student', $sid);
                return ['ok' => false, 'type' => 'red', 'message' => __('att.locked', ['time' => date('H:i', strtotime($until))])];
            }
            Database::query('UPDATE students SET FailedCodeAttempts = ? WHERE StudentID = ?', [$attempts, $sid]);
            AuditService::log('CHECKIN_WRONG_CODE', 'Student', $sid);
            return ['ok' => false, 'type' => 'amber', 'message' => __('att.wrong', ['n' => $cfg['code_max_attempts'] - $attempts])];
        }

        try {
            Database::query(
                'INSERT INTO attendance_records (StudentID, PlacementID, DailyCodeID) VALUES (?, ?, ?)',
                [$sid, $student['PlacementID'], $row['DailyCodeID']]
            );
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                return ['ok' => false, 'type' => 'amber', 'message' => __('att.already')];
            }
            throw $e;
        }
        Database::query('UPDATE students SET FailedCodeAttempts = 0, CodeLockoutUntil = NULL WHERE StudentID = ?', [$sid]);
        AuditService::log('CHECKIN', 'Attendance', (int) Database::connect()->lastInsertId());

        if ($student['SupervisorID']) {
            $supUser = Database::value('SELECT UserID FROM academic_supervisors WHERE SupervisorID = ?', [$student['SupervisorID']]);
            if ($supUser) {
                Database::query('INSERT INTO notifications (UserID, Message, Link) VALUES (?, ?, ?)',
                    [$supUser, __('n.checkin_pending', ['name' => $student['FullName']]), '/supervisor/attendance']);
            }
        }
        return ['ok' => true, 'type' => 'mint', 'message' => __('att.ok', ['time' => date('H:i')])];
    }

    /** Last N working days (Sun–Thu) with the student's record status for each. */
    public static function week(int $studentId): array
    {
        $rows = Database::all(
            'SELECT dc.CodeDate, ar.ApprovalStatus FROM attendance_records ar
               JOIN daily_codes dc ON dc.DailyCodeID = ar.DailyCodeID
              WHERE ar.StudentID = ? AND dc.CodeDate >= CURDATE() - INTERVAL 10 DAY',
            [$studentId]
        );
        $byDate = array_column($rows, 'ApprovalStatus', 'CodeDate');
        $days = [];
        $d = new DateTime('today');
        while (count($days) < 5) {
            if ((int) $d->format('w') <= 4) { // Sun(0)–Thu(4)
                $key = $d->format('Y-m-d');
                $days[] = ['date' => $key, 'label' => __('day.' . $d->format('D')), 'status' => $byDate[$key] ?? null];
            }
            $d->modify('-1 day');
        }
        return array_reverse($days);
    }
}
