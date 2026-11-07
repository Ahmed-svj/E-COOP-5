<?php
namespace Controllers\Company;

use Database;

class ProfileController extends Base
{
    public function edit(): void
    {
        $this->view('company/profile', ['c' => $this->company()]);
    }

    /** UC-07 */
    public function update(): void
    {
        $c = $this->company();
        $d = $this->validate([
            'company_name' => 'required|min:2|max:190',
            'full_name'    => 'required|min:3|max:150',
            'phone'        => 'required|phone',
            'city'         => 'required|max:100',
        ]);
        Database::transaction(function () use ($c, $d) {
            Database::query('UPDATE users SET FullName = ?, Phone = ? WHERE UserID = ?', [$d['full_name'], $d['phone'], $c['UserID']]);
            Database::query('UPDATE companies SET CompanyName = ?, City = ? WHERE CompanyID = ?', [$d['company_name'], $d['city'], $c['CompanyID']]);
        });
        \AuditService::log('COMPANY_PROFILE_UPDATED', 'Company', (int) $c['CompanyID']);
        flash('success', __('profile.saved'));
        $this->redirect('/company/profile');
    }
}
