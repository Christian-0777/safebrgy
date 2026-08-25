# SafeBrgy System Documentation

## 1. Introduction

SafeBrgy is a digital barangay service and administration system. It gives residents a convenient way to register, request official documents, submit community reports, read announcements, and maintain their account. It gives barangay administrators one protected workspace for verifying residents, processing documents, managing reports, publishing announcements, and reviewing operational activity.

### Purpose

The system connects residents and barangay personnel through one controlled information flow:

```text
Resident information and requests
		|
		v
	SafeBrgy application
		|
		v
Administrative review, action, and notification
```

Its purpose is to make common barangay services more accessible, traceable, and organized while reducing repetitive manual encoding.

### Problem Being Addressed

Traditional processing can require residents to visit the barangay office repeatedly, carry paper documents, and ask for updates in person. Staff may also need to search separate records, manually track document status, and communicate the same updates through several channels. SafeBrgy addresses these problems by providing:

- online registration and account verification;
- resident profiles containing reusable information;
- electronic document-request forms and supporting uploads;
- incident, lost-property, and blotter reporting;
- status tracking through reference and case numbers;
- announcements for public information;
- administrator dashboards, filters, logs, and notifications; and
- a shared database that keeps related records connected.

The system is not only an online form. Its value comes from connecting submission, review, status changes, notification, and recordkeeping into one workflow.

## 2. Two Sides of the Website

SafeBrgy has two user-facing sides. They use the same application foundation and database, but they have different responsibilities and access rules.

### Resident side

The resident side is designed for self-service. A resident can:

1. View the public landing page and available services.
2. Create an account using the registration wizard and email OTP.
3. Sign in after account verification.
4. View the Dashboard page for requests, reports, announcements, and recent activity.
5. Submit a Barangay Clearance, Barangay Residency, Barangay Indigency, or Barangay Business Clearance request.
6. Attach permitted supporting documents when needed.
7. Submit an Incident, Lost Property, or Blotter report with optional attachments.
8. Read announcements and mark them as read.
9. View Profile information and recent request history.
10. Update account information, identification images, profile media, password, and preferences.
11. Download personal data or permanently delete the account.

Resident records are scoped to the signed-in account. A resident can see their own requests and reports, but cannot access another resident's information.

### Administrator side

The administrator side is designed for controlled operations. An administrator can:

1. View the administrator landing page and sign-in entry point.
2. Register an administrator account and verify it with an OTP.
3. View the administrative Dashboard page with system totals and pending work.
4. Approve or reject resident registrations after reviewing submitted information and IDs.
5. Search, filter, inspect, and update document-request statuses.
6. Review reports and move them through operational statuses.
7. Create, schedule, pin, archive, and delete announcements.
8. View the administrator Profile and activity summary.
9. Update account security and barangay identity settings.
10. Review administrative and notification activity logs.

Every protected administrator page checks the administrator session before reading or changing administrative data. Administrative actions are separate from resident actions even though both sides use the same database records.

## 3. Page-by-Page Use and Shared Files

The pages are rendered by PHP and enhanced with JavaScript and CSS. Shared files provide common behavior so that authentication, layout, loading states, navigation, database access, and notifications do not need to be rewritten for every page.

### Public and resident pages

