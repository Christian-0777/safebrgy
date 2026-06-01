# SafeBrgy - Official V1.7

## What's New in V1.7

### Enhanced Resident Dashboard with Activity Overview
- **Dynamic Welcome Section**:
  - Personalized greeting that differentiates between new users (first 7 days) and returning users
  - Real-time date and time display that updates every second
  - New user onboarding tip with setup guidance
  - Gradient blue header with welcome icon

- **Status Tracker Section** with four key metrics:
  - **Pending Requests**: Displays count of requests awaiting processing
  - **Approved Documents**: Shows count of approved/ready to receive requests
  - **Your Reports**: Total reports filed by the resident
  - **Recent Updates**: Count of recent activity from last 7 days
  - Color-coded tracker cards (yellow, green, cyan, purple) with hover effects
  - Quick access links to detailed pages for each metric
  - Large, readable metrics display with icons

- **Notification Summary Details**:
  - Lists latest updates grouped by request type and status
  - Color-coded status badges (warning, success, info, danger, dark)
  - Shows number of requests per type and status combination
  - Responsive grid layout for multiple updates
  - Helpful overview of recent activities at a glance

- **Services Section** with six available services:
  - Barangay Clearance (certificate icon)
  - Barangay Residency (home icon)
  - Barangay Indigency (hand-holding heart icon)
  - Barangay Business Clearance (briefcase icon)
  - Incident Report (exclamation triangle icon)
  - Lost Property (search icon)
  - Each service card includes title, description, and "Request Now" button
  - Service cards feature hover animations and gradient buttons
  - Direct navigation to request submission with service pre-selection
  - Fully responsive card layout

- **Announcements Section**:
  - Displays 5 most recent active announcements
  - Shows announcement title and publication date only
  - Timeline-style layout with colored dot indicators
  - "See All" link to view full announcements page
  - Empty state message when no announcements available
  - Clean list design with hover effects and smooth transitions

- **Design & User Experience**:
  - Modern gradient blue theme matching admin dashboard design
  - Consistent styling with admin announcement page layouts
  - Smooth hover animations and visual feedback on all interactive elements
  - Professional card-based layout for all sections
  - Box shadows and color-coded accents for visual hierarchy
  - Fully responsive design (desktop, tablet, mobile)
  - Breakpoints at 768px and 576px for optimal mobile experience

- **Database Integration**:
  - Fetches user statistics from requests and reports tables
  - Queries pending requests count grouped by user and status
  - Counts approved/ready to receive requests
  - Retrieves total reports filed by resident
  - Calculates recent updates from last 7 days
  - Selects 5 most recent active announcements sorted by pinned status
  - Groups updates by request type and status for summary display

- **Frontend Enhancements**:
  - JavaScript date/time formatter with real-time updates (every second)
  - Logout confirmation dialog with user feedback
  - Sidebar toggle functionality for mobile devices
  - Responsive window resize handler
  - Font Awesome icons for all visual indicators
  - Bootstrap 5 grid system for responsive layout

- **Files Modified**:
  - `public/public-pages/dashboard.php`: Complete redesign with new sections and database queries
  - `assets/css/public/dashboard.css`: Comprehensive styling for welcome, tracker, services, and announcement sections
  - `assets/js/public/dashboard.js`: JavaScript functionality for date/time, logout, and responsive behavior

---

# SafeBrgy - Official V1.6

## What's New in V1.6

### Enhanced Admin Requests Management System
- **Statistics Dashboard**: Real-time statistics displaying total requests, pending, processing, and received counts
- **Request Number Tracking**: Automatic 4-digit request number generation in format R-XXXX for each request
- **Advanced Search & Filter**:
  - Search requests by request number, resident name, email, or username
  - Filter results by status (Pending, Approved, Processing, Ready to Receive, Received, Rejected)
  - Sort by newest or oldest
  - Reset functionality to clear filters

- **Request Details Modal** with comprehensive information display:
  - Request number badge for easy identification
  - Resident information (name, email, phone)
  - Request type and purpose details
  - Location information
  - Submitted date with timestamp
  - Status tracking and update capability

- **Status Management System**:
  - Six status levels: Pending, Approved, Processing, Ready to Receive, Received, Rejected
  - Color-coded status badges for quick visual identification:
    - Yellow for Pending
    - Blue for Approved and Processing
    - Green for Ready to Receive and Received
    - Red for Rejected
  - Status update dropdown in modal with Update button
  - Real-time status updates via AJAX
  - Automatic date recording when status changed to "Received"

