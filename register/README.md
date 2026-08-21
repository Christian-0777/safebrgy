# Resident Registration — Multi-Step Form

A 7-step, mobile- and desktop-friendly registration wizard built with PHP,
vanilla JS, Bootstrap 5, and a civic/ledger-inspired visual design. Left
panel carries the branding and a step "ledger"; the right panel holds the
active step's fields.

## Requirements

- PHP 8.1+
- MySQL or MariaDB
- A web server (Apache/Nginx) or PHP's built-in server for local testing

## Setup

1. **Import the database**
   ```bash
  mysql -u root -p < ../sql/safebrgy_schema.sql
   ```

2. **Configure the connection**
  Registration uses the shared `../config/db.php`. Set `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
   to match your environment.

3. **Run it locally**
   ```bash
   php -S localhost:8000
   ```
   Then open `http://localhost:8000/`.

4. **Email delivery**
   `api/send_otp.php` uses PHP's built-in `mail()` function so the OTP flow
   works out of the box on a server with mail configured. For real
   deployments, swap that block for PHPMailer/SMTP, or an SMS gateway
   (Semaphore, Twilio, etc.) if you'd rather verify by text instead of email.

5. **Testing without a mail server**
   `includes/db.php` has a `DEV_MODE` constant (default `true`). While it's
   on, `send_otp.php` returns the generated code in its JSON response and
   `main.js` logs it to the browser console, so you can complete the wizard
   without real email delivery. **Set `DEV_MODE` to `false` before deploying
   to production.**

## How the wizard works

- **All 7 steps live in the DOM at once** (`index.php`); `assets/js/main.js`
  only shows/hides the relevant `<section class="step-pane">`. Because
  nothing is removed from the page, field values are preserved automatically
  when a user moves back and forth between steps — including uploaded/
  captured photos, which are kept in a JS object in memory.
- **Step 6 ("Create account")** validates the password fields and calls
  `api/send_otp.php`, which emails a 6-digit code and stores its hash
  (never the raw code) in `registration_otps` with a 5-minute expiry.
- **Step 7 (OTP)** submits everything — all fields plus the four photos —
  to `api/register.php` in one request. That endpoint re-validates every
  field server-side, re-checks the OTP, hashes the password, stores the
  photos under `/uploads`, and inserts the resident record with
  `status = 'pending'` inside a transaction.
- On success, a modal confirms the account was created and is pending
  review, matching the message shown in the spec.

## Folder structure

```
register/
├── index.php                 Main wizard page (all steps)
├── assets/
│   ├── css/style.css          Design tokens + layout + components
│   └── js/main.js              Step logic, validation, uploads, camera, OTP
├── includes/
│   ├── functions.php           Shared helpers + dropdown option lists
├── api/
│   ├── send_otp.php             Issues/resends the OTP
│   ├── verify_otp.php            Inline "is this code valid" check
│   └── register.php               Final validation + account creation
├── database/
│   └── schema.sql                 residents + registration_otps tables
└── uploads/                        id/ profile/ cover/ (created automatically)
```

The registration flow uses only `../sql/safebrgy_schema.sql`; the old standalone registration schema is not used.

## Notes / assumptions

- The spec's steps were numbered 1–4, then 6–8 (no "step 5" content was
  given). This build renumbers them sequentially as **Steps 1–7**:
  1 Personal Information · 2 Contact & Location · 3 Economic Profile ·
  4 Other Information · 5 Review · 6 Password · 7 Verification (OTP).
- Mobile numbers (personal and emergency contact) are restricted to the
  Philippine format and stored as `+63XXXXXXXXXX`.
- "Household head" is captured as the head's full name, per the spec's
  "text input" description.
- Uploaded/captured photos are validated server-side (real image check,
  JPG/PNG/WebP only, 5MB max) before being saved.
- A CSRF token is generated per session and required on every API call.