| Page | How it is used | Shared connection |
|---|---|---|
| Landing page | Introduces the barangay, services, officials, contact information, and entry points for residents. | Shared branding, layout, logo, and navigation helpers; database settings may supply public identity information. |
| Registration page | Collects resident identity, address, contact, household, ID, image, and password information through a multi-step form. | Registration validation helpers, CSRF helper, upload validation, OTP APIs, shared database connection, and mailer. |
| Login page | Accepts credentials, verifies the password, checks account approval, and creates a resident session. | Database connection, session protection, shared authentication styling, and loading feedback. |
| OTP page | Confirms registration or other account-verification codes. | OTP validation APIs, mailer, session handling, shared authentication styles. |
| Password-reset pages | Request, verify, and consume an expiring reset code before changing a password. | Database connection, hashed OTP storage, mailer, authentication styling, and shared form behavior. |
| Dashboard page | Summarizes request counts, report counts, announcements, and recent request activity for the current resident. | Resident session, database queries, shared header/sidebar, layout, date helpers, notifications, and loading overlay. |
| Announcement page | Lists active announcements, supports search and sorting, and opens announcement details. | Database connection, announcement-read API, shared layout, header/sidebar, modal, and notification helpers. |
| Requests page | Displays request history and opens forms for the four supported document types. | Database connection, request creation API, upload validation, shared modal, loading, and notification helpers. |
| Reports page | Displays the resident's report cases, filters status, opens details, and submits new reports. | Database connection, report create/get APIs, upload handling, shared modal, loading, and notification helpers. |
| Profile page | Displays personal, address, contact, identification, media, and recent request information. | Database connection, resident session, shared header/sidebar, image handling, and escaped output. |
| Account page | Provides tabs for personal information, contact, IDs, password, notifications, privacy, support, and deletion. | Account APIs, database connection, upload validation, shared forms, confirmation dialogs, loading, and session updates. |
| Notifications page | Intended to provide a dedicated notification inbox. The current implementation is a placeholder; request activity and delivery logs currently provide the available notification feedback. | Shared header/sidebar and notification styling are present, but persistent inbox behavior is not complete. |
| Guest pages | Allow a visitor to submit a report without a resident account, using contact details and an expiry period. | Guest-specific validation, upload handling, database tables, and notification behavior. |
| Terms and privacy pages | Explain the rules for using the service and the treatment of personal information. | Shared legal-page CSS and lightweight page behavior; no authenticated database access is required. |

### Administrator pages

| Page | How it is used | Shared connection |
|---|---|---|
| Administrator landing page | Presents the administrative portal and directs authorized staff to sign in. | Administrative branding and navigation scripts; no sensitive data is shown before authentication. |
| Administrator login page | Authenticates an administrator account and creates an administrator session. | Administrator authentication handler, database connection, session protection, mailer/OTP support, and auth styles. |
| Administrator registration and OTP pages | Creates an unverified administrator account and confirms ownership of the email address. | Validation, database connection, session-based pending registration, OTP flow, and mailer. |
| Administrator Dashboard page | Shows resident totals, verification workload, request totals, report totals, charts, recent activity, and scheduled announcements. | Administrator protection, database queries, shared layout, charts, header/sidebar, notifications, and loading overlay. |
| User Verification page | Lists pending and verified residents, opens resident details, and approves or rejects accounts. | Administrator protection, verification actions, resident/user joins, mailer, SMS logging, and administrative logs. |
| Requests page | Lists document requests, filters them, opens details, and changes allowed statuses. | Administrator protection, database connection, request/detail joins, status action handler, mailer, SMS logging, and shared tables/modals. |
| Reports page | Lists all report cases, opens details, and updates report status. | Administrator protection, database connection, report API behavior, status notifications, and shared modal/table helpers. |
| Announcements page | Creates and manages public notices, attachments, audiences, priority, scheduling, pinning, and archiving. | Administrator protection, database connection, upload validation, mailer/SMS notification layer, shared modal, and table behavior. |
| Administrator Profile page | Shows administrator identity, managed-resident totals, processed work, handled cases, and recent actions. | Administrator session, database connection, logs, shared header/sidebar, and profile scripts. |
| Account Settings page | Updates administrator data, barangay identity, logo, security options, and two-factor preference. | Settings update handler, database connection, upload validation, administrative logs, shared forms, and confirmation/loading behavior. |
| Notifications page | Reserved for an administrator notification center. It is currently a placeholder while activity and delivery logs provide operational visibility. | Shared administrative layout and notification styles. |
| Logs page | Intended to present administrative and notification history. Its availability depends on the deployed installation containing the corresponding logs view. | Database log tables, administrator protection, and shared administrative layout. |

### Shared file responsibilities

The shared application layer has four practical groups:

- **Database and environment:** the environment loader reads database, mail, SMS, SSL, schema-initialization, and debug settings. The database bootstrap creates one reusable PDO connection, enables exceptions and native prepared statements, initializes or migrates required tables when configured, and provides resident-ID generation.
- **Authentication and protection:** resident and administrator session checks control access. Registration and account helpers validate CSRF tokens, normalize phone numbers, validate fields, and return consistent JSON responses.
- **Shared interface:** common color tokens, layout styles, authentication styles, header/sidebar scripts, logo behavior, navigation toggles, notification helpers, and loading overlays keep the two sides consistent and responsive.
- **API and service layer:** APIs receive form or AJAX requests, validate input, check the current session, write to the database, handle files, and return success or error responses. The mailer uses PHPMailer/SMTP when configured and can use SMS delivery through the configured provider. Delivery results are recorded for later review.

