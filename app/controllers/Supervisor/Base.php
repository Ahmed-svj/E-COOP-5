<?php
namespace Controllers\Supervisor;

use Auth;
use Controller;

abstract class Base extends Controller
{
    protected function sup(): array
    {
        return Auth::profile();
    }

    /** Record-level check: only students assigned to this supervisor. */
    protected function myStudent(int $studentId): array
    {
        $s = \Student::withUser($studentId);
        if (!$s || (int) $s['SupervisorID'] !== (int) $this->sup()['SupervisorID']) {
            abort(404);
        }
        return $s;
    }
}
