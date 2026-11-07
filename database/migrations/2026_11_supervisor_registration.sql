-- Run once on databases created before supervisor self-registration:
--   mysql -u root -p coop_db < database/migrations/2026_11_supervisor_registration.sql
ALTER TABLE academic_supervisors
  ADD COLUMN ApprovalStatus ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Approved' AFTER MaxStudentLoad,
  ADD COLUMN RejectionReason TEXT NULL AFTER ApprovalStatus;

CREATE OR REPLACE VIEW v_supervisor_load AS
SELECT sp.SupervisorID, u.FullName, sp.Department, sp.MaxStudentLoad, sp.ApprovalStatus,
       COUNT(s.StudentID) AS CurrentLoad
FROM academic_supervisors sp
JOIN users u ON u.UserID = sp.UserID
LEFT JOIN students s ON s.SupervisorID = sp.SupervisorID
GROUP BY sp.SupervisorID, u.FullName, sp.Department, sp.MaxStudentLoad, sp.ApprovalStatus;