The authoritative live system uses the shared PDO database layer. An older request demo uses a separate MySQLi connection and separate database schema; it must not be confused with the primary system during deployment or maintenance.

## 4. Actual Website Workflow

The following diagrams show how pages, APIs, database records, administrators, files, and notifications work together.

### 4.1 Resident registration and approval

```text
[Landing page]
	|
	v
[Registration page]
	|
	| request verification code
	v
[Registration OTP API] --> [Mailer] --> [Resident email]
	|
	| verify code and submit complete form
	v
[Registration API]
	|
	+--> validate fields, CSRF, images, and password
	+--> hash password and OTP
	+--> save user + resident profile in a transaction
	+--> store profile, cover, and ID file references
	v
[User record: awaiting approval]
	|
	v
[Administrator Login] --> [User Verification page]
				      |
		   +----------------+----------------+
		   |                                 |
	      [Approve]                          [Reject]
		   |                                 |
		   +--> update verification status -+
				      |
			    email/SMS + admin log
				      |
				      v
			    [Resident can sign in]
```

The browser performs convenient step validation, but the server repeats all important validation. The account is not trusted merely because the OTP is correct; administrator approval is a separate control.

### 4.2 Resident document request, supporting document, and status update

```text
[Resident Login]
	|
	v
[Dashboard page] ----> [Requests page]
				  |
				  | choose document type
				  v
			  [Request form modal]
				  |
				  | details + optional supporting document
				  v
			  [Request creation API]
				  |
				  +--> validate session, type, fields, and file
				  +--> create reference number
				  +--> insert master request record
				  +--> insert type-specific detail record
				  +--> commit transaction
				  +--> send submission notification/log result
				  v
			  [Pending request]
				  |
				  v
			  [Administrator Requests page]
				  |
		   inspect --> change allowed status
				  |
				  +--> update request status and timestamp
				  +--> record received time when applicable
				  +--> send email/SMS status notification
				  v
			  [Updated request]
				  |
				  v
		     [Resident Dashboard + Requests page]
```

Supported document details are stored separately because each document has different fields. The master request contains the common identity, type, reference number, supporting-file reference, status, and timestamps. The detail record contains the form-specific information.

Typical status progression is:

```text
Pending --> Processing --> Approved --> Ready for Pickup --> Received
    \-----------> Rejected
```

The exact transition selected by staff depends on the barangay process. The status is visible to the resident after the next page data refresh, and notification attempts are recorded.

### 4.3 Resident report and administrator response

```text
[Reports page]
	|
	| report type + title + description + location + optional image
	v
[Report creation API]
	|
	+--> validate resident session and report fields
	+--> validate and store permitted attachment
	+--> generate case number
	+--> insert report with Pending status
	+--> notify staff and record delivery attempt
	v
[Resident report list]
	|
	v
[Administrator Reports page]
	|
	+--> inspect case details
	+--> update status: Pending, Ongoing, Resolved, or Dismissed
	+--> notify resident and update timestamp
	v
[Resident Reports page + case details]
```

The report-detail API checks both the report identifier and the signed-in resident identifier. This prevents a resident from using a guessed identifier to retrieve somebody else's case.

### 4.4 Announcement publishing and reading

```text
[Administrator Announcements page]
	|
	+--> title, body, priority, schedule, audience, attachment
	+--> validate and save announcement
	+--> notify applicable residents
	v
[Active announcement]
	|
	v
[Resident Announcement page]
	|
	+--> search, sort, and open details
	+--> mark announcement as read
	v
[Announcement read record]
```

A unique resident/announcement pair makes repeated read actions harmless. Administrators can later pin or archive notices without deleting their history immediately.

### 4.5 Account maintenance and logout

```text
[Profile or Account page]
	|
	+--> update personal/contact data ------> [User/resident records]
	+--> update password -------------------> [Password hash]
	+--> replace ID or profile media -------> [Validated file + path]
	+--> export data -----------------------> [Personal JSON export]
	+--> delete account --------------------> [Transactional deletion]
	|
	v
[Shared session and loading/notification helpers]
	|
	v
[Logout] --> clear session and session cookie --> [Public landing page]
```

