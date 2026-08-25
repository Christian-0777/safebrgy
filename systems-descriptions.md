# SafeBrgy Whole-System Discussion

## 1. Purpose and Current State

SafeBrgy is a PHP/MySQL barangay-management portal. It has two active user experiences:

- **Residents** register, wait for approval, sign in, submit document requests and reports, read announcements, and manage their personal data.
- **Administrators** sign in separately, review resident applications, process requests, manage reports and announcements, maintain the barangay identity, and inspect activity and notification logs.

The application is currently a combined codebase rather than one completely uniform application. The main live system uses the shared PDO connection in `config/db.php` and the `safebrgy` database. The older `resident-request-system/` folder uses its own MySQLi connection and the separate `barangay_resident_system` database. The `log in/`, `user dash/`, `userprofile/`, and `admin-role-access-features/` folders are earlier prototypes or alternative screens. They should be treated as legacy/reference implementations unless a route explicitly points to them.

The project also contains documentation and test utilities. Some files are empty placeholders, and some features display a success message without persisting data. This discussion describes what the source code actually does, including those limitations.

## 2. High-Level Architecture

```text
Browser
	| HTML forms, fetch/AJAX, JavaScript, uploaded files
	v
Apache / PHP routing (.htaccess)
	| public pages, admin pages, API handlers
	v
Session and role checks
	| resident session or admin_user session
	v
Shared PDO/MySQL database (live system)
	| users, residents, requests, reports, announcements, logs, OTP tables
	v
Email via PHPMailer/SMTP and SMS via Textbee (when configured)
```

Most PHP pages follow this pattern:

1. Include `config/db.php`, which includes `config/env.php` and returns a reusable PDO connection.
2. Start or reuse the PHP session.
3. Reject unauthenticated or wrong-role users.
4. Read query-string/form parameters and execute prepared SQL statements.
5. Render escaped HTML, or return JSON for an AJAX endpoint.
6. Send notifications and/or write logs when an operation changes system data.

`index.php` and `admin/index.php` are public gateways. Resident pages are mapped to clean URLs such as `/safebrgy/dashboard`; admin pages are mapped to `/safebrgy/admin/dashboard` by `.htaccess`.

## 3. Database Connection and Initialization

### 3.1 Environment configuration

`config/env.php` reads a root `.env` file when present and exposes `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_CHARSET`, `DB_INIT_SCHEMA`, and `APP_DEBUG` as constants. Defaults point to MySQL on `127.0.0.1:3306`, database `safebrgy`, user `root`, and an empty password, which is convenient for XAMPP but unsuitable for production.

### 3.2 Shared PDO bootstrap

`config/db.php` defines `safeBrgy_db_connect()`. The function uses a static `$pdo`, so all calls during one PHP request reuse one connection. PDO is configured with exceptions, associative fetches, and native prepared statements (`PDO::ATTR_EMULATE_PREPARES = false`).

On the first connection it:

- Reads and executes `sql/safebrgy_schema.sql` when schema initialization is enabled.
- Selects the configured database with `USE`.
- Migrates older installations by adding missing request statuses, `requests.date_received`, `users.cover_photo`, `users.two_factor_enabled`, resident ID back-image and cover-photo fields, and the auxiliary OTP/settings/read tables.
- Ensures the singleton `barangay_settings` row exists.
- Provides `generateResidentId()`, which uses `random_int()` and a prepared uniqueness check.

The authoritative live schema is `sql/safebrgy_schema.sql`. It creates the `safebrgy` database and the following model:

| Table | Role and relationships |
|---|---|
| `users` | Authentication identity, role (`resident` or `admin`), email, phone, password hash, verification, profile/cover paths, and 2FA flag. |
| `residents` | One resident profile per user, including identity, address, household, health, and valid-ID fields. Deleting the user cascades to this profile. |
| `requests` | Common document-request row, public `reference_no`, resident contact snapshot, status, timestamps, and optional supporting file. |
| `barangay_clearance`, `barangay_residency`, `barangay_indigency`, `barangay_business_clearance` | Type-specific request details linked to `requests.id`. |
| `reports` | Incident, lost-property, or blotter submissions linked to a user where possible. |
| `announcements` | Admin-authored notices, priority, status, schedule, audience JSON, attachments JSON, pin/archive flags. |
| `announcement_reads` | Unique user/announcement read marker. |
| `admin_logs` | Administrator actions and JSON metadata. |
| `sms_logs` | Email/SMS delivery attempts, event metadata, recipient, and status. |
| `email_logs` | Schema support for email history; the current mailer primarily records notification outcomes in `sms_logs`. |
| `registration_otps` | Hashed registration codes with expiry and consumption state. |
| `password_reset_otps` | Hashed resident reset codes linked to the user and consumable once. |
| `barangay_settings` | Singleton public identity/configuration record. |
| `officials` | Optional officials catalogue; the current landing page hard-codes officials instead of querying this table. |

