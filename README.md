# SafeBrgy - Official V1.4

## What's New in V1.4

### Enhanced Admin Request Management System
- **Statistics Dashboard**: Real-time statistics displaying request counts by status (Total, Pending, Processing, Approved, Ready to Receive, Received)
- **Advanced Search & Filter**:
  - Search requests by request number (4-digit auto-generated) or resident name
  - Filter by status (Pending, Processing, Approved, Ready to Receive, Received, Rejected)
  - Sort by date (Newest or Oldest)

- **Request Table** with comprehensive information:
  - Request # (4-digit auto-generated identifier)
  - Resident Name with email display
  - Request Type (Barangay Clearance, Barangay Residency, etc.)
  - Submitted Date (formatted as MMM DD, YYYY)
  - Status with color-coded badges
  - Date Received (when marked as Received)
  - View Action button

- **Request Details Modal** with full information:
  - Resident information (name, phone, email)
  - Request details (type, purpose, location)
  - Submission and received dates
  - Current status display with color-coded badge
  - Status dropdown to change request status
  - Update Status button with real-time feedback

- **Request Status Management**:
  - Pending: Initial request status
  - Processing: Request is being processed
  - Approved: Request has been approved
  - Ready to Receive: Documents are ready for pickup
  - Received: Resident has received the document
  - Rejected: Request was rejected
  - Auto-populated date_received timestamp when status changes to "Received"

- **Database Enhancement**:
  - Updated requests table with new status values
  - Added date_received TIMESTAMP column for tracking document delivery

- **Backend Features**:
  - AJAX-based status updates without page reload
  - Server-side validation for status changes
  - Prepared statements for SQL injection prevention
  - Comprehensive error handling and feedback

- **User Interface**:
  - Responsive Bootstrap 5 design
  - Color-coded status badges for quick visual identification
  - Stat cards with status-specific color themes
  - Modal dialogs for detailed request management
  - Hover effects and smooth transitions
  - Consistent with announcement management design patterns

---

### Enhanced Admin Announcement Management System
- **Statistics Dashboard**: Real-time statistics displaying total, active, scheduled, and expired announcements
- **Advanced Search & Filter**:
  - Search announcements by title or content
  - Filter by status (active, scheduled, expired, draft)
  - Sort by newest or oldest
  - View and manage archived announcements separately

- **Create Announcement Modal** with comprehensive features:
  - Title and description inputs with rich text support
  - File attachments for images (JPG, PNG, GIF) or PDF documents (optional)
  - Priority selector (normal, important, urgent)
  - Target audience filtering:
    - All residents
    - Age-based targeting (18-25, 26-40, 41-60, 60+)
    - Education-based targeting (elementary, secondary, tertiary)
  - Schedule option for future announcement publishing

- **Announcement Management Table** with enhanced functionality:
  - Display title with pinned status indicator
  - Message preview truncation
  - Target audience badges
  - Date posted with timestamp
  - Priority level badges with color coding
  - Status badges with visual indicators
  - Action buttons for managing announcements:
    - View: Display full announcement details
    - Edit: Modify announcement content and settings
    - Pin: Pin important announcements to top
    - Archive: Move announcements to archive
    - Delete: Permanently remove announcements with confirmation

- **Announcement Status Management**:
  - Active: Currently visible to residents
  - Scheduled: Will be published at specified date/time
  - Expired: Announcements that have passed their relevance
  - Draft: Unpublished announcements
  - Pinned: Important announcements displayed prominently

- **File Management**:
  - Upload directory created: `/uploads/announcements/`
  - Automatic file handling with timestamp-based naming
  - Support for multiple file formats (images and PDFs)

- **User Interface Improvements**:
  - Responsive Bootstrap 5 integration
  - Stat cards with hover effects
  - Improved table styling and layout
  - Modal dialogs for all operations
  - Confirmation dialogs for destructive actions
  - Real-time feedback and notifications

- **Backend Enhancements**:
  - AJAX-based operations for seamless user experience
  - Server-side validation and error handling
  - Database query optimization for filtering and searching
  - Prepared statements for SQL injection prevention
  - JSON attachment storage for flexible media management

---

# SafeBrgy - Official V1.3

## What's New in V1.3

### Resident Registration, Login, and Admin Approval
- **Resident Account Registration**: Public users can now create resident accounts through the system.
- **Resident Login Flow**: Residents can log in using their credentials to access the public portal.
- **Admin Approval Workflow**: New resident accounts require review and approval by an administrator before activation.
- **Improved Security and Verification**: Pending accounts remain inactive until an admin validates and approves the registration.

---

# SafeBrgy - Official V1.2

## What's New in V1.2

### Admin Account Registration & Login Flow with OTP
- **Enhanced Admin Authentication**: Comprehensive security overhaul for admin account management
- **Admin Registration Process** (`admin/register.php`):
  - Email-based account creation
  - Phone number verification (Philippine format support)
  - Secure password requirements:
    - Minimum 8 characters
    - At least one uppercase letter
    - At least one lowercase letter
    - At least one number
  - Terms of Use and Privacy Policy agreement
  - Real-time form validation