## 5. Technology Stack

| Layer | Technology and responsibility |
|---|---|
| Presentation | HTML5, CSS3, responsive layouts, modals, tables, forms, and shared design tokens. |
| Client behavior | Vanilla JavaScript, Fetch/AJAX, form validation, previews, filtering, charts, navigation, and loading states. |
| Server application | PHP with session-based authentication, server-side validation, file processing, and page/API responses. |
| Database | MySQL/MariaDB using InnoDB, foreign keys, indexes, unique constraints, enums, JSON fields, and transactions. |
| Database access | PDO for the primary system with exceptions and native prepared statements. A legacy demo uses MySQLi separately. |
| Email | PHPMailer with SMTP configuration; the dependency is managed by Composer. |
| SMS | Configured SMS provider integration using cURL, with delivery results logged. |
| Dependency management | Composer and its autoloader. |
| Web server | Apache with rewrite and access-control rules, commonly supplied through XAMPP locally and Z.com hosting in deployment. |
| Media | Server-side validated profile, cover, ID, report, announcement, and supporting-document uploads. |

## 6. Deployment on Z.com

The production deployment uses a Z.com hosting environment capable of running PHP and MySQL/MariaDB.

### Deployment procedure

1. Create or confirm the hosting space, domain, SSL certificate, PHP version, and database service in Z.com.
2. Create a production database and a restricted database user. Do not use local development credentials.
3. Upload the application source through the Z.com file manager, SFTP, or the hosting deployment method. Keep the public web root pointed at the application root used by the hosting account.
4. Install Composer dependencies on the server, or upload the tested dependency directory produced by Composer. The PHP version must satisfy the project dependencies.
5. Import the database schema through the Z.com database tool or command line. Schema initialization should normally remain disabled after the first setup.
6. Configure production environment variables for database access, mail, SMS, SSL, application debug mode, and upload limits. Secrets belong in the hosting environment, never in public source files.
7. Set the application debug setting to false and confirm that environment/configuration files, SQL files, dependency internals, private uploads, and test utilities cannot be downloaded.
8. Enable HTTPS and redirect plain HTTP to HTTPS. Confirm that secure cookies, rewrite rules, and clean page names work under the production domain.
9. Set writable permissions only on required upload and runtime locations. Do not make the whole application writable by the web process.
10. Test registration, OTP delivery, administrator approval, document submission, report submission, status updates, announcements, account updates, and logout using non-production test accounts.
11. Configure scheduled backups for the database and uploaded files. Test restoration, not only backup creation.

### Deployment checks

```text
HTTPS active
  +--> database credentials work
  +--> email and SMS credentials work
  +--> public pages render
  +--> resident session is isolated
  +--> administrator session is protected
  +--> uploads cannot execute server code
  +--> private IDs are access-controlled
  +--> backups and restoration are verified
```

The production host should expose only the public application experience. Development demonstrations, old prototypes, temporary test tools, credentials, database dumps, and unused alternate systems should be removed, isolated, or denied by the web server.

## 7. Security

### Controls implemented

- Passwords are stored with `password_hash()` and checked with `password_verify()`.
- Database values are normally written and read through prepared statements.
- Resident and administrator pages check sessions and roles server-side.
- Registration requests use CSRF protection and require POST requests.
- Registration and password-reset OTPs are generated securely, stored as hashes/HMAC values, expire, and are consumed after use.
- User output is generally escaped before rendering, and report modal values are escaped before client-side insertion.
- Uploads use size, MIME, image, and generated-name checks in the newer workflows.
- Foreign keys preserve relationships and cascade or null related records according to the data model.
- Transactions protect multi-table registration and document-request writes.
- Unique constraints prevent duplicate resident IDs, request references, and announcement-read rows.
- Administrator actions and notification outcomes are logged.
- Web-server rules disable directory listing and deny access to sensitive configuration and source areas.

### Production security requirements and remaining risks

Security is a continuing operational responsibility. Before production use, the following must be verified or strengthened:

