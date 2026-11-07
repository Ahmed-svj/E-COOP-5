<?php
namespace Controllers\Company;

use Database;

class OpportunityController extends Base
{
    private const RULES = [
        'title'       => 'required|min:3|max:190',
        'description' => 'required|min:20|max:5000',
        'major'       => 'required|max:255',
        'city'        => 'required|max:100',
        'capacity'    => 'required|integer|between:1,500',
    ];

    public function index(): void
    {
        $items = Database::all(
            "SELECT o.*, (SELECT COUNT(*) FROM applications a WHERE a.OpportunityID = o.OpportunityID AND a.Status = 'Accepted') AS AcceptedCount,
                    (SELECT COUNT(*) FROM applications a WHERE a.OpportunityID = o.OpportunityID AND a.Status = 'Pending') AS PendingCount
               FROM coop_opportunities o WHERE o.CompanyID = ? ORDER BY o.OpportunityID DESC", [$this->company()['CompanyID']]);
        $this->view('company/opportunities', ['items' => $items, 'c' => $this->company()]);
    }

    public function create(): void
    {
        $this->requireApproved();
        $this->view('company/opportunity-form', ['o' => null]);
    }

    /** UC-08: new opportunities wait for admin approval. */
    public function store(): void
    {
        $c = $this->requireApproved();
        $d = $this->validate(self::RULES);
        $id = \Opportunity::insert([
            'CompanyID' => $c['CompanyID'], 'Title' => $d['title'], 'Description' => $d['description'],
            'MajorRequirements' => $d['major'], 'City' => $d['city'], 'Capacity' => (int) $d['capacity'],
        ]);
        \AuditService::log('OPPORTUNITY_CREATED', 'Opportunity', $id);
        \NotificationService::admins(__('n.opportunity_pending', ['title' => $d['title']]), '/admin/opportunities');
        flash('success', __('opp.submitted'));
        $this->redirect('/company/opportunities');
    }

    public function edit(int $id): void
    {
        $this->requireApproved();
        $o = $this->ownOpportunity($id);
        if ($o['Status'] === 'Closed') {
            flash('error', __('opp.closed_no_edit'));
            $this->redirect('/company/opportunities');
        }
        $this->view('company/opportunity-form', ['o' => $o]);
    }

    /** Editing an approved or rejected post sends it back for approval. */
    public function update(int $id): void
    {
        $this->requireApproved();
        $o = $this->ownOpportunity($id);
        if ($o['Status'] === 'Closed') {
            abort(403);
        }
        $d = $this->validate(self::RULES);
        $accepted = (int) Database::value("SELECT COUNT(*) FROM applications WHERE OpportunityID = ? AND Status = 'Accepted'", [$id]);
        if ((int) $d['capacity'] < $accepted) {
            \Session::flash('errors', ['capacity' => __('opp.capacity_below', ['n' => $accepted])]);
            \Session::flash('old', $d);
            $this->back();
        }
        \Opportunity::update($id, [
            'Title' => $d['title'], 'Description' => $d['description'], 'MajorRequirements' => $d['major'],
            'City' => $d['city'], 'Capacity' => (int) $d['capacity'], 'Status' => 'Pending Approval', 'RejectionReason' => null,
        ]);
        \AuditService::log('OPPORTUNITY_UPDATED', 'Opportunity', $id);
        \NotificationService::admins(__('n.opportunity_pending', ['title' => $d['title']]), '/admin/opportunities');
        flash('success', __('opp.resubmitted'));
        $this->redirect('/company/opportunities');
    }

    public function close(int $id): void
    {
        $this->ownOpportunity($id);
        $stmt = Database::query("UPDATE coop_opportunities SET Status = 'Closed' WHERE OpportunityID = ? AND Status = 'Approved'", [$id]);
        if ($stmt->rowCount()) {
            // Pending applicants are told the post closed.
            foreach (Database::all("SELECT a.ApplicationID, s.UserID FROM applications a JOIN students s ON s.StudentID = a.StudentID WHERE a.OpportunityID = ? AND a.Status = 'Pending'", [$id]) as $a) {
                Database::query("UPDATE applications SET Status = 'Rejected', RejectionReason = 'Opportunity closed' WHERE ApplicationID = ?", [$a['ApplicationID']]);
                \NotificationService::notify((int) $a['UserID'], __('n.opp_closed'), '/student/applications');
            }
            \AuditService::log('OPPORTUNITY_CLOSED', 'Opportunity', $id);
            flash('success', __('opp.closed'));
        }
        $this->redirect('/company/opportunities');
    }
}
