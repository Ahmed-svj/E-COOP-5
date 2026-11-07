<?php
namespace Controllers\Student;

use Auth;
use Controller;
use Database;

class AttendanceController extends Controller
{
    public function index(): void
    {
        $s = Auth::profile();
        $records = Database::all(
            'SELECT ar.*, dc.CodeDate FROM attendance_records ar JOIN daily_codes dc ON dc.DailyCodeID = ar.DailyCodeID
              WHERE ar.StudentID = ? ORDER BY dc.CodeDate DESC', [$s['StudentID']]);
        $lockedUntil = ($s['CodeLockoutUntil'] && strtotime($s['CodeLockoutUntil']) > time()) ? $s['CodeLockoutUntil'] : null;
        $checkedToday = (bool) array_filter($records, fn ($r) => $r['CodeDate'] === date('Y-m-d'));
        $this->view('student/attendance', compact('s', 'records', 'lockedUntil', 'checkedToday'));
    }

    /** UC-04: daily check-in. */
    public function checkIn(): void
    {
        $code = preg_replace('/\D/', '', is_array($_POST['code'] ?? null) ? implode('', $_POST['code']) : (string) ($_POST['code'] ?? ''));
        $result = \AttendanceService::checkIn(Auth::profile(), $code);
        flash($result['ok'] ? 'success' : ($result['type'] === 'amber' ? 'warning' : 'error'), $result['message']);
        $this->back();
    }
}