`ERD.md` documents these entities and foreign-key relationships. The schema uses foreign keys, unique identifiers, enums, indexes, JSON validity checks, and InnoDB transactions.

### 3.3 Legacy database connection

`resident-request-system/config.php` creates a separate MySQLi connection to `barangay_resident_system`. `resident-request-system/schema.sql` is its separate schema, and `resident-request-system/includes/functions.php` uses MySQLi prepared statements and helper functions. This subsystem is not the same database bootstrap as `config/db.php`; mixing the two databases can make records appear missing if the wrong route is used.

## 4. Authentication and Registration Flow

### Resident login

`public/login.php` accepts an email and password, loads the matching user with a prepared query, verifies `password_hash` with `password_verify()`, rejects unverified accounts, loads resident contact details, and writes `$_SESSION['user']`. Admin users are redirected to the admin dashboard; residents go to the resident dashboard. Client-side validation in the inline script is only convenience validation.

### Admin login

`admin/login.php` renders the admin form. `admin/admin_auth.php` validates the POST request, restricts the database lookup to `role = 'admin'`, verifies the password and `is_verified`, and creates `$_SESSION['admin_user']`. `admin/admin_protect.php` is included by protected admin pages; it redirects if that session is absent and supplies a few compatibility session values.

### Resident registration

`register.php` and `public/register.php` are compatibility entry points that forward to the current `register/index.php`, the seven-step registration wizard. `register/includes/functions.php` contains the option lists, CSRF token helpers, Philippine-phone normalization, age calculation, JSON responses, and image-upload validation. `register/assets/js/main.js` keeps all steps in the DOM, validates them, calculates age, handles image/camera input, requests an OTP, displays the review step, and finally submits the complete `FormData` payload.

The live API flow is:

1. `register/api/send_otp.php` requires POST and a valid CSRF token, creates a six-digit code with `random_int()`, stores an HMAC hash in `registration_otps` for five minutes, and sends the code by email.
2. `register/api/verify_otp.php` checks the current code without consuming it, so it only provides immediate UI feedback.
3. `register/api/register.php` revalidates every field and file server-side, checks email uniqueness, verifies the still-valid OTP with `hash_equals()`, stores validated images, hashes the password, inserts `users` and `residents` in one transaction, and consumes the OTP. New users remain `is_verified = 0` until an administrator approves them.

The older `public/verify_otp_process.php` contains a separate session-based registration/login OTP flow. It is retained for older routes but is not the authoritative current wizard flow.

### Admin registration and OTP

`admin/register.php` renders the form. `admin/admin_register_process.php` validates email, phone, password strength, terms acceptance, and uniqueness; creates an unverified admin; sends a seven-digit email OTP; and stores the pending verification data in the session. `admin/otp-view.php`, `admin/verify_otp_process.php`, and `admin/resend_otp.php` render, verify, and resend the five-minute admin OTP. Successful verification sets `$_SESSION['admin_user']`.

### Password reset

The current resident reset flow is `public/reset-password.php`, `public/send-reset.php`, `public/verify-reset-code.php`, and `public/confirm-password-reset.php`, driven by `assets/js/public/reset-password.js`. It identifies a verified resident, stores a hashed expiring code in `password_reset_otps`, verifies it, and changes the password inside a transaction while marking the code consumed. `admin/reset-password.php` and `assets/js/admin/reset-password.js` are an older admin reset UI; the admin implementation should be checked before relying on it because it is separate from the resident reset tables.

### Logout

