-- =====================================================================
--  COOP - Cooperative Training Management System
--  MySQL 8.0.16+  |  InnoDB  |  utf8mb4
-- =====================================================================

DROP DATABASE IF EXISTS coop_db;
CREATE DATABASE coop_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE coop_db;

-- ---------------------------------------------------------------------
-- 1. USERS (base entity for every role)
-- ---------------------------------------------------------------------
CREATE TABLE users (
    UserID            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    FullName          VARCHAR(150)  NOT NULL,
    Email             VARCHAR(190)  NOT NULL,
    Phone             VARCHAR(10)   NULL,                 -- Saudi format 05XXXXXXXX
    PasswordHash      VARCHAR(255)  NOT NULL,             -- password_hash() bcrypt (salt included)
    PasswordSalt      VARCHAR(64)   NULL,                 -- kept from ERD; bcrypt already embeds the salt
    Role              ENUM('Student','Supervisor','Company','Admin') NOT NULL,
    AccountStatus     ENUM('Active','Suspended') NOT NULL DEFAULT 'Active',
    AvatarPhoto       VARCHAR(255)  NULL,
    FailedLoginCount  TINYINT UNSIGNED NOT NULL DEFAULT 0, -- lock after 5
    LockoutUntil      DATETIME      NULL,                  -- +15 minutes
    LastLoginAt       DATETIME      NULL,
    PrivacyAcceptedAt DATETIME      NULL,                  -- PDPL privacy notice
    CreatedAt         DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt         DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_users_email UNIQUE (Email),
    CONSTRAINT chk_users_phone CHECK (Phone IS NULL OR Phone REGEXP '^05[0-9]{8}$')
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 2. PASSWORD RESET (single-use link, 30 min)
-- ---------------------------------------------------------------------
CREATE TABLE password_resets (
    ResetID     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    UserID      INT UNSIGNED NOT NULL,
    TokenHash   CHAR(64)     NOT NULL,                    -- hash('sha256', token)
    ExpiresAt   DATETIME     NOT NULL,
    UsedAt      DATETIME     NULL,
    CreatedAt   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_reset_token UNIQUE (TokenHash),
    CONSTRAINT fk_reset_user FOREIGN KEY (UserID) REFERENCES users(UserID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3. ADMIN profile
-- ---------------------------------------------------------------------
CREATE TABLE admins (
    AdminID  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    UserID   INT UNSIGNED NOT NULL,
    CONSTRAINT uq_admin_user UNIQUE (UserID),
    CONSTRAINT fk_admin_user FOREIGN KEY (UserID) REFERENCES users(UserID) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 4. COMPANY profile
-- ---------------------------------------------------------------------
CREATE TABLE companies (
    CompanyID          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    UserID             INT UNSIGNED NOT NULL,
    CompanyName        VARCHAR(190) NOT NULL,
    City               VARCHAR(100) NOT NULL,
    ApprovalStatus     ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
    ApprovedByAdminID  INT UNSIGNED NULL,
    RejectionReason    TEXT         NULL,
    CreatedAt          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_company_user UNIQUE (UserID),
    CONSTRAINT fk_company_user  FOREIGN KEY (UserID)            REFERENCES users(UserID)   ON DELETE RESTRICT,
    CONSTRAINT fk_company_admin FOREIGN KEY (ApprovedByAdminID) REFERENCES admins(AdminID) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 5. ACADEMIC SUPERVISOR profile
-- ---------------------------------------------------------------------
CREATE TABLE academic_supervisors (
    SupervisorID    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    UserID          INT UNSIGNED NOT NULL,
    Department      VARCHAR(150) NOT NULL,
    MaxStudentLoad  SMALLINT UNSIGNED NOT NULL DEFAULT 20,
    ApprovalStatus  ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Approved',  -- self-registered: Pending
    RejectionReason TEXT NULL,
    CONSTRAINT uq_supervisor_user UNIQUE (UserID),
    CONSTRAINT fk_supervisor_user FOREIGN KEY (UserID) REFERENCES users(UserID) ON DELETE RESTRICT,
    CONSTRAINT chk_supervisor_load CHECK (MaxStudentLoad > 0)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 6. COOP OPPORTUNITY
-- ---------------------------------------------------------------------
CREATE TABLE coop_opportunities (
    OpportunityID      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    CompanyID          INT UNSIGNED NOT NULL,
    Title              VARCHAR(190) NOT NULL,
    Description        TEXT         NOT NULL,
    MajorRequirements  VARCHAR(255) NOT NULL,
    City               VARCHAR(100) NOT NULL,
    Capacity           INT UNSIGNED NOT NULL,
    Status             ENUM('Pending Approval','Approved','Rejected','Closed')
                       NOT NULL DEFAULT 'Pending Approval',
    PostedDate         DATE         NOT NULL DEFAULT (CURRENT_DATE),
    ApprovedByAdminID  INT UNSIGNED NULL,
    RejectionReason    TEXT         NULL,
    CONSTRAINT fk_opp_company FOREIGN KEY (CompanyID)         REFERENCES companies(CompanyID) ON DELETE RESTRICT,
    CONSTRAINT fk_opp_admin   FOREIGN KEY (ApprovedByAdminID) REFERENCES admins(AdminID)      ON DELETE SET NULL,
    CONSTRAINT chk_opp_capacity CHECK (Capacity > 0),
    INDEX idx_opp_search (Status, City, MajorRequirements)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 7. TRAINING PLACEMENT (one opportunity -> zero or one placement)
-- ---------------------------------------------------------------------
CREATE TABLE training_placements (
    PlacementID    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    OpportunityID  INT UNSIGNED NOT NULL,
    StartDate      DATE NOT NULL,
    EndDate        DATE NOT NULL,
    Status         ENUM('Active','Completed') NOT NULL DEFAULT 'Active',
    CONSTRAINT uq_placement_opp UNIQUE (OpportunityID),               -- BR-10
    CONSTRAINT fk_placement_opp FOREIGN KEY (OpportunityID)
               REFERENCES coop_opportunities(OpportunityID) ON DELETE RESTRICT,
    CONSTRAINT chk_placement_dates CHECK (EndDate > StartDate)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 8. STUDENT profile
-- ---------------------------------------------------------------------
CREATE TABLE students (
    StudentID           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    UserID              INT UNSIGNED NOT NULL,
    InstitutionalID     VARCHAR(20)  NOT NULL,                        -- college student number
    SupervisorID        INT UNSIGNED NULL,                            -- assigned by Admin (UC-21)
    PlacementID         INT UNSIGNED NULL,                            -- set when accepted
    Major               VARCHAR(150) NOT NULL,
    GPA                 DECIMAL(3,2) NULL,
    CVFilePath          VARCHAR(255) NULL,
    FailedCodeAttempts  TINYINT UNSIGNED NOT NULL DEFAULT 0,          -- 5 wrong codes
    CodeLockoutUntil    DATETIME     NULL,                            -- +10 minutes
    CONSTRAINT uq_student_user UNIQUE (UserID),
    CONSTRAINT uq_student_instid UNIQUE (InstitutionalID),
    CONSTRAINT fk_student_user       FOREIGN KEY (UserID)       REFERENCES users(UserID)                       ON DELETE RESTRICT,
    CONSTRAINT fk_student_supervisor FOREIGN KEY (SupervisorID) REFERENCES academic_supervisors(SupervisorID) ON DELETE SET NULL,
    CONSTRAINT fk_student_placement  FOREIGN KEY (PlacementID)  REFERENCES training_placements(PlacementID)   ON DELETE SET NULL,
    CONSTRAINT chk_student_gpa CHECK (GPA IS NULL OR GPA BETWEEN 0 AND 5)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 9. APPLICATION
-- ---------------------------------------------------------------------
CREATE TABLE applications (
    ApplicationID    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    StudentID        INT UNSIGNED NOT NULL,
    OpportunityID    INT UNSIGNED NOT NULL,
    Status           ENUM('Pending','Accepted','Rejected','Withdrawn') NOT NULL DEFAULT 'Pending',
    SubmittedDate    DATE NOT NULL DEFAULT (CURRENT_DATE),
    RejectionReason  TEXT NULL,
    CONSTRAINT uq_app_student_opp UNIQUE (StudentID, OpportunityID),   -- BR-08
    CONSTRAINT fk_app_student FOREIGN KEY (StudentID)     REFERENCES students(StudentID)               ON DELETE RESTRICT,
    CONSTRAINT fk_app_opp     FOREIGN KEY (OpportunityID) REFERENCES coop_opportunities(OpportunityID) ON DELETE RESTRICT,
    INDEX idx_app_status (OpportunityID, Status)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 10. DAILY CODE (one per placement per day)
-- ---------------------------------------------------------------------
CREATE TABLE daily_codes (
    DailyCodeID  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    PlacementID  INT UNSIGNED NOT NULL,
    Code         CHAR(6)  NOT NULL,
    CodeDate     DATE     NOT NULL,
    ExpiryTime   DATETIME NOT NULL,                                    -- CodeDate 23:59:59
    IsActive     TINYINT(1) NOT NULL DEFAULT 1,
    Status       ENUM('Active','Expired') NOT NULL DEFAULT 'Active',
    CreatedAt    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_code_placement_date UNIQUE (PlacementID, CodeDate),  -- BR-13
    CONSTRAINT fk_code_placement FOREIGN KEY (PlacementID)
               REFERENCES training_placements(PlacementID) ON DELETE RESTRICT,
    CONSTRAINT chk_code_format CHECK (Code REGEXP '^[0-9]{6}$')
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 11. ATTENDANCE RECORD
-- ---------------------------------------------------------------------
CREATE TABLE attendance_records (
    AttendanceID            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    StudentID               INT UNSIGNED NOT NULL,
    PlacementID             INT UNSIGNED NOT NULL,
    DailyCodeID             INT UNSIGNED NOT NULL,
    CheckInTime             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ApprovalStatus          ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
    ReviewComment           TEXT NULL,                                 -- required when Rejected (UC-14)
    ReviewedBySupervisorID  INT UNSIGNED NULL,
    ReviewedAt              DATETIME NULL,
    CONSTRAINT uq_att_student_code UNIQUE (StudentID, DailyCodeID),    -- BR-16
    CONSTRAINT fk_att_student    FOREIGN KEY (StudentID)   REFERENCES students(StudentID)                   ON DELETE RESTRICT,
    CONSTRAINT fk_att_placement  FOREIGN KEY (PlacementID) REFERENCES training_placements(PlacementID)       ON DELETE RESTRICT,
    CONSTRAINT fk_att_code       FOREIGN KEY (DailyCodeID) REFERENCES daily_codes(DailyCodeID)              ON DELETE RESTRICT,
    CONSTRAINT fk_att_reviewer   FOREIGN KEY (ReviewedBySupervisorID) REFERENCES academic_supervisors(SupervisorID) ON DELETE SET NULL,
    INDEX idx_att_status (PlacementID, ApprovalStatus)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 12. FINAL REPORT (one per student)
-- ---------------------------------------------------------------------
CREATE TABLE final_reports (
    ReportID        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    StudentID       INT UNSIGNED NOT NULL,
    PlacementID     INT UNSIGNED NOT NULL,
    SupervisorID    INT UNSIGNED NOT NULL,
    FilePath        VARCHAR(255) NOT NULL,
    SubmittedAt     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Status          ENUM('Submitted','Resubmitted') NOT NULL DEFAULT 'Submitted',
    ReviewStatus    ENUM('Pending','Approved','Revision Requested') NOT NULL DEFAULT 'Pending',
    ReviewComments  TEXT NULL,
    CONSTRAINT uq_report_student UNIQUE (StudentID),                   -- BR-18
    CONSTRAINT fk_report_student    FOREIGN KEY (StudentID)    REFERENCES students(StudentID)                   ON DELETE RESTRICT,
    CONSTRAINT fk_report_placement  FOREIGN KEY (PlacementID)  REFERENCES training_placements(PlacementID)       ON DELETE RESTRICT,
    CONSTRAINT fk_report_supervisor FOREIGN KEY (SupervisorID) REFERENCES academic_supervisors(SupervisorID)     ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 13. EVALUATION (one Company + one Academic per student per placement)
-- ---------------------------------------------------------------------
CREATE TABLE evaluations (
    EvaluationID     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    StudentID        INT UNSIGNED NOT NULL,
    PlacementID      INT UNSIGNED NOT NULL,
    EvaluatorUserID  INT UNSIGNED NOT NULL,
    EvaluatorType    ENUM('Company','Academic') NOT NULL,              -- BR-21
    Score            TINYINT UNSIGNED NOT NULL,
    Comments         TEXT NULL,
    EvaluatedAt      DATE NOT NULL DEFAULT (CURRENT_DATE),
    CONSTRAINT uq_eval UNIQUE (StudentID, PlacementID, EvaluatorType),
    CONSTRAINT fk_eval_student   FOREIGN KEY (StudentID)       REFERENCES students(StudentID)             ON DELETE RESTRICT,
    CONSTRAINT fk_eval_placement FOREIGN KEY (PlacementID)     REFERENCES training_placements(PlacementID) ON DELETE RESTRICT,
    CONSTRAINT fk_eval_user      FOREIGN KEY (EvaluatorUserID) REFERENCES users(UserID)                    ON DELETE RESTRICT,
    CONSTRAINT chk_eval_score CHECK (Score BETWEEN 0 AND 100)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 14. NOTIFICATION
-- ---------------------------------------------------------------------
CREATE TABLE notifications (
    NotificationID  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    UserID          INT UNSIGNED NOT NULL,
    Message         TEXT     NOT NULL,
    Link            VARCHAR(255) NULL,                                 -- page to open on click
    SentDate        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    IsRead          TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT fk_notif_user FOREIGN KEY (UserID) REFERENCES users(UserID) ON DELETE CASCADE,
    INDEX idx_notif_unread (UserID, IsRead)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 15. AUDIT LOG (append-only)
-- ---------------------------------------------------------------------
CREATE TABLE audit_logs (
    AuditLogID  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    UserID      INT UNSIGNED NULL,                                     -- NULL = failed login for unknown email
    Action      VARCHAR(100) NOT NULL,                                 -- e.g. LOGIN_SUCCESS, COMPANY_APPROVED
    EntityType  VARCHAR(50)  NULL,                                     -- e.g. 'Company', 'Application'
    EntityID    INT UNSIGNED NULL,
    IPAddress   VARCHAR(45)  NULL,                                     -- IPv4 / IPv6
    Details     TEXT         NULL,
    Timestamp   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_user FOREIGN KEY (UserID) REFERENCES users(UserID) ON DELETE RESTRICT,
    INDEX idx_audit_time (Timestamp),
    INDEX idx_audit_entity (EntityType, EntityID)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 16. USER PREFERENCE (language, email notifications, dark mode...)
-- ---------------------------------------------------------------------
CREATE TABLE user_preferences (
    UserID         INT UNSIGNED NOT NULL,
    PreferenceKey  VARCHAR(50)  NOT NULL,                              -- 'lang_ar', 'email_notifications'
    Enabled        TINYINT(1)   NOT NULL DEFAULT 1,
    PRIMARY KEY (UserID, PreferenceKey),
    CONSTRAINT fk_pref_user FOREIGN KEY (UserID) REFERENCES users(UserID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 17. CONVERSATION / MEMBERS / MESSAGES (internal messaging)
-- ---------------------------------------------------------------------
CREATE TABLE conversations (
    ConversationID  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    Type            ENUM('Direct','Group') NOT NULL DEFAULT 'Direct',
    Title           VARCHAR(150) NULL,
    CreatedAt       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE conversation_members (
    ConversationID  INT UNSIGNED NOT NULL,
    UserID          INT UNSIGNED NOT NULL,
    LastReadAt      DATETIME NULL,
    PRIMARY KEY (ConversationID, UserID),
    CONSTRAINT fk_cm_conv FOREIGN KEY (ConversationID) REFERENCES conversations(ConversationID) ON DELETE CASCADE,
    CONSTRAINT fk_cm_user FOREIGN KEY (UserID)         REFERENCES users(UserID)                 ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE messages (
    MessageID       BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ConversationID  INT UNSIGNED NOT NULL,
    SenderUserID    INT UNSIGNED NOT NULL,
    Body            TEXT     NOT NULL,
    SentAt          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_msg_conv   FOREIGN KEY (ConversationID) REFERENCES conversations(ConversationID) ON DELETE CASCADE,
    CONSTRAINT fk_msg_sender FOREIGN KEY (SenderUserID)   REFERENCES users(UserID)                 ON DELETE RESTRICT,
    INDEX idx_msg_conv_time (ConversationID, SentAt)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 18. CONTACT MESSAGE (public "Contact us" form, no login)
-- ---------------------------------------------------------------------
CREATE TABLE contact_messages (
    ContactID    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    SenderName   VARCHAR(150) NOT NULL,
    SenderEmail  VARCHAR(190) NOT NULL,
    Topic        VARCHAR(150) NOT NULL,
    Body         TEXT         NOT NULL,
    SentAt       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================================
--  TRIGGERS – make AUDIT_LOG append-only (NFR-SE-06)
-- =====================================================================
DELIMITER $$
CREATE TRIGGER trg_audit_no_update BEFORE UPDATE ON audit_logs
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'audit_logs is append-only';
END$$

CREATE TRIGGER trg_audit_no_delete BEFORE DELETE ON audit_logs
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'audit_logs is append-only';
END$$
DELIMITER ;

-- =====================================================================
--  EVENT – expire daily codes at midnight (NFR-SE-04)
--  (Alternative: cron/expire_daily_codes.php)
-- =====================================================================
SET GLOBAL event_scheduler = ON;   -- needs SUPER/SYSTEM_VARIABLES_ADMIN; or enable in my.cnf

CREATE EVENT IF NOT EXISTS ev_expire_daily_codes
ON SCHEDULE EVERY 1 DAY STARTS (CURRENT_DATE + INTERVAL 1 DAY)
DO
  UPDATE daily_codes
     SET IsActive = 0, Status = 'Expired'
   WHERE IsActive = 1 AND ExpiryTime < NOW();

-- =====================================================================
--  USEFUL VIEWS
-- =====================================================================
-- Approved opportunities visible to students (BR-07)
CREATE VIEW v_public_opportunities AS
SELECT o.OpportunityID, o.Title, o.Description, o.MajorRequirements, o.City,
       o.Capacity, o.PostedDate, c.CompanyID, c.CompanyName,
       (SELECT COUNT(*) FROM applications a
         WHERE a.OpportunityID = o.OpportunityID AND a.Status = 'Accepted') AS AcceptedCount
FROM coop_opportunities o
JOIN companies c ON c.CompanyID = o.CompanyID
WHERE o.Status = 'Approved' AND c.ApprovalStatus = 'Approved';

-- Approved training hours per student (attendance counts only after approval)
CREATE VIEW v_student_attendance_summary AS
SELECT s.StudentID, u.FullName, s.PlacementID,
       SUM(ar.ApprovalStatus = 'Approved') AS ApprovedDays,
       SUM(ar.ApprovalStatus = 'Pending')  AS PendingDays,
       SUM(ar.ApprovalStatus = 'Rejected') AS RejectedDays
FROM students s
JOIN users u ON u.UserID = s.UserID
LEFT JOIN attendance_records ar ON ar.StudentID = s.StudentID
GROUP BY s.StudentID, u.FullName, s.PlacementID;

-- Supervisor current load (UC-21 warning)
CREATE VIEW v_supervisor_load AS
SELECT sp.SupervisorID, u.FullName, sp.Department, sp.MaxStudentLoad, sp.ApprovalStatus,
       COUNT(s.StudentID) AS CurrentLoad
FROM academic_supervisors sp
JOIN users u ON u.UserID = sp.UserID
LEFT JOIN students s ON s.SupervisorID = sp.SupervisorID
GROUP BY sp.SupervisorID, u.FullName, sp.Department, sp.MaxStudentLoad, sp.ApprovalStatus;

-- =====================================================================
--  LEAST-PRIVILEGE APPLICATION ACCOUNT (3.4.2.3)
--  The PHP app connects with this user: no DDL, cannot delete audit logs.
-- =====================================================================
CREATE USER IF NOT EXISTS 'coop_app'@'localhost' IDENTIFIED BY 'ChangeThisStrongPassword!';
GRANT SELECT, INSERT, UPDATE, DELETE ON coop_db.* TO 'coop_app'@'localhost';
FLUSH PRIVILEGES;
