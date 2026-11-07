<?php
namespace Controllers\Student;

use Auth;
use Controller;
use Database;

class OpportunityController extends Controller
{
    /** UC-02: browse and filter approved opportunities (also serves JSON for live filtering). */
    public function index(): void
    {
        $q = mb_substr($this->input('q'), 0, 100);
        $city = mb_substr($this->input('city'), 0, 100);
        $major = mb_substr($this->input('major'), 0, 150);

        $where = ['1=1'];
        $params = [];
        if ($q !== '') {
            $where[] = '(Title LIKE ? OR CompanyName LIKE ? OR Description LIKE ?)';
            array_push($params, "%$q%", "%$q%", "%$q%");
        }
        if ($city !== '') {
            $where[] = 'City = ?';
            $params[] = $city;
        }
        if ($major !== '') {
            $where[] = 'MajorRequirements LIKE ?';
            $params[] = "%$major%";
        }
        $sid = (int) Auth::profile()['StudentID'];
        $items = Database::all(
            'SELECT v.*, (SELECT Status FROM applications a WHERE a.OpportunityID = v.OpportunityID AND a.StudentID = ?) AS MyStatus
               FROM v_public_opportunities v WHERE ' . implode(' AND ', $where) . ' ORDER BY PostedDate DESC, OpportunityID DESC LIMIT 100',
            [$sid, ...$params]
        );

        if (($_GET['format'] ?? '') === 'json') {
            $this->json(['html' => \View::partial('student/_opportunity-cards', ['items' => $items]), 'count' => count($items)]);
        }
        $cities = Database::all('SELECT DISTINCT City FROM v_public_opportunities ORDER BY City');
        $majors = Database::all('SELECT DISTINCT MajorRequirements AS Major FROM v_public_opportunities ORDER BY Major');
        $this->view('student/opportunities', compact('items', 'q', 'city', 'major', 'cities', 'majors'));
    }

    public function show(int $id): void
    {
        $o = Database::one('SELECT * FROM v_public_opportunities WHERE OpportunityID = ?', [$id]);
        if (!$o) {
            abort(404);
        }
        $s = Auth::profile();
        $myApp = Database::one('SELECT * FROM applications WHERE StudentID = ? AND OpportunityID = ?', [$s['StudentID'], $id]);
        $complete = \Student::isComplete($s);
        $this->view('student/opportunity-details', compact('o', 's', 'myApp', 'complete'));
    }

    /** UC-03: apply. Profile must be complete; one per opportunity; not allowed once placed. */
    public function apply(int $id): void
    {
        $s = Auth::profile();
        $o = Database::one('SELECT * FROM v_public_opportunities WHERE OpportunityID = ?', [$id]);
        if (!$o) {
            abort(404);
        }
        $error = match (true) {
            !\Student::isComplete($s)                 => __('apply.incomplete'),
            (bool) $s['PlacementID']                   => __('apply.placed'),
            $o['AcceptedCount'] >= $o['Capacity']      => __('err.capacity_full'),
            default                                    => null,
        };
        if ($error) {
            flash('error', $error);
            $this->redirect("/student/opportunities/$id");
        }
        try {
            $appId = \Application::insert(['StudentID' => $s['StudentID'], 'OpportunityID' => $id]);
        } catch (\PDOException $e) {
            if ($e->getCode() !== '23000') {
                throw $e;
            }
            flash('error', __('apply.duplicate'));
            $this->redirect("/student/opportunities/$id");
        }
        $companyUser = (int) Database::value('SELECT UserID FROM companies WHERE CompanyID = ?', [$o['CompanyID']]);
        \NotificationService::notify($companyUser, __('n.new_applicant', ['name' => $s['FullName'], 'title' => $o['Title']]), '/company/applicants?opportunity=' . $id);
        \AuditService::log('APPLICATION_SUBMITTED', 'Application', $appId);
        flash('success', __('apply.done'));
        $this->redirect('/student/applications');
    }
}