`public/logout.php` and `admin/logout.php` clear the session, remove the session cookie using its existing cookie parameters, destroy the session, and redirect to the appropriate landing page.

## 5. Resident Pages and Data Fetching

All current resident pages require `$_SESSION['user']['role'] === 'resident'` and use `config/db.php`.

### Dashboard: `public/public-pages/dashboard.php`

The page reads the resident identity from the session, counts pending and approved requests by `resident_email`, counts reports by `user_id`, counts recent request updates, fetches five active non-archived announcements, and fetches recent grouped request updates. It renders activity cards, recent announcements, service links, and shared header/sidebar components. `assets/js/public/dashboard.js` supplies date/time and client interaction.

### Requests: `public/public-pages/requests.php`

The page fetches the current resident's request history by email and joins each request to all four detail tables to obtain a purpose. Its modal forms cover clearance, residency, indigency, and business clearance. `assets/js/public/request.js` opens modals, validates/serializes forms, uploads optional files, posts to `api/requests/create.php`, shows the response, and adds the new request to the table without a full reload. That endpoint forwards to `resident-request-system/submit_request.php`, so the request write currently uses the legacy MySQLi connection and its transaction. The endpoint inserts the master request and one type-specific row, generates a reference number, and sends submission notifications.

### Reports: `public/public-pages/reports.php`

The page queries reports by the logged-in `user_id`, calculates status counts, and renders searchable/filterable report rows. `assets/js/public/reports.js` supports the create-report modal and detail loading. `api/reports/create.php` validates the report type, optionally validates and stores an image, creates a case number, inserts a pending report, and sends notification email/SMS. `api/reports/get.php` returns a report only when both report ID and session user ID match, which prevents one resident from reading another resident's report.

### Announcements: `public/public-pages/announcement.php`

The page accepts a search term and safe sort choice, then uses a prepared query for active, non-archived announcements. It joins the author, decodes audience and attachment JSON, escapes displayed text, and renders detail modals. `assets/js/public/announcement.js` handles the interface. `api/announcement-noted.php` permits residents only, checks that the announcement is active, and inserts a unique `(announcement_id, user_id)` marker into `announcement_reads`.

### Profile: `public/public-pages/profile.php`

The page joins `residents` and `users` for the current user, resolves profile/cover/ID paths, fetches the latest ten requests by resident email, and renders personal, address, contact, identification, and request-history sections. `assets/js/public/profile.js` handles profile interactions. Output is escaped with `htmlspecialchars()`.

### Account: `public/public-pages/account.php`

The page fetches the full resident profile plus user account fields and prepares the settings tabs for personal data, contact, ID images, security, notification preferences, privacy, support, and account deletion. `assets/js/public/account.js` handles tabs, previews, drag-and-drop, and form submission. The handlers are:

- `api/account/update_personal.php`: updates resident personal fields and the session name.
- `api/account/update_contact.php`: updates user email/phone and resident mobile/emergency contact data after basic validation.
- `api/account/update_password.php`: verifies the current password, validates strength, hashes the new password, and updates it.
- `public/public-pages/update_account.php`: older combined email/phone/password handler retained for compatibility.
- `api/account/update_id.php`: validates and stores both ID images, then updates resident paths.
- `api/account/update_cover.php`: validates a real image with MIME and image checks and updates the resident's `users.cover_photo` path.
- `api/account/update_notifications.php`: stores preferences in the session only; it does not yet have a database preferences table.
- `api/account/download_data.php`: exports the current user, resident row, requests, and reports as JSON.
- `api/account/delete_account.php`: requires the literal `DELETE`, removes resident/user data in a transaction, and destroys the session.
- `api/account/deactivate_account.php`: currently destroys the session and redirects; it does not persist deactivation because the status schema is not implemented.
- `api/account/send_contact.php`, `send_feedback.php`, and `report_issue.php`: validate the request and display a success flash message, but their TODO sections do not currently send/store the submitted content.

### Notifications placeholder

`public/public-pages/notifications.php` and `admin/main-pages/notifications.php` are empty files. The dashboard links to the resident file, but no page is rendered there. Current notification information is represented by recent request updates and delivery logs rather than a standalone notification page.

## 6. Administrator Pages and Data Fetching

All current admin main pages include `admin/admin_protect.php` and therefore require an admin session.

