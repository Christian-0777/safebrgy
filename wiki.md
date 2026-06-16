# SafeBrgy Wiki

A comprehensive Barangay management system designed to streamline document requests, announcements, reports, and resident account management.

## Table of Contents

1. [Project Overview](#project-overview)
2. [System Architecture](#system-architecture)
3. [Technology Stack](#technology-stack)
4. [Installation & Setup](#installation--setup)
5. [Directory Structure](#directory-structure)
6. [Database Schema](#database-schema)
7. [Authentication System](#authentication-system)
8. [Core Features](#core-features)
9. [API Endpoints](#api-endpoints)
10. [Frontend Pages](#frontend-pages)
11. [Admin Dashboard](#admin-dashboard)
12. [User Roles & Permissions](#user-roles--permissions)
13. [Configuration](#configuration)
14. [Development Guidelines](#development-guidelines)

---

## Project Overview

**SafeBrgy** is a comprehensive web application for managing Barangay (Filipino local government unit) operations. It provides a platform for:

- **Residents** to submit document requests, view announcements, and manage their profiles
- **Administrators** to verify users, process requests, post announcements, view reports, and monitor system activity
- **Secure Communication** through email notifications and status updates
- **Document Request Tracking** from submission to delivery

### Key Objectives

- Digitalize Barangay document request process
- Provide transparent status tracking for resident requests
- Centralize announcements and community information
- Enable residents to submit reports and concerns
- Streamline admin workflows with intuitive dashboards

---

## System Architecture

### High-Level Overview

```
┌─────────────────────────────────────────────────────────┐
│                    Web Browser                          │
├─────────────────────────────────────────────────────────┤
│           Frontend (HTML/CSS/JavaScript)                │
├─────────────────────────────────────────────────────────┤
│              PHP Application Layer                      │
│  ┌──────────────┬──────────────┬──────────────┐        │
│  │   Admin UI   │  Public UI   │   API Routes │        │
│  └──────────────┴──────────────┴──────────────┘        │
├─────────────────────────────────────────────────────────┤
│              Session Management                         │
│         (OTP, Authentication, Authorization)           │
├─────────────────────────────────────────────────────────┤
│         Database Layer (MySQL via PDO)                  │
├─────────────────────────────────────────────────────────┤
│      External Services (Email, SMS, Mailer)            │
└─────────────────────────────────────────────────────────┘
```

### Request Flow

1. User submits form through web interface
2. Frontend validates input using JavaScript
3. Form data sent via AJAX to PHP API endpoint
4. PHP validates and processes server-side
5. Database updated with new data
6. Email notification sent (if applicable)
7. Response returned to frontend
8. UI updated to reflect changes

---

## Technology Stack

### Backend
- **PHP 8.0+** - Server-side scripting language
- **MySQL/MariaDB** - Relational database
- **PDO** - Database abstraction layer with prepared statements for SQL injection prevention
- **Composer** - PHP dependency manager

### Frontend
- **HTML5** - Markup
- **CSS3** - Styling and responsive design
- **Bootstrap 5** - UI framework
- **JavaScript (ES6+)** - Client-side interactivity
- **Font Awesome 6** - Icon library
- **jQuery** - DOM manipulation (if included)

### External Libraries
- **PHPMailer 7.0** - Email sending library
- **SendGrid 8.1** - Email delivery service integration
- **Twilio 8.11** - SMS and communication services

### Development Tools
- **XAMPP/LAMP** - Local development server
- **Git** - Version control
- **Composer** - Dependency management

---

## Installation & Setup

### Prerequisites

- PHP 8.0 or higher
- MySQL 5.7 or MariaDB 10.3+
- Web server (Apache with mod_rewrite enabled)
- Composer
- Git

### Step 1: Clone or Download Project

```bash
git clone <repository-url> safebrgy
cd safebrgy
```

### Step 2: Install Dependencies

```bash
composer install
```

### Step 3: Configure Environment Variables

Copy the environment template and configure:

```bash
cp config/.env.example config/.env
```

Edit `config/env.php` with your database credentials:

```php
define('DB_HOST', '');
define('DB_PORT', '');
define('DB_NAME', '');
define('DB_USER', '');
define('DB_PASS', '');
define('DB_CHARSET', '');
define('DB_INIT_SCHEMA', true);
```

### Step 4: Database Setup

The database schema will auto-initialize on first connection if `DB_INIT_SCHEMA` is enabled. Alternatively, manually import:

```bash
mysql -u root -p safebrgy < sql/safebrgy_schema.sql
```

### Step 5: Configure Email Services

Update `config/mailer.php` with your email credentials:

```php
// PHPMailer configuration
$mail->Host = 'smtp.gmail.com';
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'your-app-password';
```

Or for SendGrid:

```php
$sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
```

### Step 6: Start Development Server

For XAMPP:
- Place project in `htdocs/safebrgy`
- Start Apache and MySQL
- Access at `http://localhost/safebrgy`

Or using PHP built-in server:

```bash
php -S localhost:8000
```

---

## Directory Structure

### Root Level Files
```
├── index.php                          # Main public landing page
├── admin_index.php                    # Admin landing page
├── register.php                       # Public registration page
├── login.php                          # Public login (redirects to public/login.php)
├── README.md                          # Project documentation
├── ADMIN_REQUESTS_DOCUMENTATION.md    # Admin requests feature docs
├── wiki.md                            # This file
├── composer.json                      # PHP dependencies
└── composer.lock                      # Locked dependency versions
```

### /admin Directory - Admin Dashboard
```
admin/
├── admin_auth.php              # Admin login authentication
├── admin_protect.php           # Session protection middleware
├── admin_register_process.php  # Admin registration handler
├── login.php                   # Admin login form
├── logout.php                  # Admin logout handler
├── register.php                # Admin registration form
├── otp-view.php               # OTP entry form for admin
├── verify_otp_process.php     # OTP verification handler
├── resend_otp.php             # OTP resend endpoint
├── reset-password.php         # Password reset form
└── main-pages/
    ├── dashboard.php          # Admin dashboard
    ├── announcement.php       # Announcement management
    ├── reports.php            # View submitted reports
    ├── requests.php           # View and update requests
    ├── user_verification.php  # Verify resident accounts
    ├── account_settings.php   # Admin account settings
    ├── profile.php            # Admin profile view
    ├── notifications.php      # System notifications
    └── verify_user.php        # User verification handler
```

### /public Directory - Resident Interface
```
public/
├── login.php              # Resident login form
├── logout.php             # Resident logout handler
├── register.php           # Resident registration form
├── otp-view.php          # OTP verification form
├── verify_otp_process.php # OTP verification handler
├── resend_otp.php        # Resend OTP endpoint
├── reset-password.php    # Password reset form
└── public-pages/
    ├── dashboard.php      # Resident dashboard
    ├── announcement.php   # View announcements
    ├── requests.php       # Submit and track requests
    ├── reports.php        # Submit reports
    ├── notifications.php  # View notifications
    ├── profile.php        # View profile
    ├── account.php        # Account management
    ├── update_account.php # Account update handler
    └── view_user.php      # View other users (if allowed)
```

### /api Directory - Backend API
```
api/
├── announcement-noted.php  # Mark announcements as read
├── admin/
│   ├── get_request.php            # Get request details
│   └── update_request_status.php  # Update request status
├── reports/
│   ├── create.php         # Submit new report
│   └── get.php            # Retrieve reports
└── requests/
    └── create.php         # Submit new document request
```

### /assets Directory - Static Files
```
assets/
├── style.css              # Global styles
├── admin_landing.css      # Admin landing page styles
├── admin_landing.js       # Admin landing page scripts
├── css/
│   ├── admin/             # Admin page-specific styles
│   │   ├── dashboard.css
│   │   ├── announcement.css
│   │   ├── requests.css
│   │   ├── reports.css
│   │   ├── account_settings.css
│   │   ├── login.css
│   │   ├── register.css
│   │   ├── otp-view.css
│   │   ├── profile.css
│   │   ├── reset-password.css
│   │   └── user_verification.css
│   ├── public/            # Resident page-specific styles
│   │   └── [similar structure]
│   ├── shared/            # Shared styles
│   │   ├── colors.css     # Color variables
│   │   └── [other shared]
│   └── modals/            # Modal component styles
├── js/
│   ├── admin/             # Admin page scripts
│   │   ├── dashboard.js
│   │   ├── requests.js
│   │   └── [others]
│   ├── public/            # Resident page scripts
│   │   └── [similar structure]
│   └── shared/            # Shared utility scripts
└── img/                   # Images and graphics
```

### /config Directory - Configuration
```
config/
├── db.php      # Database connection and initialization
├── env.php     # Environment variables
└── mailer.php  # Email configuration
```

### /sql Directory - Database
```
sql/
├── safebrgy_schema.sql    # Database schema with all tables
└── migrations/            # Future migration files
    └── 001_update_requests_table.sql
```

### /uploads Directory - User Uploads
```
uploads/
├── profile_images/        # User profile pictures
├── valid_ids/             # Valid ID documents
└── announcements/         # Announcement media
```

### /vendor Directory - Dependencies
```
vendor/                    # Composer packages
├── autoload.php
├── phpmailer/
├── sendgrid/
├── twilio/
└── [others]
```

---

## Database Schema

### Users Table
```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(255) UNIQUE,
  email VARCHAR(255) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin', 'resident') DEFAULT 'resident',
  first_name VARCHAR(255),
  last_name VARCHAR(255),
  middle_name VARCHAR(255),
  profile_image_path VARCHAR(255),
  is_verified INT DEFAULT 0,
  verification_token VARCHAR(255),
  phone VARCHAR(255),
  address VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Requests Table
```sql
CREATE TABLE requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  request_number VARCHAR(10) UNIQUE,
  request_type ENUM('Barangay Clearance', 'Barangay Residency', 
                     'Barangay Indigency', 'Barangay Business Clearance'),
  status ENUM('Pending', 'Approved', 'Processing', 
              'Ready to Receive', 'Received', 'Rejected') DEFAULT 'Pending',
  purpose TEXT,
  document_data JSON,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  date_received DATETIME,
  FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Announcements Table
```sql
CREATE TABLE announcements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  image_path VARCHAR(255),
  posted_by INT NOT NULL,
  posted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (posted_by) REFERENCES users(id)
);
```

### Reports Table
```sql
CREATE TABLE reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  status ENUM('Submitted', 'Under Review', 'Resolved') DEFAULT 'Submitted',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Additional Tables
- **admin_logs** - Track admin actions
- **otp_codes** - Store OTP for verification
- **announcement_notes** - Track announcement reads
- **notifications** - System notifications

---

## Authentication System

### Two-Tier Authentication

#### Step 1: Email & Password Verification
1. User enters email and password
2. Password compared with stored hash using `password_verify()`
3. If match fails, redirect with error

#### Step 2: OTP Verification
1. On successful login, generate 6-digit OTP
2. OTP sent via email (PHPMailer or SendGrid)
3. User enters OTP on verification page
4. OTP validated against stored value with timestamp
5. Session established only after both steps pass

### Session Management

```php
// Session data stored after successful authentication
$_SESSION['user'] = [
    'id' => $user_id,
    'email' => $email,
    'username' => $username,
    'role' => 'admin' || 'resident'
];
```

### Protection Mechanisms

- **Prepared Statements** - Prevent SQL injection
- **Password Hashing** - Using PHP's `password_hash()` with bcrypt
- **Session Timeout** - Auto-logout after inactivity
- **CSRF Protection** - Token validation on forms
- **Role-Based Access** - Verify user role on protected pages

### Login Flow

**Public/Resident Login:**
```
login.php → public/login.php → verify_otp_process.php → 
public/public-pages/dashboard.php
```

**Admin Login:**
```
admin/login.php → admin_auth.php → admin/otp-view.php → 
admin/verify_otp_process.php → admin/main-pages/dashboard.php
```

---

## Core Features

### 1. Document Request System

**Available Request Types:**
- **Barangay Clearance** - Certificate of good moral character
- **Barangay Residency** - Proof of residency
- **Barangay Indigency** - Certificate for indigent assistance
- **Barangay Business Clearance** - Business operation permit

**Request Status Flow:**
```
Pending → Approved → Processing → Ready to Receive → Received
                  ↘ (if rejected) → Rejected
```

**Key Features:**
- Dynamic multi-step form modal
- Type-specific field validation
- Automatic request number generation (R-0001, R-0002, etc.)
- File upload support
- Email notifications on status changes
- Resident tracking and history

### 2. Announcement System

**Admin Features:**
- Post new announcements with title and rich content
- Upload announcement media/images
- View all posted announcements
- Delete or edit announcements

**Resident Features:**
- View all active announcements
- Mark announcements as read
- Sort by date posted
- Search announcements

### 3. Reports System

**Resident Features:**
- Submit reports/concerns to barangay
- Include title, description, and attachments
- Track report status (Submitted, Under Review, Resolved)

**Admin Features:**
- View all submitted reports
- Filter by status
- Update report status
- View resident details and attachments

### 4. User Verification System

**Admin Features:**
- View pending resident verification requests
- Approve or reject user accounts
- View user details and uploaded documents
- Track verification statistics

**Verification Statistics:**
- Pending Verification (Yellow)
- Verified Accounts (Green)
- Rejected Accounts (Red)

### 5. User Profile Management

**Resident Capabilities:**
- View and edit profile information
- Upload/change profile picture
- View personal documents
- Manage notification settings

**Admin Capabilities:**
- View resident profiles
- Verify uploaded documents
- Check account status
- View submission history

### 6. Notifications System

**Types of Notifications:**
- Request status updates
- OTP codes
- Account verification results
- Announcement updates
- Report status changes

---

## API Endpoints

### Authentication Endpoints

#### POST `/admin/admin_auth.php`
Admin login authentication.

**Parameters:**
```
POST {
  email: string,
  password: string
}
```

**Response:**
```json
{
  "success": true/false,
  "message": "error/success message"
}
```

#### POST `/admin/verify_otp_process.php`
Verify OTP for admin login.

**Parameters:**
```
POST {
  otp: string (6 digits)
}
```

#### POST `/public/verify_otp_process.php`
Verify OTP for resident login.

### Request Management Endpoints

#### POST `/api/requests/create.php`
Submit new document request.

**Parameters:**
```json
{
  "request_type": "Barangay Clearance|Barangay Residency|Barangay Indigency|Barangay Business Clearance",
  "purpose": "string",
  "years_residency": "integer" (for Residency),
  "date_started_living": "date" (for Residency),
  "monthly_income": "float" (for Indigency),
  "household_members": "integer" (for Indigency),
  "business_name": "string" (for Business Clearance)
}
```

**Response:**
```json
{
  "success": true/false,
  "message": "error/success message",
  "request_id": "integer"
}
```

#### GET `/api/admin/get_request.php`
Get request details.

**Parameters:**
```
GET ?request_id=123
```

**Response:**
```json
{
  "id": 123,
  "request_number": "R-0001",
  "request_type": "Barangay Clearance",
  "status": "Pending",
  "user": { "id", "name", "email" },
  "created_at": "timestamp",
  "date_received": "timestamp"
}
```

#### POST `/api/admin/update_request_status.php`
Update request status.

**Parameters:**
```json
{
  "request_id": 123,
  "status": "Approved|Processing|Ready to Receive|Received|Rejected"
}
```

### Report Endpoints

#### POST `/api/reports/create.php`
Submit new report.

**Parameters:**
```json
{
  "title": "string",
  "description": "text",
  "attachments": "file" (optional)
}
```

#### GET `/api/reports/get.php`
Retrieve reports.

**Parameters:**
```
GET ?filter=all|submitted|resolved&sort=newest
```

### Announcement Endpoints

#### POST `/api/announcement-noted.php`
Mark announcement as read.

**Parameters:**
```json
{
  "announcement_id": 123
}
```

---

## Frontend Pages

### Public Pages (Resident Interface)

#### Login & Authentication
- **public/login.php** - Email/password login form
- **public/otp-view.php** - OTP verification form
- **public/register.php** - New resident registration
- **public/reset-password.php** - Password recovery

#### Main Features
- **public/public-pages/dashboard.php** - Home dashboard with overview
- **public/public-pages/requests.php** - View/submit document requests
- **public/public-pages/announcement.php** - Browse announcements
- **public/public-pages/reports.php** - Submit reports/concerns
- **public/public-pages/notifications.php** - Notification center
- **public/public-pages/profile.php** - View user profile
- **public/public-pages/account.php** - Account settings

#### Request Features
- Service cards for each request type
- Status overview dashboard (Pending, Processing, Ready to Receive, Received)
- Search by request reference number
- Real-time status tracking
- Email notifications

---

## Admin Dashboard

### Navigation Structure

**Main Dashboard** (`admin/main-pages/dashboard.php`)
- System statistics
- Recent activities
- Quick access to main sections

**User Management** (`admin/main-pages/user_verification.php`)
- Pending verification list
- Approved accounts
- Rejected accounts
- Verification metrics

**Request Management** (`admin/main-pages/requests.php`)
- All document requests
- Advanced search and filtering
- Status update modal
- Statistics cards

**Announcements** (`admin/main-pages/announcement.php`)
- Post new announcements
- Edit existing announcements
- Delete announcements
- View all announcements

**Reports** (`admin/main-pages/reports.php`)
- View all submitted reports
- Filter by status
- View report details
- Update report status

**Account Management** (`admin/main-pages/account_settings.php`)
- Change password
- Update profile information
- Security settings

---

## User Roles & Permissions

### Resident Role
**Permissions:**
- ✅ Register and login
- ✅ Submit document requests
- ✅ View request status
- ✅ View announcements
- ✅ Submit reports
- ✅ View notifications
- ✅ Edit profile
- ✅ View account settings
- ❌ Cannot access admin panel
- ❌ Cannot approve/reject requests
- ❌ Cannot verify users
- ❌ Cannot post announcements

### Admin Role
**Permissions:**
- ✅ Access admin dashboard
- ✅ Verify user accounts
- ✅ Approve/reject user registration
- ✅ Process document requests
- ✅ Update request status
- ✅ Post announcements
- ✅ View and respond to reports
- ✅ View system logs
- ✅ Manage admin accounts
- ✅ View statistics and analytics
- ✅ Access admin settings
- ❌ Cannot submit personal requests (uses admin dashboard instead)

### Guest (Unauthenticated)
**Permissions:**
- ✅ View login page
- ✅ View registration page
- ✅ Access password reset
- ✅ View public announcements (if allowed)
- ❌ Cannot submit requests
- ❌ Cannot access admin panel
- ❌ Cannot view resident dashboard

---

## Configuration

### Database Configuration (`config/env.php`)

```php
// Database connection settings
define('DB_HOST', 'localhost');        // MySQL host
define('DB_PORT', '3306');             // MySQL port
define('DB_NAME', 'safebrgy');         // Database name
define('DB_USER', 'root');             // MySQL user
define('DB_PASS', '');                 // MySQL password
define('DB_CHARSET', 'utf8mb4');       // Character set
define('DB_INIT_SCHEMA', true);        // Auto-initialize schema

// Application settings
define('APP_NAME', 'SafeBrgy');
define('APP_URL', 'http://localhost/safebrgy');
define('SESSION_TIMEOUT', 3600);       // 1 hour
define('OTP_EXPIRY', 300);             // 5 minutes
```

### Email Configuration (`config/mailer.php`)

**PHPMailer Setup:**
```php
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'your-app-password';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
$mail->setFrom('noreply@safebrgy.com', 'SafeBrgy');
```

**SendGrid Setup:**
```php
$sendgrid_api_key = getenv('SENDGRID_API_KEY');
$sendgrid = new \SendGrid($sendgrid_api_key);
```

### Email Templates

Common email templates:
- **OTP Verification** - 6-digit OTP code
- **Account Approved** - User verification confirmation
- **Account Rejected** - Rejection notification
- **Request Status Update** - Request status change notification
- **Request Ready** - Document ready for pickup notification
- **Report Acknowledgment** - Report submission confirmation

---

## Development Guidelines

### Code Standards

#### PHP Standards
- Use PSR-12 coding standards
- Use prepared statements with PDO
- Always sanitize user input
- Use type hints for function parameters
- Organize code into logical functions

#### JavaScript Standards
- Use ES6+ syntax
- Use const/let instead of var
- Add JSDoc comments for functions
- Avoid inline scripts; use external files
- Use data attributes for DOM manipulation

#### CSS Standards
- Use utility classes from Bootstrap 5
- Follow BEM naming convention for custom classes
- Use CSS variables for colors and spacing
- Mobile-first responsive design
- Group related styles together

### Security Best Practices

1. **SQL Injection Prevention**
   - Always use prepared statements with PDO
   - Never concatenate user input into SQL queries

2. **XSS Prevention**
   - Use `htmlspecialchars()` for output
   - Use `htmlentities()` for special characters
   - Validate input types

3. **CSRF Protection**
   - Generate and validate CSRF tokens
   - Use SameSite cookie attribute
   - Validate request origin

4. **Authentication**
   - Use `password_hash()` and `password_verify()`
   - Implement OTP for sensitive operations
   - Use secure session management

5. **File Uploads**
   - Validate file types and extensions
   - Store uploads outside webroot if possible
   - Generate random filenames
   - Check file size limits

### Common Tasks

#### Adding a New Feature Page

1. Create PHP file in appropriate directory
2. Include database and session files
3. Add authentication check (if needed)
4. Create HTML structure
5. Add corresponding CSS file
6. Add JavaScript for interactivity
7. Create API endpoint if needed
8. Add navigation link to menu
9. Test on both desktop and mobile

#### Creating a Database Migration

1. Create SQL file in `sql/migrations/`
2. Name: `00X_description.sql`
3. Include IF NOT EXISTS statements
4. Add version tracking to admin_logs
5. Document changes in README.md

#### Adding API Endpoints

1. Create PHP file in appropriate `api/` subdirectory
2. Check authentication and permissions
3. Validate input data
4. Use prepared statements
5. Return JSON response
6. Include error handling
7. Log important actions

### Testing Checklist

- [ ] Test on Chrome, Firefox, Safari, Edge
- [ ] Test on mobile devices (iOS, Android)
- [ ] Test form validation (client and server)
- [ ] Test error handling
- [ ] Test database transactions
- [ ] Test email notifications
- [ ] Test file uploads
- [ ] Test session timeout
- [ ] Test access control
- [ ] Test SQL injection attempts

### Debugging Tips

**Enable Error Logging:**
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

**Database Debugging:**
```php
// Check query with parameters
echo $stmt->queryString;
var_dump($stmt->getDebugInfo());
```

**Session Debugging:**
```php
var_dump($_SESSION);
```

**JavaScript Debugging:**
- Use browser DevTools Console
- Add console.log() statements
- Check Network tab for API responses

---

## Version History

### V2.2.1 (Current)
- Admin user verification page redesign with status cards
- Enhanced card-based UI design
- Responsive grid layout improvements

### V2.2
- Complete resident request page redesign
- Multi-step request modal system
- Request status tracking dashboard
- Email notifications for request updates
- Advanced search functionality

### V2.1
- Admin requests page complete redesign
- Search and filtering system
- Statistics cards
- Request status update modal

### V2.0
- Core platform launch
- User authentication with OTP
- Document request system
- Announcements feature
- Reports submission
- Admin dashboard

---

## Troubleshooting

### Common Issues

**Database Connection Failed**
- Check credentials in `config/env.php`
- Verify MySQL is running
- Check database exists
- Verify user permissions

**OTP Not Received**
- Check email configuration
- Verify SMTP credentials
- Check spam folder
- Review email logs

**Session Expires Too Quickly**
- Adjust SESSION_TIMEOUT in config
- Check server session settings
- Clear browser cookies
- Verify session storage

**File Upload Fails**
- Check file permissions on uploads directory
- Verify file size limits
- Check allowed file types
- Review PHP upload limits

---

## Support & Contribution

For issues, bugs, or feature requests:
1. Check existing documentation
2. Review code comments
3. Check browser console for errors
4. Review server logs
5. Submit detailed bug report with screenshots

---

## License & Credits

SafeBrgy Barangay Management System
Designed for local government administration

---

**Last Updated:** June 2026
**Version:** 2.2.1
**Status:** Active Development
