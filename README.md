# COOP – Cooperative Training Management System

MIS 451 capstone, Jubail Industrial College. Built from the *COOP Development Guide* (Deliverables 1–3) with the look of the **COOP Design System**.

**Stack:** HTML5 · CSS3 + Bootstrap 5.3.3 (LTR + RTL) · vanilla JavaScript · PHP 8.1+ (no framework, PDO) · MySQL 8.0.16+ · Chart.js 4.4.1

## Run it locally

1. Install XAMPP / Laragon (PHP 8.1+ with `pdo_mysql`, `mbstring`, `fileinfo`) and MySQL 8.
2. Create the database, tables, views, triggers, event and the `coop_app` account:
   ```bash
   mysql -u root -p < database/schema.sql
   ```
3. Copy `.env.example` to `.env`. Set `DB_PASS` to the `coop_app` password from `schema.sql`, and change that password before going live.
4. Load demo data, or create only the first admin:
   ```bash
   php database/seed.php
   php database/create_admin.php "COOP Admin" admin@ecoop.test "Coop2026!"
   ```
5. Serve `public/` as the web root:
   ```bash
   php -S 127.0.0.1:8080 -t public public/index.php
   ```
   With Apache, point the `DocumentRoot` (or a virtual host) at `coop/public`; `.htaccess` routes every request to `index.php`.