- use strong production database credentials and keep them outside the source tree;
- keep debug output disabled and avoid returning raw exception messages to users;
- regenerate the session identifier after successful login and use secure, HTTP-only, same-site cookies;
- add consistent CSRF protection to every state-changing administrator and resident action;
- add login, OTP, reset-code, upload, and notification rate limits;
- validate file content on the server, block script execution in upload storage, and protect private IDs from direct public access;
- authorize every AJAX action against the current user or administrator scope;
- avoid storing HTML-escaped values in the database; escape at output instead;
- align request ownership with stable user identifiers rather than email alone;
- finish persistent notification preferences, deactivation, support forms, maintenance, and backup controls before promising them as complete features; and
- remove plaintext credentials, legacy demos, temporary test tools, unused prototypes, and unneeded production endpoints.

## 8. Database Design

The main database is relational. The `users` table is the identity parent for residents and administrators. A resident's detailed information is stored in the related `residents` table. This separation keeps authentication data distinct from demographic and household information.

### Main entities

| Entity | Purpose |
|---|---|
| Users | Login identity, role, contact information, password hash, verification state, profile media, and two-factor preference. |
| Residents | Resident ID, personal details, address, household details, contact data, and identification-file references. |
| Requests | Common document request information, public reference number, resident snapshot, supporting file, status, and timestamps. |
| Document detail tables | Extra fields for clearance, residency, indigency, and business-clearance requests. |
| Reports | Resident-linked cases with type, description, location, attachments, case number, and status. |
| Guest reports | Expiring reports submitted without a resident account. |
| Announcements | Public notices with priority, scheduling, audience, attachments, pinning, and archive state. |
| Announcement reads | Records which resident has read which announcement. |
| Barangay settings | Singleton record for barangay name, address, contacts, logo, and description. |
| Officials | Optional officials catalogue for future or configured public presentation. |
| OTP tables | Separate registration and password-reset codes with expiration and consumption state. |
| Admin logs | Administrator action history and metadata. |
| Email/SMS logs | Notification recipient, event, delivery status, and error information. |
| Remember tokens | Revocable, expiring login-token records where the feature is enabled. |

### Relationships

```text
Users 1 -------- 1 Residents
Users 1 -------- many Requests
Users 1 -------- many Reports
Users 1 -------- many Admin logs
Users 1 -------- many Announcements
Users many ----- many Announcements through Announcement reads
Requests 1 ----- 1 type-specific document detail
Users 1 -------- many OTP and notification records
```

InnoDB transactions are important because one user registration creates both an account and a resident profile, while one document request creates a master request and a type-specific detail row. If either part fails, the transaction should roll back so incomplete records are not presented as valid submissions.

The primary system uses a shared PDO connection that is reused during each request. It can initialize the schema when explicitly enabled and can apply compatibility migrations for older installations. The separate legacy request demo uses another database connection; production documentation and maintenance should treat the primary schema as authoritative.

## 9. Current Scope and Limitations

The primary resident and administrator workflows are implemented, but the repository also contains prototypes and compatibility code from earlier versions. The following areas need special operational awareness:

- the standalone notification pages are placeholders;
- notification preferences currently use session storage rather than a dedicated preference table;
- deactivation does not yet persist a deactivated account state;
- support, feedback, and issue-report forms need durable storage or a confirmed external service;
- some administrator API files are placeholders because the active status handlers are inside their page implementations;
- legacy request, login, profile, and role-access implementations use different patterns and may use a separate database;
- test SMS and temporary submission utilities must never be exposed in production.

These limitations do not remove the value of the working core, but they should be included in acceptance testing and future development planning.

## 10. Conclusion: The True Value of SafeBrgy

The true value of SafeBrgy is not simply that it replaces paper forms with web forms. Its value is that it creates a trusted chain from a resident's identity to a request or report, from that submission to accountable administrative action, and from that action to a visible status and notification.

For residents, this means less uncertainty, fewer unnecessary visits, and a clearer view of their transactions. For administrators, it means organized queues, reusable resident information, searchable records, controlled status updates, and an audit trail. For the barangay, it creates a foundation for faster service, better accountability, and decisions based on consistent records.

When deployed with strong credentials, protected uploads, complete authorization, reliable backups, and finished operational controls, SafeBrgy becomes more than a website. It becomes a shared service record: a practical bridge between the community and the people responsible for serving it.
