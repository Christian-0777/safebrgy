# Barangay Resident Document Request Portal

A simple PHP + MySQL + JS resident request page built for a school activity.
Residents can request four types of documents, each with its own modal form,
and see their submissions listed in a tracking table.

## Documents supported
1. Barangay Clearance
2. Barangay Residency
3. Barangay Indigency
4. Barangay Business Clearance

## Tech stack
- PHP (mysqli, procedural + small helper functions)
- MySQL / MariaDB
- Vanilla JavaScript (Fetch API, no frameworks)
- Plain CSS (no frameworks)

## Folder structure
```
resident-request-system/
├── config.php              # DB connection + mail settings
├── schema.sql               # Run this first to create the database & tables
├── index.php                 # Main page: services cards, modals, requests table
├── submit_request.php        # Handles AJAX form submission for all 4 documents
├── includes/
│   └── functions.php         # Reference no. generator, file upload, email sender
├── css/
│   └── style.css
├── js/
│   └── script.js             # Modal logic + AJAX submit
└── uploads/                  # Uploaded supporting documents/images land here
```

## Setup (XAMPP / WAMP / Laragon)
1. Copy the `resident-request-system` folder into your server's `htdocs` (XAMPP)
   or `www` (WAMP) directory.
2. Open phpMyAdmin (or the MySQL CLI) and run the contents of **schema.sql**.
   This creates the `barangay_resident_system` database and all 5 tables
   (`requests`, `barangay_clearance`, `barangay_residency`,
   `barangay_indigency`, `barangay_business_clearance`).
3. Open `config.php` and update `DB_HOST`, `DB_USER`, `DB_PASS` if your MySQL
   credentials differ from the defaults (`root` / empty password).
4. Make sure the `uploads/` folder is writable by the web server.
5. Visit `http://localhost/resident-request-system/` in your browser.

## Email notifications
`includes/functions.php` uses PHP's built-in `mail()` function. On a local
XAMPP/WAMP setup this normally will **not** actually deliver email unless you
configure an SMTP relay (e.g. Mercury Mail bundled with XAMPP, or a tool like
"Sendmail for Windows"/Papercut SMTP). The request will still be saved to the
database and the confirmation modal will still show, even if the email fails
silently — this keeps the demo usable without a real mail server.

For a production version, swap `sendNotificationEmail()` in
`includes/functions.php` for **PHPMailer** with real SMTP credentials
(e.g. Gmail SMTP, SendGrid, etc.).

## How each request flows
1. Resident clicks **Request Now** on a document card → a modal opens with
   the fields specific to that document.
2. Resident fills out the form and clicks **Submit Request**.
3. JS sends the form (via `FormData`/`fetch`) to `submit_request.php`.
4. The script:
   - Uploads the optional supporting file to `/uploads`.
   - Generates a unique reference number (`BRGY-YYYYMMDD-XXXXX`).
   - Inserts the shared info into the `requests` master table.
   - Inserts the document-specific fields into its own table
     (e.g. `barangay_clearance`, `barangay_residency`, etc.).
   - Sends the resident a "pending review" email.
   - Returns JSON with the reference number.
5. The page closes the request modal, shows a confirmation modal
   ("Your [Document] has pending to review by our officials. We will send
   an email for updates"), and adds the new row to the **My Requests** table
   without a page reload.

## Notes for grading / demo
- No login/authentication system is included — this activity focuses only
  on the request page itself, so resident name/email are captured directly
  in each form.
- Status values (`Pending`, `Approved`, `Rejected`, `Ready for Pickup`) are
  included in the schema so a future "admin panel" could update them; this
  demo only ever inserts requests as `Pending`.