6. Make `storage/` writable by the web server.
7. Schedule the cron jobs from `cron/` (see each file's first line):
   - `expire_daily_codes.php` and `complete_placements.php` (nightly)
   - `attendance_reminders.php` (each training morning, Sun–Thu)
   - `weekly_summary.php` (Thursday afternoon)
   - `backup.sh` (daily)

## Demo accounts (after `seed.php`)

Every demo account uses the password **`Coop2026!`**.

| Email | Role |
| --- | --- |
| `admin@ecoop.test` | COOP office admin |
| `supervisor@ecoop.test` | Academic supervisor (Dr. Khalid Omar) |
| `company@ecoop.test` | Company (Nova Systems) |
| `student@ecoop.test` | Student placed in Backend Developer Intern |
| `newstudent@ecoop.test` | Student with no placement (try applying) |

More sample accounts use the same password:
- Companies: `maaden@ecoop.test` (approved) and `gulftech@ecoop.test` (waiting for approval)
- Supervisor: `noura@ecoop.test`
- Students: `abdullah@ecoop.test`, `yazan@ecoop.test`, `reem@ecoop.test`, `lina@ecoop.test`

Emails (password reset, notifications) are written to `storage/logs/mail.log` while `MAIL_DRIVER=log`.

## Tests

With the server running on port 8080 and a fresh seed:

```bash
node tests/e2e-pages.js
```
Opens all 46 role pages and checks that cross-role URLs return 403.

```bash
mysql -u root < database/schema.sql && php database/seed.php && node tests/e2e-flow.js
```
Runs 39 checks covering the whole workflow and the security checklist in guide §3.9.

## Pages added in the latest round

- **Public:** FAQ (`/faq`, by role) and Privacy (`/privacy`, a full notice), linked from the header and footer.
- **Company:** Applicant profile (`/company/applicants/{id}`) with major, GPA, contacts, CV, seat count, Accept/Reject and a message button. Applicant search now works.
- **Admin:**
  - User details (`/admin/users/{id}`): account, role details and recent activity, with Suspend/Activate.
  - Placements (`/admin/placements`): every placement with progress, trainees and Mark completed.
  - **System report** (`/admin/report`), with Print and Download CSV. It covers:
    - headline numbers and a "Needs attention" to-do list
    - top companies and supervisor load
    - users, companies, opportunities, applications, placements, attendance, reports and evaluations
    - security (failed logins, lockouts, blocked access)
    - the system (PHP and MySQL versions, database and file size, last backup, scheduler, mail, debug, HTTPS)

`tests/e2e-links.js` crawls every page for every role. It checks that each link opens and that every button has an action.

## What is implemented

Every use case from UC-01 to UC-23, plus:

- Messages and notifications, with an unread badge that polls every 30 s
- Settings: language, dark mode, email notifications, password and photo
- The public pages: home, about with the privacy notice, and the contact form
- An English and Arabic (RTL) interface

**Security:**
- bcrypt passwords
- PDO prepared statements only
- Escaped output and a Content-Security-Policy with no inline scripts
- CSRF tokens on every POST
- Session: ID regenerated at login, HttpOnly + SameSite cookies, 30-minute idle timeout
- Account locked for 15 minutes after 5 failed logins
- Check-in blocked for 10 minutes after 5 wrong codes
- Role checks on every route, plus record-level checks
- Suspended users are logged out immediately; an admin cannot suspend their own account
- Uploads: file type checked from the contents, 10 MB limit, random names, stored outside `public/`
- Append-only audit log, protected by database triggers
- A transaction with a row lock so accepting applicants can never exceed capacity

## Differences from the guide

- **No Composer packages.** A small `.env` loader replaces phpdotenv, and `MailService` logs emails instead of using PHPMailer. Install PHPMailer to send real SMTP email.
- **`database/seed.php` instead of `seed.sql`.** It hashes the passwords and creates sample PDF files.
- **One model file per table, as listed in the guide.** Most queries live in services and controllers.

## Logo and static HTML preview

- **Logo files** are in `public/assets/img/`:
  - `logo.png`: the full logo with a transparent background
  - `logo-mark.png`: the hexagon mark, used in the dashboard sidebar and as the app icon
  - `favicon.png`: the browser-tab icon
- **`coop-website.html`** is a single self-contained file holding **every page of the site for every role, in English and Arabic** (122 pages). It opens in any browser without PHP.
  - Every link leads to a real page, including every filter tab (Pending, Accepted, …). CV and report links download the embedded PDFs.
  - **Buttons act like the real site** until you reload. Examples: Accept and Reject, Approve, Suspend, Assign, Generate code, Check in, Apply, Send message, Mark all read, Save, and Contact. Search boxes and filters work too. `tests/preview-buttons.js` checks this.
  - To see a role, log in on the preview's login page with that role's demo email. Log in, Log out and Register move between pages; other forms show a "preview only" message.
- **Rebuild it after changes** while the PHP server is running with demo data:
  ```bash
  python3 tools/export_html.py
  ```

## Profile photo and language

- **Change your photo:** press the round photo (or initials) at the top right of any dashboard page, or the large photo on the Settings page. Pick a JPG, PNG or WebP up to 2 MB. It shows everywhere straight away and uploads in the background. "Change photo" is also in the account menu.
- **Dark mode:** the site opens in dark mode. The sun/moon button in the top bar (and in the public header) switches to light and back instantly. Signed-in users keep their choice on their account; visitors keep it for the visit. The Settings page has the same switch.
- **Change email:** Settings → Change email. Enter the new address and your current password. The address must not already be in use, and students must use a college domain. The old address gets an email saying it changed.
- **Switch language:** use the العربية / English button in the top bar (language is no longer a Settings option). The same page reloads in the other language and the choice is remembered for your account.
- **In `coop-website.html`:** both work the same way. The chosen photo is saved in your browser only, because the file has no server.
- **Note on registration:** the self-registration form still requires a college domain from `STUDENT_EMAIL_DOMAIN` (comma-separated; default `jic.edu.sa,ecoop.test`).

## Run it on GitHub

**Live preview (GitHub Pages).** `docs/index.html` is the static preview of every page in English and Arabic.
1. After pushing, open the repository's **Settings → Pages → Source** and choose **GitHub Actions**. This is needed only once.
2. The **Deploy preview to GitHub Pages** workflow publishes it at `https://<user>.github.io/<repo>/`.
3. Pages cannot run PHP or MySQL, so forms only show a "preview only" message there.

**The real PHP + MySQL site (GitHub Codespaces).**
1. Press **Code → Codespaces → Create codespace on main**.
2. `.devcontainer/` starts PHP 8.3 and MySQL 8.0, imports `schema.sql`, loads the demo data and serves the site on port 8080.
3. The site opens in your browser. Log in with any demo account; the password is `Coop2026!`.

**Tests (GitHub Actions).** `.github/workflows/tests.yml` runs the six browser test suites against a real MySQL 8 on every push.

After changing pages, rebuild the preview while the site is running, then commit `docs/index.html`:

```bash
python3 tools/export_html.py docs/index.html
```

## Settings page

Settings is in the COOP design: white cards with mint switches and buttons.
- **Profile card:** your photo (press it, or use "Change photo", for PNG, JPG or WebP up to 5 MB), name, role and email.
- **Profile:**
  - Name, email and phone for every role, plus each role's own fields: GPA, major and CV (with Remove) for students; company name and city for companies; department for supervisors.
  - Changing the email asks for your current password. The new address must not be in use, students must use a college domain, and the old address is told by email.
- **Notifications:** Attendance reminders, Application updates, Reports and evaluations, and Weekly summary. Each saves as soon as you flip it, and decides which emails you get. The two cron jobs above send the reminders and the summary.
- **Appearance:** Language (English / العربية) and Theme (Light / Dark). Dark is the default.
- **Security:** change password.
- **Help:** FAQ, message the co-op office, and the privacy notice.

## Supervisor: change an attendance decision

A supervisor can change a check-in that was already approved or rejected:
1. Press **Change** on the Attendance review page (Approved or Rejected tab) or in a student's attendance history.
2. Pick **Approved** or **Rejected**. Rejecting needs a comment, which the student sees.
3. The student is notified, and the audit log records the old and new status.

Only the student's own supervisor can do this. `tests/e2e-attendance-change.js` checks it.

## Supervisor registration

Academic supervisors can sign up at **/register/supervisor**. The link is on the login page, the home page and the other sign-up pages.
1. They use a college email (`STUDENT_EMAIL_DOMAIN`), a department and a phone number.
2. The account starts as **waiting for approval**. The supervisor can log in and sees a notice, but cannot be assigned students yet.
3. The admin is notified, and the dashboard and System report show a reminder. In **Users → Academic supervisor**, the admin chooses **Approve** or **Reject** (with a reason).
4. Approving notifies the supervisor. Rejecting turns the account off and emails the reason.

Supervisors the admin creates, and the demo ones, are approved straight away. Existing databases need `database/migrations/2026_11_supervisor_registration.sql` once. `tests/e2e-register-supervisor.js` covers all of this.
