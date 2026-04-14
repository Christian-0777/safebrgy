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