### Dashboard: `admin/main-pages/dashboard.php`

The dashboard loads the administrator identity and queries resident totals, voter totals, age groups, disability counts, request totals, report cases, recent admin/request activity, pending verifications, pending documents by type, scheduled announcements, and pending reports. It renders statistics, charts/progress areas, calendar data, activity, and notification summaries. `assets/js/admin/dashboard.js` provides chart/UI behavior.

### Requests: `admin/main-pages/requests.php`

The page detects compatible request column names for old schemas, joins users/residents and all detail tables, applies safe search/status/sort choices, and calculates status totals. Its POST branch accepts a fixed action and fixed status list, updates the request, finds the resident, and calls `sendRequestStatusNotification()`. `assets/js/admin/requests.js` posts status changes and reloads the page. `api/admin/get_request.php` and `api/admin/update_request_status.php` are empty placeholders; the active handlers are inside this page.

### Reports: `admin/main-pages/reports.php`

The page queries all reports with user/resident identity, supports search, and calculates status statistics. Its AJAX branch returns one report for a modal or updates a report status and sends a resident notification. `assets/js/admin/reports.js` loads detail JSON, safely escapes values before constructing modal HTML, and posts status updates.

### User verification: `admin/main-pages/user_verification.php`

The page fetches pending and verified residents, counts pending/verified/rejected statistics, and renders actions. `assets/js/admin/user_verification.js` calls `view_user.php` to load a resident modal and `verify_user.php` to approve or reject. `view_user.php` joins `users` and `residents`, returns escaped HTML including ID/profile/cover previews, and `verify_user.php` updates or deletes the account, sends email/SMS, writes `sms_logs`, and records an `admin_logs` action.

### Announcements: `admin/main-pages/announcement.php`

The page supports create, pin, archive, and delete actions in its POST/AJAX branch. Creation validates title/description, stores image attachments, builds target-audience JSON, sets active/scheduled status, inserts the announcement, and sends notifications to resident accounts. Listing queries include author, read count, resident count, filters, archived view, and sorting. `assets/js/admin/announcement.js` drives forms, uploads, modals, and action buttons. `announcement_backup.php` is an older backup implementation and is not used by the clean route.

### Admin profile: `admin/main-pages/profile.php`

The page reads admin account/profile/cover fields, counts managed residents, processed documents, announcements, and handled cases, then fetches the last twenty admin logs. `assets/js/admin/profile.js` can build a client-side CSV download from the rendered data.

### Admin settings: `admin/main-pages/account_settings.php`

The page reads administrator data, the singleton barangay settings row, and a union of `admin_logs` and `sms_logs` limited to 200 rows. Tabs cover administrator account fields, barangay identity/logo, security/2FA, and maintenance. `admin/update_settings.php` validates and persists account or barangay settings, stores uploaded images, writes admin logs, and updates `two_factor_enabled`. The maintenance buttons are explicitly marked coming soon. `assets/js/admin/account_settings.js` controls tabs, previews, the maintenance modal, and the 2FA request.

### Admin logs compatibility route

The clean route `/safebrgy/admin/logs` maps to `admin/logs/logs.php`, but that file is not present in the supplied file inventory. The old route is therefore documented in `.htaccess` but may fail unless the missing file exists outside the current workspace state.

## 7. Notifications and External Services

`config/mailer.php` loads Composer autoloading and provides the shared notification layer. It reads SMTP, SendGrid, and Textbee configuration from the environment, builds branded HTML email, sends SMTP mail through PHPMailer when configured, and sends SMS through Textbee using cURL when credentials are configured. `sendSms()` normalizes Philippine numbers. Notification helpers cover OTPs, request/report submission, request/report status changes, announcements, and account approval/rejection. `logNotificationEvent()` records delivery results in `sms_logs`.

`composer.json` declares PHPMailer and SendGrid; `composer.lock` pins the dependency graph; `vendor/` contains Composer's autoloader and the installed PHPMailer, SendGrid, and Starkbank packages. The current source uses PHPMailer directly and leaves SendGrid imports commented out.

## 8. Security Controls Actually Present