- **Admin Login with OTP Verification** (`admin/login.php`):
  - Secure credential authentication
  - One-Time Password (OTP) verification via email
  - Session management with authentication tokens

- **OTP Management**:
  - **OTP View** (`admin/otp-view.php`): Dedicated OTP entry interface
  - **OTP Verification** (`admin/verify_otp_process.php`): Server-side OTP validation and session establishment
  - **Resend OTP** (`admin/resend_otp.php`): Request new OTP if not received

- **Security Features**:
  - Protected admin routes with authentication checks (`admin/admin_protect.php`)
  - Secure session handling and token management
  - Password reset functionality (`admin/reset-password.php`)

- **Admin Backend Processing**:
  - Registration processing (`admin/admin_register_process.php`)
  - Authentication middleware for route protection (`admin/admin_auth.php`)

---

# SafeBrgy - Official V1.1

## What's New in V1.1

### Admin Account Registration Form
- **New Admin Registration Page** (`admin/register.php`): Dedicated registration form for creating new admin accounts
- **Form Fields**:
  - Email Address: Official email for admin account
  - Phone Number: Contact number for verification (supports Philippine format)
  - Password: Secure password with strength requirements
  - Confirm Password: Real-time validation to ensure password match
- **Create Account Button**: Submit registration form to create new admin account
- **Password Requirements**: 
  - Minimum 8 characters
  - At least one uppercase letter
  - At least one lowercase letter
  - At least one number
- **Real-time Validation**: JavaScript-powered validation for email, phone number, and password matching
- **Terms Agreement**: Checkbox to accept Terms of Use and Privacy Policy before account creation

---

# SafeBrgy - Official V1.0

## Overview

SafeBrgy is a comprehensive barangay (community) management system designed to facilitate safe and efficient community administration. This version marks the official release of V1.0, providing core functionalities for both administrators and public users.

The system is built to handle various aspects of barangay operations, including user management, announcements, reports, and service requests, ensuring a streamlined experience for both administrators and community members.

## Features

### Admin Panel
- **Dashboard**: Overview of system statistics, recent activities, and key metrics
- **User Verification**: Manage and verify user accounts for security
- **Announcement Management**: Create, edit, and publish community announcements
- **Reports Handling**: Process and manage community reports
- **Request Processing**: Handle service requests from residents
- **Profile and Account Settings**: Manage admin profiles and system settings

### Public Portal
- **User Registration and Login**: Secure account creation and authentication
- **Dashboard**: Personal dashboard for users to access services
- **Announcement Viewing**: Stay updated with community news and announcements
- **Report Submission**: Submit reports for community issues
- **Request Submission**: Submit service requests to the barangay
- **Profile Management**: Update personal information and settings

## Technology Stack
- **Backend**: PHP for server-side logic and database interactions
- **Frontend**: HTML5, CSS3, and JavaScript for responsive user interfaces
- **Database**: MySQL for data storage and management
- **Server Environment**: Apache web server (via XAMPP)
- **Additional Libraries**: Custom CSS and JS files for enhanced functionality

## Installation

1. **Prerequisites**: Ensure XAMPP (or similar Apache, MySQL, PHP stack) is installed and running on your system.

2. **Project Setup**:
   - Copy the project files to your web server's document root (e.g., `C:\xampp\htdocs\safebrgy`).
   - Ensure the directory structure matches the provided workspace layout.

3. **Database Configuration**:
   - Create a new MySQL database for the project.
   - Import the database schema file (if provided in the project).
   - Update database connection settings in relevant PHP files (typically in config or database connection files).

4. **File Permissions**: Ensure proper read/write permissions for upload directories (`uploads/` and subdirectories).

5. **Access the Application**:
   - Start Apache and MySQL services in XAMPP.
   - Navigate to `http://localhost/safebrgy` in your web browser.

## Usage

### For Administrators
- Access the admin panel via `admin/login.php`.
- Log in with admin credentials.
- Use the sidebar navigation to access different modules (dashboard, announcements, reports, etc.).

### For Public Users
- Register or log in via `public/login.php` or the main `index.php`.
- Access personal dashboard and submit reports or requests.
- View announcements and manage profile settings.

### File Structure
- `admin/`: Admin-specific PHP pages
- `public/`: Public-facing PHP pages
- `assets/`: CSS, JS, and image files
- `uploads/`: User-uploaded files (images, documents, IDs)
- `notes/`: Documentation and notes

## Version History

- **V1.0** (Official Release): Initial stable release with core barangay management features implemented.

## Contributing

We welcome contributions to improve SafeBrgy. Please follow these guidelines:
1. Fork the repository.
2. Create a feature branch.
3. Make your changes and test thoroughly.
4. Submit a pull request with a clear description of changes.

## License

This project is licensed under the MIT License. See the LICENSE file for details.

## Support

For support or questions, please contact the development team or refer to the project documentation.

---

*SafeBrgy V1.0 - Empowering communities through technology.*