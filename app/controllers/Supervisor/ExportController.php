<?php
namespace Controllers\Supervisor;

class ExportController extends Base
{
    /** UC-17: printable cohort report. */
    public function index(): void
    {
        $cohort = \ReportService::cohort((int) $this->sup()['SupervisorID']);
        $this->view('supervisor/export', ['cohort' => $cohort, 'sp' => $this->sup()]);
    }

    public function csv(): void
    {
        $rows = array_map(fn ($r) => [
            $r['InstitutionalID'], $r['FullName'], $r['Major'], $r['CompanyName'] ?? '', $r['PlacementStatus'] ?? '',
            (int) $r['Approved'], (int) $r['Pending'], (int) $r['Rejected'], $r['ReportStatus'] ?? '', $r['CompanyScore'] ?? '', $r['AcademicScore'] ?? '',
        ], \ReportService::cohort((int) $this->sup()['SupervisorID']));
        \AuditService::log('EXPORT_CSV', 'Supervisor', (int) $this->sup()['SupervisorID']);
        \ReportService::csv('coop-cohort-' . date('Y-m-d') . '.csv',
            ['Student ID', 'Name', 'Major', 'Company', 'Placement', 'Approved days', 'Pending', 'Rejected', 'Report', 'Company score', 'Academic score'], $rows);
    }
}