- PDO and MySQLi prepared statements are used for most database values, reducing SQL injection risk.
- Passwords are stored with `password_hash()` and checked with `password_verify()`.
- Resident and admin routes perform server-side session and role checks.
- Registration APIs require a CSRF token and POST method.
- Registration OTPs and reset OTPs are stored as HMAC hashes, expire, and are consumed/invalidated.
- OTP and reset targets are masked in the UI in the current flows.
- File uploads use size limits, MIME/image checks in the newer registration and cover/ID handlers, generated names, and controlled directories.
- User-supplied HTML output is generally escaped with `htmlspecialchars()`; report modal values are escaped in JavaScript before insertion.
- Foreign keys and transactions protect related records and multi-table registration/request writes.
- Announcement read rows have a composite unique key, making repeated marking idempotent.
- Admin actions and notification results are logged.

These controls are not universal. The source still has security and reliability gaps that should be considered before production deployment:

- `config/env.php` enables `APP_DEBUG` by default and uses empty default database credentials.
- Several older OTP flows keep plaintext codes in PHP sessions, and the older resend endpoint uses `rand()` rather than `random_int()`.
- Some admin AJAX actions do not include a CSRF token, and several handlers do not verify that an ID belongs to the intended admin scope.
- Some upload handlers trust the client MIME field or file extension in addition to server checks; uploaded directories should block script execution and private ID files should not be directly public.
- Resident requests are associated by email in the main request table, while reports use `user_id`; changing an email can make request history inconsistent.
- Login throttling, session ID regeneration after login, secure cookie settings, rate limiting, and centralized authorization middleware are not consistently visible.
- `clean_str()` applies HTML escaping before storage, which can produce double-escaping or data corruption when values are later escaped at output.
- `send_contact.php`, feedback, issue reporting, notification preferences, deactivation, and maintenance/backup controls are incomplete.
- Error responses in some legacy handlers expose exception messages. Production responses should be generic while detailed errors go only to server logs.

## 9. Routing and Shared Frontend Files

`.htaccess` redirects physical PHP paths to clean public/admin URLs and internally rewrites clean URLs to the correct PHP file. It also keeps the two landing pages outside the clean-page route group.

Shared resident/admin presentation and behavior are implemented by:

- `assets/css/shared/colors.css`: design tokens, colors, dimensions, and shared variables.
- `assets/css/shared/layout.css`: global layout, typography, cards, forms, tables, modals, and responsive rules.
- `assets/css/shared/auth.css`: login/register/OTP/reset presentation.
- `assets/css/shared/shared-header.css` and `shared_sidebar.css`: authenticated header/sidebar styling.
- `assets/css/shared/notifications.css` and `loading-overlay.css`: notification/loading styles.
- `assets/js/shared/shared-header.js`: profile menu, placeholder notification/search behavior, and logout redirect.
- `assets/js/shared/shared-sidebar.js`: active links, desktop collapse, and generated mobile navigation.
- `assets/js/shared/layout_functions.js`, `logo_functions.js`, `nav-toggle.js`, and `notification.js`: shared layout, logo, navigation, and notification helpers.
- `assets/js/shared/loading-overlay.js`: shared processing overlay and unload behavior.

Page-specific JS/CSS files under `assets/js/public`, `assets/js/admin`, and `assets/js/admin-role-access-features`, together with matching CSS folders, provide modal logic, validation, previews, status controls, charts, and responsive styling. Empty `assets/css/public/modals/otp-modal.css` is a placeholder.

## 10. Landing Pages

`index.php` loads the shared DB bootstrap but mainly renders a public landing page: branding, service catalogue, hard-coded officials, registration notice, contact/footer content, and login-protected service prompts. `assets/style.css`, `assets/css/public/modals/login.css`, and the shared scripts style and animate it.

`admin/index.php` is the public administrator gateway. `assets/admin_landing.css` and `assets/admin_landing.js` provide the hero, navigation, mobile menu, and admin-login link. It does not expose administrative data before authentication.

`external-links/terms-of-service.html` and `privacy-policy.html` are standalone legal pages, styled by `external-links/shared.css` and behavior-enhanced by `external-links/shared.js`.

## 11. Legacy, Prototype, Test, and Documentation Files

