<?php
class PlacementService
{
    /**
     * Accept an applicant in one transaction with a row lock on the opportunity (capacity race-safe).
     * Creates the opportunity's placement on first acceptance, links the student, withdraws their
     * other pending applications, notifies and audits. Returns null on success or an error message.
     */
    public static function accept(int $applicationId, int $companyId, ?string $start, ?string $end): ?string
    {
        return Database::transaction(function (PDO $db) use ($applicationId, $companyId, $start, $end) {
            $app = Database::one(
                'SELECT a.*, o.CompanyID, o.Title, o.Status AS OppStatus FROM applications a
                   JOIN coop_opportunities o ON o.OpportunityID = a.OpportunityID
                  WHERE a.ApplicationID = ?',
                [$applicationId]
            );
            if (!$app || (int) $app['CompanyID'] !== $companyId) {
                return __('err.not_found');
            }

            $opp = Database::one('SELECT Capacity, Status FROM coop_opportunities WHERE OpportunityID = ? FOR UPDATE', [$app['OpportunityID']]);
            $accepted = (int) Database::value("SELECT COUNT(*) FROM applications WHERE OpportunityID = ? AND Status = 'Accepted'", [$app['OpportunityID']]);
            if ($accepted >= (int) $opp['Capacity']) {
                $db->rollBack();
                return __('err.capacity_full');
            }

            $student = Database::one('SELECT s.StudentID, s.PlacementID, s.UserID FROM students s WHERE s.StudentID = ? FOR UPDATE', [$app['StudentID']]);
            if ($student['PlacementID']) {
                $db->rollBack();
                return __('err.student_placed');
            }

            $upd = $db->prepare("UPDATE applications SET Status = 'Accepted' WHERE ApplicationID = ? AND Status = 'Pending'");
            $upd->execute([$applicationId]);
            if ($upd->rowCount() !== 1) {
                $db->rollBack();
                return __('err.not_pending');
            }

            $placementId = Database::value('SELECT PlacementID FROM training_placements WHERE OpportunityID = ?', [$app['OpportunityID']]);
            if (!$placementId) {
                if (!$start || !$end || $end <= $start) {
                    $db->rollBack();
                    return __('err.dates_required');
                }
                Database::query('INSERT INTO training_placements (OpportunityID, StartDate, EndDate) VALUES (?, ?, ?)', [$app['OpportunityID'], $start, $end]);
                $placementId = (int) $db->lastInsertId();
            }

            Database::query('UPDATE students SET PlacementID = ? WHERE StudentID = ?', [$placementId, $student['StudentID']]);
            Database::query(
                "UPDATE applications SET Status = 'Withdrawn' WHERE StudentID = ? AND Status = 'Pending' AND ApplicationID <> ?",
                [$student['StudentID'], $applicationId]
            );

            NotificationService::notify((int) $student['UserID'], __('n.app_accepted', ['title' => $app['Title']]), '/student/applications');
            NotificationService::admins(__('n.needs_supervisor'), '/admin/assign');
            AuditService::log('APPLICATION_ACCEPTED', 'Application', $applicationId, 'Placement ' . $placementId);
            return null;
        });
    }

    public static function reject(int $applicationId, int $companyId, string $reason): ?string
    {
        $app = Database::one(
            'SELECT a.*, o.CompanyID, o.Title, s.UserID FROM applications a
               JOIN coop_opportunities o ON o.OpportunityID = a.OpportunityID
               JOIN students s ON s.StudentID = a.StudentID
              WHERE a.ApplicationID = ?',
            [$applicationId]
        );
        if (!$app || (int) $app['CompanyID'] !== $companyId) {
            return __('err.not_found');
        }
        $stmt = Database::query("UPDATE applications SET Status = 'Rejected', RejectionReason = ? WHERE ApplicationID = ? AND Status = 'Pending'", [$reason, $applicationId]);
        if ($stmt->rowCount() !== 1) {
            return __('err.not_pending');
        }
        NotificationService::notify((int) $app['UserID'], __('n.app_rejected', ['title' => $app['Title']]), '/student/applications');
        AuditService::log('APPLICATION_REJECTED', 'Application', $applicationId, $reason);
        return null;
    }

    /** Mark placements Completed once their EndDate has passed (cron + admin button). */
    public static function completeDue(): int
    {
        $due = Database::all("SELECT PlacementID FROM training_placements WHERE Status = 'Active' AND EndDate < CURDATE()");
        foreach ($due as $p) {
            self::complete((int) $p['PlacementID']);
        }
        return count($due);
    }

    public static function complete(int $placementId): void
    {
        $stmt = Database::query("UPDATE training_placements SET Status = 'Completed' WHERE PlacementID = ? AND Status = 'Active'", [$placementId]);
        if ($stmt->rowCount() !== 1) {
            return;
        }
        foreach (Database::all('SELECT UserID FROM students WHERE PlacementID = ?', [$placementId]) as $s) {
            NotificationService::notify((int) $s['UserID'], __('n.placement_completed'), '/student/evaluations');
        }
        AuditService::log('PLACEMENT_COMPLETED', 'Placement', $placementId);
    }
}