- **Requests Management Table** with enhanced functionality:
  - Request number display (R-0001 format)
  - Resident name with email in secondary text
  - Request type classification (badge)
  - Date submitted with formatted timestamp
  - Current status indicator with color coding
  - Date received (automatically set when marked as received)
  - View action button opening detailed modal

- **Statistics Cards** with color-coded design:
  - Total Requests card (blue)
  - Pending Requests card (yellow)
  - Processing Requests card (blue)
  - Received Requests card (green)
  - Hover effects for enhanced interactivity

- **Database Schema Updates**:
  - Added `request_number` column to requests table for request tracking (format: R-XXXX)
  - Added `date_received` column to automatically record when documents are received
  - Updated `status` enum to include new statuses: 'Ready to Receive' and 'Received'
  - Migration file provided for existing databases

- **Backend Enhancements**:
  - AJAX-based request details and status updates
  - Server-side status update processing
  - Automatic date recording for received requests
  - Optimized database queries for request filtering and statistics
  - Prepared statements for SQL injection prevention
  - XSS protection with HTML escaping

- **User Interface Improvements**:
  - Responsive Bootstrap 5 integration
  - Statistics cards with hover effects and color coding
  - Professional modal dialogs for viewing and managing requests
  - Search and filter panel with responsive layout
  - Responsive table layout for all device sizes
  - Real-time feedback and status update confirmations

- **Design Consistency**:
  - Matching the Admin Announcements page design and layout
  - Consistent styling, typography, and color scheme
  - Uniform modal and form interactions
  - Professional card-based statistics display

---

# SafeBrgy - Official V1.5

## What's New in V1.5

### Enhanced Admin Reports Management System
- **Statistics Dashboard**: Real-time statistics displaying total reports, pending, ongoing, resolved, and dismissed cases
- **Case Number Tracking**: Automatic 4-digit case number generation for each report
- **Advanced Search & Filter**:
  - Search reports by reporter name or case number
  - Filter results dynamically with form submission
  - Reset functionality to clear filters

- **Report Details Modal** with comprehensive information display:
  - Case number badge for easy identification
  - Report type (Incident, Lost Property, Blotter)
  - Complete description and location information
  - Reporter details including name and email
  - Date filed with timestamp
  - Attachments display capability

- **Status Management System**:
  - Four status levels: Pending, Ongoing, Resolved, Dismissed
  - Color-coded status badges for quick visual identification:
    - Yellow for Pending
    - Cyan for Ongoing
    - Green for Resolved
    - Red for Dismissed
  - Status update dropdown in modal with Apply button
  - Real-time status updates via AJAX

- **Reports Management Table** with enhanced functionality:
  - Case number display with badge styling
  - Report title for quick identification
  - Date filed with formatted timestamp
  - Report type classification (Incident, Lost Property, Blotter)
  - Status indicator with color coding
  - Reporter name display
  - View action button opening detailed modal

- **Statistics Cards** with color-coded design:
  - Total Reports card (blue)
  - Pending Reports card (yellow)
  - Ongoing Reports card (cyan)
  - Resolved Reports card (green)
  - Dismissed Reports card (red)
  - Hover effects for enhanced interactivity

- **Database Schema Updates**:
  - Added `case_number` column to reports table for case tracking
  - Updated `report_type` enum to include 'Blotter' alongside 'Incident' and 'Lost Property'
  - Modified `status` enum from ('New','In Progress','Resolved','Closed') to ('Pending','Ongoing','Resolved','Dismissed')

- **Backend Enhancements**:
  - AJAX-based report details retrieval
  - Server-side status update processing
  - Optimized database queries for report filtering and statistics
  - Prepared statements for SQL injection prevention
  - XSS protection with HTML escaping in JavaScript

- **User Interface Improvements**:
  - Responsive Bootstrap 5 integration
  - Statistics cards with hover effects and color coding
  - Professional modal dialogs for viewing and managing reports
  - Input group styling for search functionality
  - Responsive table layout for all device sizes
  - Real-time feedback and status update confirmations

---

# SafeBrgy - Official V1.4

## What's New in V1.4

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