- `log in/login.html`, `process-login.php`, `dashboard.php`, `logout.php`, and `CREDENTIALS.txt` form an old demo login using hard-coded users, a plaintext-style remember cookie, and `$_SESSION['user_email']`; it does not use the live `users` table.
- `user dash/dashboard.html`, `user dash/script.js`, and `user dash/styles.css` are a static resident-dashboard prototype.
- `userprofile/index.html`, `script.js`, and `styles.css` are a static profile prototype.
- `admin-role-access-features/dashboard.php`, `announcement.php`, `reports.php`, `request.php`, and `verify.php`, with their matching JS/CSS, are an alternate/older admin feature set and are not the clean-route admin pages.
- `resident-request-system/index.php` is a standalone request-demo UI that calls its own `includes/functions.php` and displays all requests from its separate database. `resident-request-system/README.md` explains that demo, `schema.sql` defines its database, `js/script.js` controls its modals/AJAX, `css/style.css` styles it, and its `.gitkeep` upload marker preserves the upload folder.
- `includes/loading/loading_overlay.php`, `script.js`, and `style.css` are a standalone loading-overlay demonstration. The live pages instead use `assets/js/shared/loading-overlay.js` and its matching CSS.
- `register/README.md` documents the current registration subsystem. `register/database/.htaccess`, `register/includes/.htaccess`, and `register/uploads/.htaccess` are directory-level web-server protection/configuration files; the registration `uploads/.htaccess` applies to the generated ID/profile/cover media folders.
- `test_sms.php` is an SMS testing utility; `tmp_submit_test.php` is a temporary request-submission test utility. These should not be exposed in production because test endpoints can disclose configuration or trigger real notifications.
- `ADMIN_REQUESTS_DOCUMENTATION.md`, `README.md`, `wiki.md`, `landing_pages.md`, and `ERD.md` are project documentation. They describe releases, setup, landing pages, request administration, and database relationships; this file consolidates the runtime behavior across the whole repository.
- `.gitignore` controls version-control exclusions. `composer.json` and `composer.lock` define PHP dependencies.

## 12. Static Assets and Upload Storage

`assets/img/seal.png` and `assets/img/hero.jpg` are shared branding assets. `uploads/announcements`, `uploads/cover_photos`, `uploads/profile_images`, `uploads/reports`, and `uploads/id` contain runtime-generated media referenced by database paths. The newer registration wizard also writes profile and cover media under `register/uploads/profile` and `register/uploads/cover`, while `resident-request-system/uploads` belongs to the legacy request demo.

These uploaded files are data, not application logic, so the system must protect their directories with web-server rules, access control, backups, and retention policies. Valid IDs and profile data are personally sensitive even when their file names are randomized.

## 13. End-to-End Example

For a resident requesting a barangay clearance:

1. The resident signs in through `public/login.php`; the server verifies the hash and creates the resident session.
2. `public/public-pages/requests.php` loads that resident's history and renders the clearance modal.
3. `assets/js/public/request.js` sends the form and optional upload to `api/requests/create.php`.
4. The forwarding handler `resident-request-system/submit_request.php` validates the document type, writes `requests`, writes `barangay_clearance`, commits the transaction, and sends notification delivery attempts.
5. The resident sees the returned reference number and pending status.
6. An admin opens `admin/main-pages/requests.php`, which fetches the request through joined user/resident/detail queries.
7. `assets/js/admin/requests.js` posts an allowed status to the admin page's AJAX branch.
8. The server updates the status, sends email/SMS, and records the delivery result in `sms_logs`; the admin action itself is handled by the page, although request status logging is less complete than verification/settings logging.
9. The resident dashboard and request history display the updated status on the next fetch.

This same pattern applies to reports and announcements: a protected page fetches scoped data, JavaScript submits an API/page action, the server validates and persists it, and notification/logging code communicates the result.

## 14. Recommended Operational Interpretation

For deployment and maintenance, treat the PDO-backed `safebrgy` system, `public/`, `admin/`, `api/`, `register/`, `config/`, `sql/`, `assets/`, and root `.htaccess` as the primary application. Treat `resident-request-system/`, `log in/`, `user dash/`, `userprofile/`, and `admin-role-access-features/` as legacy or prototype surfaces until they are either removed, isolated, or deliberately brought under the same database, authentication, CSRF, upload, and authorization model.
