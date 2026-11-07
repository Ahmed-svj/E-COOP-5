<?php
namespace Controllers\Company;

use Auth;
use Controller;

abstract class Base extends Controller
{
    protected function company(): array
    {
        return Auth::profile();
    }

    /** Companies can only post and manage once an admin approves them (UC-19). */
    protected function requireApproved(): array
    {
        $c = $this->company();
        if ($c['ApprovalStatus'] !== 'Approved') {
            flash('warning', __('company.not_approved'));
            $this->redirect('/company/dashboard');
        }
        return $c;
    }

    protected function ownOpportunity(int $id): array
    {
        $o = \Opportunity::find($id);
        if (!$o || (int) $o['CompanyID'] !== (int) $this->company()['CompanyID']) {
            abort(404);
        }
        return $o;
    }
}
