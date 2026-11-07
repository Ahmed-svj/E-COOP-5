<?php
namespace Controllers\Student;

use Auth;
use Controller;
use Database;
use Upload;

class ProfileController extends Controller
{
    public function edit(): void
    {
        $this->view('student/profile', ['s' => Auth::profile()]);
    }

    /** UC-01: update profile and CV. */
    public function update(): void
    {
        $s = Auth::profile();
        $d = $this->validate([
            'full_name' => 'required|min:3|max:150',
            'phone'     => 'required|phone',
            'major'     => 'required|max:150',
            'gpa'       => 'required|numeric|between:0,5',
        ]);

        $cv = $s['CVFilePath'];
        if (!empty($_FILES['cv']) && $_FILES['cv']['error'] !== UPLOAD_ERR_NO_FILE) {
            [$path, $err] = Upload::store($_FILES['cv'], 'cv');
            if ($err) {
                \Session::flash('errors', ['cv' => $err]);
                \Session::flash('old', $d);
                $this->redirect('/student/profile');
            }
            Upload::delete($cv);
            $cv = $path;
            \AuditService::log('CV_UPLOADED', 'Student', (int) $s['StudentID']);
        }

        Database::transaction(function () use ($s, $d, $cv) {
            Database::query('UPDATE users SET FullName = ?, Phone = ? WHERE UserID = ?', [$d['full_name'], $d['phone'], $s['UserID']]);
            Database::query('UPDATE students SET Major = ?, GPA = ?, CVFilePath = ? WHERE StudentID = ?', [$d['major'], $d['gpa'], $cv, $s['StudentID']]);
        });
        flash('success', __('profile.saved'));
        $this->redirect('/student/profile');
    }
}
