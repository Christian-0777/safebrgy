## What's New in V2.2.7.1.1 (Minor Update)
The cards in the first row now use col-md-4 for equal width distribution, and the second row has two col-md-6 columns side-by-side without the offset.

## What's New in V2.2.7.1 (Minor Update)

### UI/UX Redesign Layout Refresh

#### Overview
A full redesign of the overall layout and visual system across the landing page, public pages, and admin pages. The update introduces a more professional Bootstrap-based interface with improved spacing, card styling, modern navigation, and responsive behavior for both desktop and mobile screens.

#### Highlights
- Redesigned landing page with a cleaner hero section and polished content layout
- Applied a consistent shared layout system to public and admin pages
- Reworked header, sidebar, cards, buttons, and form styling for a more professional look
- Added responsive mobile navigation with burger menu support
- Sidebar now opens as a mobile drawer for both resident and admin main pages
- Improved overall readability and visual hierarchy across the application

#### Files Updated
- assets/css/shared/layout.css
- assets/js/shared/layout_functions.js
- assets/style.css
- index.php
- admin/main-pages/dashboard.php
- admin/main-pages/profile.php
- admin/main-pages/announcement.php
- admin/main-pages/requests.php
- admin/main-pages/reports.php
- admin/main-pages/user_verification.php
- admin/main-pages/account_settings.php
- public/public-pages/dashboard.php
- public/public-pages/profile.php
- public/public-pages/announcement.php
- public/public-pages/requests.php
- public/public-pages/reports.php
- public/public-pages/account.php

---

## What's New in V2.2.7 (Minor Update)

### Email Notifications for Admin Updates

#### Overview
Added automated email notifications so residents are informed whenever an admin updates the status of their request or report, and whenever a new announcement is published. These emails include the relevant update details and, for announcements, attached images when available.

#### Highlights
- Residents receive email updates when a request status changes
- Residents receive email updates when a report status changes
- Residents receive email notifications for newly created announcements
- Announcement emails include title, priority, message content, and attached pictures

#### Files Updated
- config/mailer.php
- admin/main-pages/requests.php
- admin/main-pages/reports.php
- admin/main-pages/announcement.php

---

## What's New in V2.2.6 (Minor Update)

### Resident Request Page - New Resident Request Portal

#### Overview
A revamped resident request experience that replaces the previous resident request page with a cleaner, modern request portal. The new page uses the shared SafeBrgy header and sidebar, supports all barangay document request types, and integrates with the resident request system submission flow for a smoother request process.

#### Highlights
- Replaced the existing resident request page with a new request portal layout
- Added a modern service-card interface for barangay document requests
- Integrated the shared SafeBrgy header, sidebar, and color system
- Added modal-based request forms for clearance, residency, indigency, and business clearance
- Connected request submission to the resident request system API and database flow
- Added a request history table for tracking submitted requests

#### Files Updated
- public/public-pages/requests.php
- assets/css/public/request.css
- assets/js/public/request.js
- api/requests/create.php
- sql/safebrgy_schema.sql

---

## What's New in V2.2.5 (Minor Update)

### Resident Account Settings - Comprehensive Settings Dashboard

#### Overview
A complete resident account settings page featuring 9 major sections with tab-based navigation for managing personal information, contact details, identification, security, notifications, privacy, support, and account actions. The page provides residents with centralized control over all account settings and preferences.

#### Settings Navigation Tabs (9 Sections)
Tab-based navigation system allowing residents to switch between different settings sections:
1. **Personal Info** - Profile pictures, personal details
2. **Contact Info** - Phone numbers, email, emergency contact
3. **ID** - Valid ID upload and management
4. **Security** - Password, 2FA, login activity
5. **Notifications** - Notification preferences
6. **Privacy** - Data download, privacy policy
7. **Support** - Contact barangay, feedback, issues
8. **About** - Version, terms, privacy policy
9. **Danger Zone** - Deactivate/delete account

#### Section 1: Personal Information
Comprehensive personal profile management:
- **Profile Media**:
  - Profile Picture upload with preview (400x400px recommended, 5MB max)
  - Cover Photo upload with preview (1200x300px recommended, 10MB max)
  - Drag-and-drop file upload support
  
- **Personal Details Form**:
  - First Name, Middle Name, Last Name (required)
  - Suffix dropdown (Jr., Sr., II, III, IV)
  - Gender dropdown (Male, Female, Other)
  - Date of Birth with auto-calculated age display
  - Civil Status dropdown (Single, Married, Widowed, Separated, Divorced)
  - Nationality and Occupation text fields
  
- **Database Updates**: Saves to `residents` table with all personal information
- **Session Update**: Updates session user data after successful save

#### Section 2: Contact Information
Complete contact management:
- **Phone Number**: Main contact number (required) with placeholder
- **Mobile Number**: Alternative mobile number
- **Email Address**: Primary email for notifications (required, validated)
- **Emergency Contact Name**: Contact person name
- **Emergency Contact Number**: Emergency phone number
- **Validation**: Email uniqueness check, phone format validation
- **Database Updates**: Updates both `users` and `residents` tables

#### Section 3: Valid ID Update
ID document management:
- **Current ID Display**: Shows previously uploaded ID with full-size preview button
- **Upload New ID**:
  - Drag-and-drop upload area with visual feedback
  - Supported formats: JPG, PNG, PDF (10MB max)
  - File type and size validation with MIME type verification
  - Image preview for JPG/PNG files
  - PDF notification for PDF files
- **Database Storage**: Saves file path to `residents.valid_id_path`
- **File Organization**: Uploaded to `/uploads/valid_ids/` directory

#### Section 4: Security Settings
Account security management:
- **Change Password**:
  - Current password verification
  - New password with strength requirements:
    - Minimum 8 characters
    - At least one uppercase letter
    - At least one lowercase letter
    - At least one number
  - Visual password strength indicator (5-level scale)
  - Password confirmation field
  - Collapse form for compact display
  
- **Two-Factor Authentication (2FA)**:
  - Toggle switch to enable/disable 2FA
  - Label shows current status (Enable/Disable)
  
- **Login Activity**:
  - Timeline display of recent login sessions
  - Shows device type, browser, date/time
  - Current session highlighted as "Active"
  - Visual timeline with device icons
  
- **Logout All Other Devices**:
  - Button to sign out from all other devices
  - Maintains current session

#### Section 5: Notification Preferences
Customizable notification settings:
- **Document Request Updates**: Toggle for notification about request status changes
- **Barangay Announcements**: Toggle for receiving important announcements
- **Reports Update**: Toggle for report status notifications
- **Toggle Switches**: Form-check-input components with visual feedback
- **Default**: All notifications enabled by default
- **Database Storage**: Saves to session (future: `user_notification_preferences` table)

#### Section 6: Privacy & Data
Data privacy and management:
- **Download Personal Data**:
  - Generates JSON export of all user data
  - Includes: personal info, requests, reports
  - Auto-download file naming: `safebrgy-personal-data-YYYY-MM-DD.json`
  
- **Privacy Policy Link**:
  - Direct link to `/external-links/privacy-policy.html`
  - Opens in new tab
  
- **Activity Log**:
  - Link to view account activity history
  - Shows recent actions and changes

#### Section 7: Support & Help
Support and contact options:
- **Contact Barangay Office**:
  - Modal form for sending messages
  - Subject and message fields
  - Submits via `api/account/send_contact.php`
  
- **Submit Feedback**:
  - Modal form for feedback submission
  - Feedback type selector (Bug Report, Feature Request, Improvement, General)
  - Message textarea for detailed feedback
  
- **Report an Issue**:
  - Modal form for reporting technical/security issues
  - Issue type selector (Technical, Security, Other)
  - Description and affected page fields
  - Submits via `api/account/report_issue.php`

#### Section 8: About SafeBrgy
System information:
- **Version Information**:
  - SafeBrgy Version: 1.0.0
  - Last Updated: June 2024
  - Status badge showing "You're using the latest version"
  
- **Terms & Conditions**:
  - Link to `/external-links/terms-of-service.html`
  - Opens in new tab
  
- **Privacy Policy**:
  - Link to `/external-links/privacy-policy.html`
  - Duplicate link for easy access
  
- **Developed By**:
  - Credits to SafeBrgy Development Team

#### Section 9: Danger Zone
Destructive account actions:
- **Deactivate Account**:
  - Temporarily disables account (reversible)
  - Warning message: "Your profile will be hidden from other residents"
  - Note: "You can reactivate it anytime by logging in"
  - Modal confirmation with optional reason textarea
  
- **Delete Account**:
  - Permanently deletes account and all data
  - Warning: "This action cannot be reversed"
  - Lists what will happen (permanent deletion of all data)
  - Requires typing "DELETE" to confirm (case-sensitive)
  - Optional reason for deletion
  - Database transaction with rollback on error
  - Destroys session and redirects to home

#### Design & Styling

**Color Scheme**:
- Primary: #0b63d6 (Blue) - headers, buttons, icons
- Danger: #f32b36 (Red) - danger zone, delete actions
- Warning: #ffc107 (Yellow) - warnings, cautions
- Success: #19a964 (Green) - success messages
- Light: #f8f9fa (Off-white) - backgrounds

**Layout & Typography**:
- Font: Arial, sans-serif throughout
- Page Header: 32px bold with icon
- Tab Navigation: Horizontal scrollable tabs with active state
- Settings Card: White card with section header and content area
- Form Elements: Consistent padding, focus states, validation styling
- Buttons: Inline-flex with icons and text, hover effects

**Responsive Design**:
- Desktop (1400px+): Full layout with tabs visible
- Tablet (768px-1399px): Adjusted spacing, 2-column forms
- Mobile (<576px): Stacked tabs, single-column forms, reduced padding

**Visual Effects**:
- Tab hover: Border color change to primary blue
- Tab active: Full primary blue background with white text
- Button hover: Elevation with translateY(-2px) and shadow
- Form focus: Blue border with subtle shadow
- Card hover: Subtle elevation effect
- Smooth transitions: 0.3s ease on all interactive elements

#### File Upload & Media Handling

**Profile Picture**:
- Size: 400x400px recommended
- Format: JPG, PNG
- Max size: 5MB
- Preview shows in placeholder
- Drag-and-drop support

**Cover Photo**:
- Size: 1200x300px recommended
- Format: JPG, PNG
- Max size: 10MB
- Gradient blue background default
- Background image overlay on upload

**Valid ID**:
- Formats: JPG, PNG, PDF
- Max size: 10MB
- MIME type validation with finfo
- Drag-and-drop upload area
- Image preview for visual formats

#### Form Validation

**Client-Side**:
- Required field validation
- Email format validation (regex)
- Phone format validation
- Birthdate format validation
- File type and size checks
- Password strength indicators
- Drag-and-drop feedback

**Server-Side**:
- Prepared statements (SQL injection prevention)
- Email uniqueness verification
- Password verification against current hash
- File MIME type verification
- File size validation
- HTML sanitization with htmlspecialchars()

#### Backend API Endpoints (api/account/)

| Endpoint | Method | Function |
|----------|--------|----------|
| `update_personal.php` | POST | Save personal information |
| `update_contact.php` | POST | Save contact details |
| `update_id.php` | POST | Upload valid ID file |
| `update_password.php` | POST | Change password with validation |
| `update_notifications.php` | POST | Save notification preferences |
| `send_contact.php` | POST | Message barangay office |
| `send_feedback.php` | POST | Submit feedback |
| `report_issue.php` | POST | Report issues/bugs |
| `download_data.php` | GET | Export personal data as JSON |
| `deactivate_account.php` | POST | Temporarily disable account |
| `delete_account.php` | POST | Permanently delete account |

#### Security Features

- Session authentication check (residents only)
- User role verification
- Prepared SQL statements (all queries)
- HTML sanitization with htmlspecialchars()
- Email uniqueness check
- Current password verification before change
- Password strength requirements:
  - Minimum 8 characters
  - Uppercase, lowercase, numbers required
- File type validation with MIME verification
- File size limits (5MB for pictures, 10MB for ID)
- "DELETE" confirmation text for account deletion
- Database transactions for data deletion
- User data isolation (can only access own data)

#### Database Integration

**Tables Used**:
- `users` table: email, phone, password_hash, created_at
- `residents` table: first_name, middle_name, last_name, gender, birthdate, civil_status, nationality, occupation, mobile_number, emergency_contact_name, emergency_contact_number, valid_id_path, profile_image_path

**Queries**:
- SELECT personal/contact info: JOIN residents with users
- UPDATE operations: Both tables as needed
- File path storage: Relative paths for uploads

#### Frontend Features

**Tab System**:
- Active tab highlighting with blue background
- Content sections toggle with display:none/block
- Smooth tab switching
- Scroll-to-top on tab change

**Form Handling**:
- Real-time validation feedback
- Required field indicators
- Collapsible sections (e.g., Change Password)
- Modal dialogs for confirmations
- Toast notifications for messages

**File Preview**:
- Image preview on file selection
- Drag-and-drop visual feedback
- File list with remove buttons
- Progress indicators for uploads

**Password Strength**:
- Visual strength meter (5-level scale)
- Color-coded strength indicator
- Real-time calculation on input

**Modals**:
- Contact office modal with subject/message
- Feedback modal with type selector
- Issue report modal with description
- Account deactivation confirmation
- Account deletion confirmation with "DELETE" requirement

#### JavaScript Functionality (assets/js/public/account.js)

**Tab Management**:
- `document.querySelectorAll('.settings-tab')` event listeners
- Tab click handler with show/hide logic
- Active class management

**File Upload Handlers**:
- `handleProfilePictureChange()`: Validates and previews profile image
- `handleCoverPhotoChange()`: Validates and previews cover photo
- `handleIdFileChange()`: Validates ID file with type checking

**Drag-and-Drop**:
- `handleDragOver()`: Visual feedback on drag
- `handleDragLeave()`: Reset visual state
- `handleDrop()`: Process dropped files

**Form Validation**:
- `handlePersonalInfoSubmit()`: Required field validation
- `handleContactFormSubmit()`: Email format and required field validation
- `handleNotificationSubmit()`: Optional field handling

**Security Functions**:
- `toggleTwoFactor()`: 2FA toggle handling
- `logoutAllDevices()`: Logout other sessions

**Account Actions**:
- `downloadPersonalData()`: Triggers JSON download
- `showDeactivateConfirm()`: Shows deactivation modal
- `confirmDeactivate()`: Submits deactivation request
- `showDeleteConfirm()`: Shows deletion modal with confirmation
- `confirmDelete()`: Submits deletion request

**Utilities**:
- `showNotification()`: Auto-dismissing alert notifications
- `calculatePasswordStrength()`: 5-level strength calculation
- `updatePasswordStrengthIndicator()`: Visual strength display

#### CSS Styling (assets/css/public/account.css)

**Layout**:
- `.main-content`: Padding-top 80px for header
- `.container-fluid`: Max-width 1400px
- `.settings-header`: Page title and description
- `.settings-tabs`: Horizontal tab navigation
- `.settings-card`: White card containers with shadows
- `.section-header`: Card header with background color
- `.section-content`: Card content with padding

**Forms**:
- `.form-label`: Bold, uppercase labels
- `.form-control`: Blue focus state, smooth transitions
- `.form-select`: Matching control styling
- `.upload-area`: Dashed border, hover highlight

**Buttons**:
- `.btn-primary`: Blue background with hover effect
- `.btn-danger`: Red background for destructive actions
- `.btn-warning`: Yellow for warnings
- `.btn-outline-*`: Outlined variants
- All buttons: Inline-flex with icon + text alignment

**Responsive**:
- Mobile breakpoint: 576px
- Tablet breakpoint: 768px
- Tab stack on mobile
- Single-column forms on mobile
- Reduced padding on small screens
- Full-width buttons on mobile

#### Files Created/Modified

- **UPDATED**: `public/public-pages/account.php` - Complete rewrite with 9 sections and tab navigation
- **UPDATED**: `assets/css/public/account.css` - Comprehensive styling with responsive design
- **UPDATED**: `assets/js/public/account.js` - Interactive features and form handling
- **NEW**: `api/account/update_personal.php` - Personal info update endpoint
- **NEW**: `api/account/update_contact.php` - Contact info update endpoint
- **NEW**: `api/account/update_id.php` - Valid ID upload endpoint
- **NEW**: `api/account/update_password.php` - Password change endpoint
- **NEW**: `api/account/update_notifications.php` - Notification preferences endpoint
- **NEW**: `api/account/send_contact.php` - Contact message endpoint
- **NEW**: `api/account/send_feedback.php` - Feedback submission endpoint
- **NEW**: `api/account/report_issue.php` - Issue reporting endpoint
- **NEW**: `api/account/download_data.php` - Personal data export endpoint
- **NEW**: `api/account/deactivate_account.php` - Account deactivation endpoint
- **NEW**: `api/account/delete_account.php` - Account deletion endpoint

#### User Workflows

**Update Personal Information**:
1. Click "Personal Info" tab
2. Fill in form fields
3. Upload profile/cover photos (optional)
4. Click "Save Personal Information"
5. Success message appears

**Change Password**:
1. Click "Security" tab
2. Click "Update" button to expand form
3. Enter current password
4. Enter new password (shows strength meter)
5. Confirm password
6. Submit to save

**Download Personal Data**:
1. Click "Privacy" tab
2. Click "Download Data" button
3. JSON file auto-downloads to machine
4. Contains all personal info, requests, reports

**Delete Account**:
1. Click "Danger Zone" tab
2. Click "Delete Account" button
3. Read warning message
4. Type "DELETE" to confirm
5. Optionally provide reason
6. Submit to permanently delete

#### Database Migration Needed

```sql
-- Optional: Add additional columns for enhanced features
ALTER TABLE users ADD COLUMN is_active TINYINT(1) DEFAULT 1;
ALTER TABLE users ADD COLUMN two_factor_enabled TINYINT(1) DEFAULT 0;

-- Create notification preferences table (optional)
CREATE TABLE user_notification_preferences (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  document_updates TINYINT(1) DEFAULT 1,
  announcements TINYINT(1) DEFAULT 1,
  reports TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create feedback table
CREATE TABLE feedback (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  type VARCHAR(50),
  message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create issue reports table
CREATE TABLE issue_reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  type VARCHAR(50),
  description TEXT,
  page VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### Design Consistency
- Matches resident dashboard design patterns
- Same color scheme and typography
- Consistent header and sidebar with dashboard
- Bootstrap 5 grid system for uniformity
- Professional card-based layout throughout
- Font Awesome icons matching other pages

#### Future Enhancement Opportunities
- Two-factor authentication setup with authenticator app
- Profile picture upload with crop functionality
- Cover photo upload with positioning controls
- Activity log with detailed action history
- Login attempt security alerts
- Device management interface
- Export data in additional formats (CSV, PDF)
- Session timeout management
- Email change verification
- Phone number verification
- Account recovery options
- Backup codes for 2FA

#### Performance Considerations
- Optimized file upload with size validation
- Efficient database queries with prepared statements
- CSS animations using GPU acceleration
- Lazy-loading for modals and heavy content
- Session storage for temporary preferences
- Minimal JavaScript for fast load times

#### Testing Checklist
- ✓ Tab navigation switches content correctly
- ✓ Personal info form saves to database
- ✓ Contact info updates both tables
- ✓ Valid ID upload with file validation
- ✓ Password change with strength requirements
- ✓ Notification preferences save
- ✓ Download personal data as JSON
- ✓ Contact/feedback/issue modals display
- ✓ Account deactivation works
- ✓ Account deletion requires "DELETE" confirmation
- ✓ File uploads to correct directory
- ✓ Responsive design on all screen sizes
- ✓ All validations work (email, phone, file size)
- ✓ Error messages display correctly
- ✓ Success messages appear after updates

---

## What's New in V2.2.4 (Minor Update)

### Resident Profile Page - Comprehensive Profile Dashboard

#### Overview
A complete resident profile page displaying personal information, contact details, identification status, and requested documents history. The page provides residents with a centralized view of their account information and document request tracking.

#### Profile Header Section
- **Cover Photo**: Gradient blue background with decorative pattern
- **Profile Picture**: Circular avatar with initial letter fallback for residents without profile images
- **Full Name**: Large heading displaying resident's complete name
- **Resident ID**: Unique 7-digit identifier with ID card icon
- **Edit Profile Button**: Direct link to account settings page for updating profile information

#### Personal Information Section
Comprehensive display of resident demographics:
- **First Name**: Resident's given name
- **Middle Name**: Resident's middle name (if available)
- **Last Name**: Resident's family name
- **Gender**: Male, Female, or Other
- **Date of Birth**: Formatted as "Month Day, Year"
- **Age Calculation**: Automatically calculated from birthdate, displayed as "X years, Y months, Z days" format
- **Civil Status**: Single, Married, Widowed, Separated, or Divorced
- **Nationality**: Country of citizenship
- **Occupation**: Current employment or profession

**Layout**: Responsive 3-column grid on desktop, adapting to mobile devices

#### Contact Information Section
Contact details for resident communication:
- **Mobile Number**: Phone number with icon
- **Email Address**: Email account for correspondence
- **Icons**: Font Awesome phone and envelope icons for visual clarity

**Layout**: 2-column responsive grid

#### Identification Section
Verification and ID documentation:
- **Valid ID Preview**: Uploaded identification document image
  - Clickable to open full-size modal preview
  - Professional border and shadow styling
  - Hover effects for interactivity
- **Verification Status Badge**:
  - Green badge with checkmark for "Verified" residents
  - Orange badge with clock icon for "Pending Verification"
  - Easy visual identification of account status

**Layout**: 2-column grid with image on left, status on right

#### Requested Documents History Section
Table displaying resident's document requests:
- **Columns**:
  - **Document Type**: Type of requested document with PDF icon
  - **Request Date**: Formatted submission date
  - **Status**: Color-coded badge indicator
    - Yellow: Pending
    - Blue: Processing
    - Green: Approved, Ready to Receive
    - Gray: Received
    - Red: Rejected
  - **Action**: Download button
    - Enabled only for "Ready to Receive" or "Received" status
    - Links to download endpoint
    - Disabled state for pending/processing requests

**Features**:
- Displays up to 10 most recent requests
- Hover effects for better interactivity
- Empty state message with icon when no requests exist
- Responsive table with horizontal scroll on mobile
- Direct navigation to full requests page for additional entries

#### Logout Button
- Positioned at bottom of page in danger red color
- Sign-out icon with text
- Confirmation dialog before logout
- Redirects to logout endpoint

#### Design & Styling
- **Color Scheme**: 
  - Primary: #0b63d6 (Blue) for headers, buttons, and accents
  - Table Header: #3C8E95 (Teal) for professional appearance
  - Status Badges: Color-coded (green, orange, blue, gray, red)
  - Background: Light gray (#f8f9fa) for subtle contrast
- **Layout**: Bootstrap 5 responsive grid system
- **Typography**: Arial font family throughout
- **Cards**: White background cards with subtle shadows
- **Icons**: Font Awesome 6.4.0 for visual indicators
- **Responsive Breakpoints**:
  - Desktop (1400px+): Full layout
  - Tablet (768px-1399px): Adjusted columns
  - Mobile (<768px): Stacked layout

#### Database Integration

**Queries**:
1. Resident Data: `SELECT r.*, u.is_verified, u.profile_image FROM residents r LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = ?`
2. Documents History: `SELECT id, request_type, created_at, status FROM requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 10`
3. User Verification: Check `u.is_verified` field for verification status

**Data Sources**:
- `residents` table: All personal information (name, birthdate, gender, civil status, nationality, occupation, mobile number, valid ID path, profile image)
- `users` table: Verification status and profile image
- `requests` table: Document request history with types and statuses

**Helper Functions**:
- `calculateAge($birthdate)`: Returns array with years, months, days
- `formatDate($date)`: Formats date as "Month Day, Year"
- `getStatusBadgeClass($status)`: Returns CSS badge class for status

#### Interactive Features

**JavaScript Functionality**:
- `openImageModal()`: Opens Bootstrap modal for full-size ID image preview
- `handleLogout()`: Displays confirmation dialog before logout
- `downloadDocument()`: Validates document availability before download
- `viewMoreRequests()`: Navigation to full requests page
- `showNotification()`: Toast-style notification display

**Bootstrap Modal**:
- Image preview modal with centered layout
- Large image display area
- Professional modal styling with close button

#### Image Modal
- Full-screen image preview for ID documents
- Modal title: "Valid ID Preview"
- Large responsive image display
- Dismissible close button
- Professional styling consistent with page design

#### Security Features
- Session authentication check (residents only)
- User role verification
- Data isolation (residents see only their own profile)
- HTML sanitization with htmlspecialchars()
- Secure file paths for ID images
- Prepared SQL statements for all queries

#### User Workflows

**View Profile**:
1. Navigate to Profile page from sidebar
2. View all personal information automatically populated
3. Review verification status
4. Check document request history
5. Download completed documents if available

**Edit Profile**:
1. Click "Edit Profile" button
2. Redirected to account settings page
3. Update information
4. Save changes

**Request Documents**:
1. View document request history
2. Click on request to view details
3. Download completed documents
4. Navigate to requests page for new requests

#### Design Consistency
- Matches resident dashboard design patterns
- Same color scheme and typography as other resident pages
- Consistent header and sidebar with dashboard
- Bootstrap 5 grid system for uniformity
- Professional card-based layout throughout
- Font Awesome icons matching other pages

#### Files Created/Modified
- **MODIFIED**: `public/public-pages/profile.php`: Complete rewrite with database queries and helper functions
  - Fetches resident data with verification status
  - Calculates age from birthdate
  - Retrieves document request history
  - Displays all profile sections with proper formatting
  - Includes image modal for ID preview
  
- **MODIFIED**: `assets/css/public/profile.css`: Comprehensive styling
  - Profile header with cover photo and picture
  - Section styling with cards and shadows
  - Table styling with hover effects
  - Badge colors for status indicators
  - Modal styling for image preview
  - Responsive design for all screen sizes
  - Typography and spacing consistency
  
- **MODIFIED**: `assets/js/public/profile.js`: Interactive features
  - Image modal functionality
  - Logout confirmation
  - Notification system
  - Document download validation
  - Helper functions for formatting

#### Responsive Design
- **Desktop (1400px+)**: Full 3-column grid for personal info, side-by-side layouts for identification
- **Tablet (768px-1399px)**: 2-column grid, adjusted spacing
- **Mobile (<768px)**: Single column stack, full-width elements, optimized spacing

#### Empty States
- When no requested documents: "No requested documents yet" message with inbox icon and link to create request
- Graceful handling of missing data with N/A defaults
- User-friendly messaging throughout

#### Performance Considerations
- Efficient database queries with prepared statements
- Limited result set (10 most recent documents)
- CSS optimizations with GPU acceleration
- Image lazy-loading ready
- Minimal JavaScript for fast load times

#### Future Enhancement Opportunities
- Add profile picture upload functionality
- Add cover photo customization
- Add profile information edit modal
- Add document status timeline
- Add document status notification alerts
- Add profile export to PDF
- Add activity log section
- Add family members section
- Add emergency contacts section
- Add document re-upload for rejected applications

---

## What's New in V2.2.3 (Minor Update)

## Resident ID System - Automated 7-Digit ID Generation

#### Overview
A new resident identification system that automatically generates unique 7-digit resident IDs upon successful registration. Each resident receives a permanent identifier stored in their profile for tracking and identification purposes within the barangay system.

#### Key Features
- **Automatic ID Generation**: 7-digit numeric IDs (1000000-9999999) generated during registration
- **Unique Identifier System**: Each resident receives a unique ID with database-level uniqueness constraints
- **Registration Integration**: ID is assigned immediately upon successful OTP verification and account creation
- **Permanent Storage**: Resident IDs are stored in the `residents` table and linked to user profiles

#### Technical Implementation

**Database Schema Updates**:
- Added `resident_id VARCHAR(7) UNIQUE NOT NULL` field to `residents` table
- Positioned as the second column for easy reference
- Unique constraint ensures no duplicate IDs can exist

**ID Generation Function** (`generateResidentId()` in `config/db.php`):
- Generates random 7-digit numbers within the range 1000000-9999999
- Performs database uniqueness verification before assigning
- Implements retry logic with maximum 100 attempts for ID generation
- Throws exception if unique ID cannot be generated (safeguard against infinite loops)

**Registration Flow Integration**:
- ID is generated during initial registration form submission
- ID is stored in session alongside other pending registration data
- ID is permanently saved to database upon successful OTP verification
- ID is available immediately for resident identification and tracking

#### How It Works
1. Resident completes registration form
2. System generates unique 7-digit resident ID
3. ID is temporarily stored in session during OTP verification step
4. Upon successful OTP verification, resident account is created with assigned ID
5. Resident ID can be used for identification, document tracking, and system queries
6. ID remains permanent throughout the resident's account lifecycle

#### Database Queries
- `generateResidentId()`: Generates and validates unique 7-digit IDs
  ```php
  SELECT id FROM residents WHERE resident_id = ?
  ```
- Ensures uniqueness before ID assignment to prevent conflicts

#### Files Modified
- `sql/safebrgy_schema.sql`: Added `resident_id` column to residents table
- `config/db.php`: Added `generateResidentId()` helper function
- `register.php`: Integrated ID generation into registration process
- `public/verify_otp_process.php`: Updated to store resident_id during account creation

#### Future Enhancements
- Display resident ID on resident profile page
- Include resident ID in certificates and documents
- Use resident ID for advanced search and filtering
- Integrate resident ID into admin tracking systems
- Generate resident ID cards or badges
- API endpoints for resident ID lookups

---

# What's New in V2.2.2 (Minor Update)

### Admin Profile Page - Complete Profile & Activity Dashboard

#### Overview
A comprehensive admin profile page displaying personal admin information, account statistics, activity logs, and profile management options. The page provides administrators with a complete overview of their account and system activities.

#### Admin Information Section
Personal admin profile card featuring:
- **Profile Picture**: Circular avatar with initial letter fallback for admins without profile images
- **Admin Name**: Large heading with admin username
- **Admin Role Badge**: "System Administrator" role indicator with badge icon
- **Contact Information**:
  - Email Address with envelope icon
  - Contact Number with phone icon
  - Date Joined (formatted as "Month Day, Year")
  - Last Login (formatted as "Month Day, Year at H:MM A")
- **Edit Profile Button**: Direct link to account_settings.php for profile updates
- **Responsive Layout**: Profile picture on left (mobile: top), info in center, edit button on right (mobile: bottom)

#### Account Statistics Section
Four color-coded statistic cards displaying admin activity metrics:

1. **Total Residents Managed** (Blue card)
   - Icon: Users icon (#007bff)
   - Count: Total verified residents
   - Data Source: Residents joined with verified users table

2. **Documents Processed** (Green card)
   - Icon: File-check icon (#28a745)
   - Count: Approved/completed requests
   - Status filter: 'Approved', 'Ready to Receive', 'Received'

3. **Announcements Posted** (Yellow card)
   - Icon: Bullhorn icon (#ffc107)
   - Count: Announcements created by this admin
   - Filtered by author_id from announcements table

4. **Cases Handled** (Red card)
   - Icon: Briefcase icon (#dc3545)
   - Count: Resolved/dismissed reports
   - Status filter: 'Resolved', 'Dismissed'

**Card Features**:
- Flexbox layout with icon and content side-by-side
- Color-coded left border indicators matching icon color
- Large readable numbers with number_format() for thousands separator
- Responsive grid layout (4 columns desktop, 2 columns tablet, 1 column mobile)
- Hover effects with elevation and shadow animations

#### Activity Log Section
Comprehensive activity tracking table displaying admin actions:

**Table Features**:
- Columns:
  - **Date & Time**: Formatted timestamp (Mon DD, YYYY at H:i A) with calendar icon
  - **Activity**: Action description with optional metadata
- Display: Latest activities shown on top (ORDER BY created_at DESC)
- Limit: Shows up to 20 most recent logs
- Metadata Display: JSON metadata parsed and displayed as secondary text
- Empty State: "No activity logs available" message with inbox icon when no data
- Table Styling: 
  - Header with light gray background (#f8f9fa)
  - Row hover effects for interactivity
  - Responsive design with horizontal scroll on mobile

**Data Source**: Queries `admin_logs` table filtered by admin_id

#### Action Buttons Section
Two action buttons at the bottom of the page:

1. **Download Information Button** (Secondary gray button)
   - Icon: Download icon
   - Function: Exports admin profile data as CSV file
   - Exports:
     - Profile Information (Name, Email, Phone, Date Joined, Last Login)
     - Account Statistics (Residents Managed, Documents Processed, Announcements, Cases Handled)
   - File naming: `admin-profile-{timestamp}.csv`
   - CSV Format: Two-column key-value pairs

2. **Logout Button** (Danger red button)
   - Icon: Sign-out icon
   - Function: Logs out admin with confirmation dialog
   - Confirmation: "Are you sure you want to logout?"
   - Redirects to: `admin/logout.php`
   - Form submission on confirm

#### Download Information Feature
**Functionality**:
- Extracts profile data from visible page elements using DOM queries
- Generates CSV content with headers and formatted data
- Creates downloadable file with auto-generated filename including timestamp
- Shows success notification after download
- Toast notification displays: "Profile information downloaded successfully!"

**CSV Format**:
```
"Admin Profile Information","June 16, 2026 2:30 PM"
""
"Profile Information",""
"Name","John Admin"
"Email","admin@example.com"
"Contact Number","+63 912 345 6789"
"Date Joined","June 01, 2026"
"Last Login","June 16, 2026 at 2:00 PM"
""
"Account Statistics",""
"Total Residents Managed","1,250"
"Documents Processed","487"
"Announcements Posted","23"
"Cases Handled","156"
```

#### Design & Styling

**Color Scheme**:
- Primary: #007bff (Blue) - for statistics and primary actions
- Success: #28a745 (Green) - for positive metrics
- Warning: #ffc107 (Yellow) - for caution metrics
- Danger: #dc3545 (Red) - for important metrics
- Secondary: #6c757d (Gray) - for secondary actions
- Light: #f8f9fa (Off-white) - for backgrounds

**Layout & Typography**:
- Font: Arial, sans-serif throughout
- Admin Name: 28px, font-weight 700
- Section Titles: 18px with bottom border and icon
- Stat Values: 28px bold with color-coding
- Regular Text: 14px for body content
- Labels: 12px uppercase with letter-spacing

**Visual Effects**:
- Card hover: Elevation with translateY(-3px) and enhanced shadow
- Shadow: 0 2px 4px rgba(0,0,0,0.1) base, 0 4px 12px on hover
- Transitions: All 0.3s ease for smooth animations
- Profile Picture: 150px circular with 4px border and shadow
- Avatar Fallback: Gradient blue background with large white letter

#### Responsive Design

**Desktop (1400px+)**:
- Admin info card: 3-column layout (picture | info | button)
- Statistics: 4-column grid
- Full-width layout with max-width 1400px

**Tablet (768px - 1399px)**:
- Admin info card: Info section wraps below picture
- Statistics: 2-column grid
- Button centers or stacks based on space

**Mobile (<576px)**:
- Admin info card: Stack vertically (picture, info, button)
- Statistics: 1-column stack
- Activity table: Responsive text size reduction
- Action buttons: Full-width stack

#### Database Integration

**Queries**:
1. Admin Info: SELECT username, email, phone, profile_image, created_at, updated_at FROM users
2. Residents Managed: SELECT COUNT(*) FROM residents r JOIN users u ON r.user_id = u.id WHERE u.is_verified = 1
3. Documents Processed: SELECT COUNT(*) FROM requests WHERE status IN ('Approved', 'Ready to Receive', 'Received')
4. Announcements Posted: SELECT COUNT(*) FROM announcements WHERE author_id = :adminId
5. Cases Handled: SELECT COUNT(*) FROM reports WHERE status IN ('Resolved', 'Dismissed')
6. Last Login: SELECT created_at FROM admin_logs WHERE admin_id = :adminId ORDER BY created_at DESC LIMIT 1
7. Activity Logs: SELECT * FROM admin_logs WHERE admin_id = :adminId ORDER BY created_at DESC LIMIT 20

**Prepared Statements**: All queries use prepared statements with parameterized queries for SQL injection prevention

#### Frontend Features

**JavaScript Functionality**:
- Event listeners for Download and Logout buttons
- DOM queries to extract profile data from page elements
- CSV generation with proper escaping and formatting
- File download mechanism using Blob and data URI
- Toast notification system with animations
- Logout confirmation dialog with preventDefault

**Animations**:
- Notification slide-in from right (0.3s ease)
- Notification slide-out to right before removal
- Smooth CSS transitions on all interactive elements
- Hover effects on stat cards and buttons

#### Security Features

- Session authentication check via `admin_protect.php`
- Prepared statements for all database queries
- HTML sanitization with htmlspecialchars()
- User data isolation (admins see only their own profile)
- Logout confirmation to prevent accidental logouts
- No sensitive data in browser console

#### Files Created/Modified

- **admin/main-pages/profile.php**: Complete profile page with database integration
- **assets/css/admin/profile.css**: Comprehensive styling with responsive design
- **assets/js/admin/profile.js**: Download, logout, and notification functionality
- **Bootstrap 5.3.0**: CDN integration for responsive grid system

#### Design Consistency

- Follows admin dashboard design patterns
- Uses same color scheme and typography as other admin pages
- Consistent stat card styling with dashboard
- Responsive Bootstrap 5 grid system matching other pages
- Professional card-based layout throughout
- Same icons and visual hierarchy as dashboard

#### Files Modified/Referenced

- `admin/main-pages/profile.php`: Profile page main file
- `assets/css/admin/profile.css`: Profile page styling
- `assets/js/admin/profile.js`: Profile page JavaScript
- `assets/css/shared/`: Shared header and sidebar styles
- `admin/main-pages/account_settings.php`: Edit profile destination
- `admin/logout.php`: Logout destination

---

## What's New in V2.2.1 (Minor Update)

### Admin User Verification Page - Status Cards Redesign

#### Overview
A visual redesign of the admin user verification page featuring status cards at the top for quick visibility into account verification metrics, matching the dashboard design patterns and layout preferences.

#### Statistics Summary Cards (3 Key Metrics)
Three responsive status cards displaying verification statistics:

1. **Pending Verification** (Yellow/Warning color)
   - Icon: Hourglass-half icon
   - Count: Total unverified accounts awaiting approval
   - Color Code: #ffc107 (Warning)

2. **Verified Accounts** (Green/Success color)
   - Icon: Check-circle icon
   - Count: Total approved resident accounts
   - Color Code: #28a745 (Success)

3. **Rejected Accounts** (Red/Danger color)
   - Icon: Times-circle icon
   - Count: Total rejected/deleted accounts (from admin_logs)
   - Color Code: #dc3545 (Danger)

**Card Features**:
- Flexbox layout with icon and content side-by-side
- Hover effects with elevation and shadow animations
- Color-coded left border indicators
- Large readable numbers with formatted values
- Responsive grid layout (3 columns desktop, 2 columns tablet, 1 column mobile)
- Uppercase label text with letter-spacing for visual hierarchy

#### Table Enhancements
- Updated table styling with card containers and subtle shadows
- Section headers now use standardized `.section-title` class with color variations:
  - Yellow (#ffc107) for "Pending Verification"
  - Green (#28a745) for "Verified Accounts"
- Improved empty state messages with Font Awesome icons
- Better padding, spacing, and hover effects for readability
- Enhanced responsive design

#### Design Consistency
- Follows admin dashboard design patterns for `stat-card` components
- Uses same color scheme and typography as dashboard
- Maintains responsive Bootstrap 5 grid system
- Consistent shadow effects and hover animations
- Professional card-based layout throughout

#### Database Integration
- Queries for verification statistics:
  - `SELECT COUNT(*) FROM users WHERE role = "resident" AND is_verified = 1` (Verified count)
  - `SELECT COUNT(*) FROM users WHERE role = "resident" AND is_verified = 0` (Pending count)
  - `SELECT COUNT(*) FROM admin_logs WHERE action = "reject_user"` (Rejected count from logs)

#### Files Modified
- `admin/main-pages/user_verification.php`: Added statistics queries and redesigned layout with status cards
- `assets/css/admin/user_verification.css`: Updated styling with stat-card classes and enhanced table design

---

## What's New in V2.2

### Resident Request Page - Complete Modal System with Multi-Step Workflow

#### Overview
A comprehensive multi-document request system for residents to submit applications for various barangay documents. The implementation includes dynamic multi-step modals, type-specific form handling, file uploads, and a complete backend API for request processing with email notifications.

#### Services Section - Four Request Types
Interactive service cards showcasing available request options:

1. **Barangay Clearance** (Blue gradient icon)
   - Certificate of good moral character
   - Quick access via service card with "Request Now" button

2. **Barangay Residency** (Green gradient icon)
   - Proof of residency in barangay
   - Quick access via service card with "Request Now" button

3. **Barangay Indigency** (Cyan gradient icon)
   - Certificate of indigency for assistance programs
   - Quick access via service card with "Request Now" button

4. **Barangay Business Clearance** (Yellow gradient icon)
   - Business operations clearance
   - Quick access via service card with "Request Now" button

#### Status Overview Dashboard
Four status tracking cards displaying live statistics:
- **Pending**: Count of requests awaiting processing (warning yellow badge)
- **Processing**: Count of requests currently under review (primary blue badge)
- **Ready to Receive**: Count of completed requests (success green badge)
- **Received**: Count of documents already received (secondary gray badge)

#### Search Functionality
- Real-time search bar for finding requests
- Search by request type or reference number
- Case-insensitive matching with instant results
- "No results" message when appropriate

#### Request Modals - Type-Specific Forms

**Modal 1: Barangay Clearance Request**
- Fields: Purpose of Request (required, textarea)
- Supporting Documents (optional, multi-file drag-drop upload)
- Submit button with primary blue color
- Dynamic file list showing selected documents

**Modal 2: Barangay Residency Request**
- Fields:
  - Years of Residency (required, number input)
  - Date Started Living in Barangay (required, date picker)
  - Purpose of Request (required, textarea)
  - Supporting Documents (optional, multi-file upload)
- Submit button with success green color
- Dynamic file list with remove functionality

**Modal 3: Barangay Indigency Request**
- Fields:
  - Monthly Income (required, decimal number)
  - Number of Household Members (required, integer)
  - Type of Assistance Needed (required, dropdown)
    - Medical Assistance
    - Educational Assistance
    - Financial Assistance
    - Burial Assistance
    - Others (conditional - shows additional text input when selected)
  - Purpose of Request (required, textarea)
  - Supporting Documents (optional, multi-file upload)
- Submit button with info cyan color
- Conditional field visibility for "Others" assistance type
- Dynamic file list with file type icons

**Modal 4: Business Clearance Request**
- Fields:
  - Business Name (required, text input)
  - Business Full Address (required, textarea)
  - Contact Number (required, tel input)
  - Business Started (required, date picker)
  - TIN - Tax Identification Number (optional, text input)
  - Business Description (optional, textarea)
  - Business Logo (optional, image file upload - JPG, PNG, GIF)
  - Purpose of Request (required, textarea)
  - Supporting Documents (optional, multi-file upload)
- Submit button with warning yellow color
- Separate handling for business logo image file
- Dynamic file list with document organization

#### Upload System Features
- **Drag-and-Drop Support**: Users can drag files over upload area for visual feedback
- **Click to Browse**: Traditional file browser fallback
- **Multiple File Selection**: Select multiple files at once
- **File Type Validation**: Whitelist enforcement (PDF, DOC, DOCX, JPG, JPEG, PNG, GIF)
- **File Size Validation**: 5MB per file maximum limit
- **File List Display**: Shows all selected files with:
  - File icon (type-specific: PDF, Word, Image, Generic)
  - File name with truncation
  - File size in MB
  - Remove button for individual file deletion
- **Real-time Updates**: File list updates immediately on selection or removal
- **Visual Feedback**: Hover states and dragover highlighting

#### Confirmation Modal
Post-submission success modal displaying:
- Success checkmark icon (large green circle)
- "Request Submitted Successfully!" heading
- **Date Requested**: Formatted submission timestamp (e.g., "June 08, 2026 2:30 PM")
- **Reference No**: Unique request number (format: REQ-YYYYMMDDHHmmss-XXXX)
- **Confirmation Message**: "Your '[document type]' is pending to review by our officials. We will send you an email for updates."
- Static backdrop (prevents accidental dismissal)
- Auto-page refresh on close to show new request in table

#### Recent Requests Table
Comprehensive display of all resident requests:
- Columns:
  - Request Number (unique identifier, strongly formatted)
  - Type (color-coded badge - Info teal)
  - Date Requested (formatted timestamp: Mon DD, YYYY)
  - Status (color-coded badge):
    - Yellow: Pending
    - Blue: Processing
    - Green: Ready to Receive, Received
    - Red: Rejected
  - Actions: View button for detailed information
- Table hover effects for interactivity
- Empty state message with inbox icon when no requests exist
- Responsive design with horizontal scroll on mobile

#### Request Submission Backend API
**Endpoint**: `POST /api/requests/create.php`

**Processing**:
1. Session authentication check
2. Request type validation against whitelist
3. Document-specific field collection:
   - Barangay Clearance: Purpose only
   - Barangay Residency: Years, start date, purpose
   - Barangay Indigency: Income, household members, assistance type, purpose
   - Business Clearance: All business details, purpose
4. File upload handling:
   - Multipart form data processing
   - MIME type verification
   - File size validation (5MB max per file)
   - Secure unique filename generation (REQ-timestamp-randomhex.ext)
   - Storage in `/uploads/requests/` directory
5. Request number generation (REQ-YYYYMMDDHHmmss-XXXX format)
6. Database insertion with JSON storage:
   - All document data as JSON
   - All attachments as JSON array
   - Pending status default
7. Email notification sending (optional - non-blocking)
8. JSON response with success/error status

**Response Format**:
```json
{
  "success": true,
  "message": "Request submitted successfully",
  "data": {
    "request_number": "REQ-20260608143022-1234",
    "request_type": "Barangay Clearance",
    "date_requested": "June 08, 2026 2:30 PM"
  }
}
```

#### Email Notification System
- **Automatic Notifications**: Sent to resident's email on successful submission
- **Email Contents**:
  - Personalized greeting with resident name
  - Document type confirmation
  - Unique reference number
  - Submission date and time
  - Pending review message
  - Thank you message
- **HTML Formatted**: Professional email template with styling
- **Graceful Degradation**: Non-blocking (doesn't fail request if email fails)

#### Database Schema Updates
- **Requests Table Fields**:
  - `id`: Primary key, auto-increment
  - `user_id`: Foreign key to users table
  - `request_number`: VARCHAR(20), unique identifier (REQ-YYYYMMDDHHmmss-XXXX)
  - `request_type`: ENUM (Barangay Clearance, Barangay Residency, Barangay Indigency, Barangay Business Clearance)
  - `purpose`: VARCHAR(255), stored separately for quick access
  - `status`: ENUM (Pending, Approved, Processing, Ready to Receive, Received, Rejected), default 'Pending'
  - `document_data`: JSON column with all form fields
  - `attachments`: JSON array of file paths
  - `created_at`: TIMESTAMP default current_timestamp
  - `updated_at`: TIMESTAMP for tracking changes

**Sample document_data JSON**:
```json
{
  "purpose": "Employment requirement",
  "years_of_residency": "5",
  "date_started": "2021-06-08",
  "monthly_income": "25000",
  "household_members": "4",
  "assistance_type": "Medical Assistance",
  "business_name": "ABC Store",
  "business_address": "123 Main St, Barangay X",
  "contact_number": "09123456789",
  "business_started": "2020-01-15",
  "tin": "123456789",
  "business_description": "General merchandise store",
  "business_logo": "uploads/requests/REQ_logo_hexstring.png"
}
```

#### Admin Request Details Modal - Enhanced
Comprehensive view showing applicant and request information:

**Applicant Information Section**:
- Full Name (from residents table)
- Age
- Date of Birth (formatted: Mon DD, YYYY)
- Gender
- Civil Status
- House Number/Street/Purok
- Contact Number (mobile_number or phone)
- Email Address
- Valid ID (clickable link with preview)

**Request Details Section**:
- Request Number (unique identifier badge)
- Request Type (color-coded badge)
- Date Requested (formatted timestamp)
- Purpose of Request (full text)

**Document Information Section**:
- All document-specific fields from JSON
- Dynamic field names based on request type
- Formatted display for readability

**Supporting Documents Section** (NEW):
- Responsive 2-column grid of document cards
- Image previews for image files (JPG, PNG, GIF)
- Generic file icon for non-image files
- File name display with download button
- Hover effects for interactivity

**Status Management Section**:
- Current status display (bolded)
- Status update dropdown with all status options
- Update Status button
- Date Received display when applicable

#### Frontend Technology Stack
- **HTML/CSS**: Bootstrap 5 framework for responsive grid layout
- **JavaScript**: Vanilla JS for form handling and AJAX
- **File Handling**: FormData API for multipart uploads
- **AJAX**: Fetch API for async request submission
- **Modals**: Bootstrap 5 modal framework
- **Icons**: Font Awesome 6.4.0
- **Styling**: Custom CSS with responsive breakpoints

#### Design & Styling
- **Color Scheme**: 
  - Service cards: Individual gradients (blue, green, cyan, yellow)
  - Status badges: Standard Bootstrap colors
  - Buttons: Matched to card colors for visual cohesion
- **Responsive Layout**:
  - Desktop: 4-column service card grid
  - Tablet (768px): 2-column grid
  - Mobile (<576px): 1-column stack
- **Animations**:
  - Service card hover lift with shadow
  - File upload drag-over highlight
  - Modal fade-in transitions
  - Button hover state changes
- **Typography**: Arial font family throughout
- **Spacing**: Bootstrap standard padding and margins
- **Accessibility**: Proper labels, required indicators, focus states

#### Security Features
- **Session Authentication**: User must be logged in to access page
- **Input Validation**: Client-side and server-side validation
- **File Validation**: 
  - MIME type verification
  - Size limits enforcement
  - File type whitelist
- **SQL Injection Prevention**: Prepared statements for all DB queries
- **XSS Prevention**: HTML escaping with htmlspecialchars()
- **User Data Isolation**: Residents can only access their own requests
- **Error Suppression**: No sensitive error details in responses

#### Files Created/Modified
- **NEW**: `api/requests/create.php` - Complete API endpoint with error handling
- **UPDATED**: `public/public-pages/requests.php` - Full page rewrite with modals and database integration
- **UPDATED**: `assets/js/public/requests.js` - Comprehensive JavaScript with file upload, form handling, AJAX
- **UPDATED**: `assets/css/public/requests.css` - Complete styling with upload areas, modals, responsive design
- **UPDATED**: `admin/main-pages/requests.php` - Enhanced with document preview section
- **UPDATED**: `assets/css/admin/requests.css` - Document preview card styling

#### Testing Checklist
- ✓ Create request for each document type
- ✓ Test drag-drop file uploads
- ✓ Test conditional "Others" field in indigency
- ✓ Test file size and type validation
- ✓ Test form submission error handling
- ✓ Verify request appears in resident table
- ✓ Test search functionality
- ✓ View request in admin modal
- ✓ Test document previews in admin
- ✓ Update request status as admin
- ✓ Test mobile responsiveness
- ✓ Verify email notifications sent

#### Performance Optimizations
- Output buffering to prevent HTML in JSON responses
- Error suppression to prevent PHP notices
- Graceful email notification handling (non-blocking)
- Efficient file upload processing
- Optimized database queries with prepared statements
- CSS transforms for GPU acceleration

---

## What's New in V2.1

### Admin Dashboard - Comprehensive Executive Overview Redesign

#### Overview
A complete redesign of the admin dashboard featuring an executive dashboard with 6 key metric cards, demographic statistics, recent activity tracking, notification summaries, and a calendar widget. The dashboard provides administrators with comprehensive visibility into barangay operations with real-time data.

#### Statistics Summary Cards (6 Key Metrics)
- **Total Residents**: Count of verified resident accounts
  - Icon: Users icon
  - Color: Primary Blue
  - Data Source: Verified users joined with residents table
  
- **Registered Voters**: Residents with voter status set to 'Yes'
  - Icon: Vote-yea icon
  - Color: Success Green
  - Data Source: Query residents table for voter_status = 'Yes'

- **Senior Citizens (60+)**: Residents aged 60 and above
  - Icon: Person-cane icon
  - Color: Warning Yellow
  - Data Source: Query residents WHERE age >= 60

- **Persons with Disabilities**: Residents with disabilities recorded
  - Icon: Wheelchair icon
  - Color: Danger Red
  - Data Source: Query residents WHERE disabilities is NOT NULL

- **Pending Document Requests**: Active requests awaiting processing
  - Icon: File-invoice icon
  - Color: Info Teal
  - Data Source: Query requests WHERE status = 'Pending'

- **Pending Cases**: Reports in pending or ongoing status
  - Icon: Exclamation-circle icon
  - Color: Secondary Gray
  - Data Source: Query reports WHERE status IN ('Pending', 'Ongoing')

**Card Features**:
- Responsive grid layout (3 columns desktop, 2 columns tablet, 1 column mobile)
- Flex layout with icon and content side-by-side
- Hover effects with elevation and shadow animations
- Color-coded left border indicators
- Large readable numbers with formatted values (e.g., "1,250")
- Uppercase label text with letter-spacing for visual hierarchy

#### Residents Statistics Section
Two-column responsive layout displaying population demographics:

**Gender Distribution**:
- Progress bar chart showing Male, Female, Other populations
- Displays count and percentage for each gender
- Color-coded bars (primary blue)
- Gender-specific icons (Mars for Male, Venus for Female)
- Responsive stack on mobile

**Population by Age Groups**:
- Six age brackets: 0-12, 13-19, 20-35, 36-50, 51-60, 60+
- Progress bars with percentages and counts
- Info teal color (#17a2b8) for age bars
- CASE statement SQL for age grouping
- Sortable by age group in database

**Features**:
- Card-based design with headers and subtitles
- Icons for visual clarity
- Scrollable containers for long lists
- Empty state messages when no data available
- Responsive columns (full-width on mobile, 50% on tablet, 50% on desktop)

#### Recent Activities Timeline
Chronological feed combining admin actions and resident activities:

**Activity Sources**:
- Admin logs from `admin_logs` table
- Recent resident requests from `requests` table
- Combined and sorted by timestamp (newest first)

**Timeline Features**:
- Vertical timeline with gradient line indicator
- Activity markers (circular dots with box shadow)
- Actor information (Admin vs Resident indicator with icon)
- Action description text
- Formatted timestamp (Mon DD, YYYY HH:ii)
- Limited to 10 most recent activities
- Hover effects for better interactivity

**Activity Display**:
- Admin activities show shield icon with admin username
- Resident activities show user icon with resident username
- Clear action descriptions (e.g., "posted an announcement", "submitted a new request")
- Professional timeline styling matching admin design patterns

#### Notifications Summary Section
Two-column responsive layout for critical alerts:

**Left Column - Notification Cards** (4 cards):
1. **User Verification Pending**
   - Icon: User-check (warning orange background)
   - Count: Number of unverified users
   
2. **Pending Document Requests**
   - Icon: File-alt (info teal background)
   - Count: Total pending requests
   
3. **Upcoming Events (Next 7 Days)**
   - Icon: Calendar-check (primary blue background)
   - Count: Scheduled announcements within 7 days
   
4. **New Reports**
   - Icon: Exclamation-triangle (danger red background)
   - Count: Pending reports count

**Right Column - Pending Requests by Type**:
- Breakdown of pending requests grouped by request type
- Each request type displays:
  - File icon with request type name
  - Warning badge with count
  - Individual list items for each request type
- Empty state when all requests processed
- Scrollable container if many request types

**Card Features**:
- Icon boxes with colored backgrounds
- Large readable counts in primary blue
- Hover effects for interactivity
- Badge-styled counts with warning color
- Request type icons and labels

#### Calendar Widget - Scheduled Events
Displays upcoming scheduled events and announcements:

**Event Display**:
- Event list with 10 most recent scheduled/active announcements
- Each event shows:
  - Date card (day/month in blue gradient box)
  - Event title
  - Time with clock icon
  - Priority badge (normal/important/urgent)
  - Status badge (scheduled/active)

**Event Information**:
- Ordered by scheduled_at date ascending
- Filters only status IN ('scheduled', 'active')
- Large formatted day number, small uppercase month abbreviation
- Responsive layout with date on left, content on right

**Styling**:
- Date box: Linear gradient blue to dark blue
- Priority badges: Color-coded (gray/yellow/red)
- Status badges: Success green or info teal
- Hover effects with background color change
- Responsive stack on mobile

**Empty State**:
- Calendar icon with "No scheduled events" message when empty

#### Design & Styling System

**Color Scheme**:
- Primary: #007bff (Blue)
- Success: #28a745 (Green)
- Warning: #ffc107 (Yellow)
- Danger: #dc3545 (Red)
- Info: #17a2b8 (Teal)
- Secondary: #6c757d (Gray)

**Layout**:
- Bootstrap 5 responsive grid system
- Container-fluid with max-width 1400px
- Responsive breakpoints: 1400px desktop, 768px tablet, 576px mobile
- Padding and margins following Bootstrap standards
- Flexbox layouts for alignment

**Typography**:
- Font: Arial, sans-serif throughout
- Welcome title: 28px, font-weight 600
- Section titles: 18px with bottom border
- Stat values: 28px bold
- Regular text: 14px, 13px for secondary text

**Effects & Animations**:
- Hover elevation with translateY(-3px)
- Shadow transitions (0 2px 4px to 0 4px 12px)
- Smooth CSS transitions (0.3s ease)
- Progress bar animations (0.6s ease)
- Timeline gradient background

**Responsive Design**:
- Stat cards stack vertically on mobile (1 column)
- Statistics section: 2 columns tablet, 1 column mobile
- Activity timeline: Adjusted marker positioning on mobile
- Notification cards: Stack vertically on mobile
- All containers full-width on mobile with appropriate padding

#### Database Queries

**Statistics Queries**:
- COUNT() aggregations for all metrics
- LEFT JOIN residents with users table for verified count
- CASE statements for age grouping
- GROUP BY for gender/type breakdowns
- Prepared statements for SQL injection prevention

**Activity Queries**:
- SELECT from admin_logs with user_id JOIN
- SELECT from requests with user_id JOIN
- Combined and sorted results
- Limited to 10 results

**Notification Queries**:
- COUNT WHERE is_verified = 0 for pending users
- GROUP BY request_type for pending breakdown
- Date arithmetic for 7-day upcoming events
- Status filtering for pending reports

#### Backend Implementation

**PHP Processing**:
- Session authentication check
- Admin user retrieval
- Multiple aggregation queries executed sequentially
- Array sorting with usort() for activity timeline
- Data formatting for display

**Data Validation**:
- Null coalescing operators for safe defaults
- Empty array checks before iteration
- Safe output with htmlspecialchars() for HTML display
- Proper number formatting with number_format()

#### Frontend Features

**Bootstrap Components**:
- Grid system for responsive layout
- Progress bars for demographic visualization
- Badge components for status indicators
- Responsive utilities (d-flex, gap, etc.)

**Interactive Elements**:
- Hover states on all cards
- Responsive font sizing
- Proper spacing and alignment
- Accessible color contrasts

#### Files Modified
- `admin/main-pages/dashboard.php`: Complete rewrite with new sections and database queries
- `assets/css/admin/dashboard.css`: Comprehensive styling with responsive design and animations

#### Performance Considerations
- Optimized database queries with prepared statements
- Limited result sets (10 for activities, events)
- Efficient aggregation using SQL GROUP BY
- CSS GPU acceleration with transform/opacity
- Minimal JavaScript for fast load times

#### Future Enhancements
- Real-time data refresh using AJAX
- Export statistics to PDF/Excel
- Custom date range filtering
- Advanced search within activities
- Notification preferences/alerts
- Dashboard widget customization
- Data visualization charts (Chart.js integration)
- Predicted trends and analytics

---

## What's New in V2.0

### Resident Request Page - Complete 3-Step Modal Implementation

#### Overview
A comprehensive multi-step request system for residents to submit applications for various barangay documents. The implementation includes a 3-step modal workflow, dynamic form handling, and enhanced admin interface for request management.

#### Step 1: Document Type Selection
- **Interactive Document Selection Interface**:
  - Four document type options with descriptive icons:
    - **Barangay Clearance**: Certificate of good moral character (green icon)
    - **Barangay Residency**: Proof of residency in barangay (blue icon)
    - **Barangay Indigency**: Certificate of indigency (orange icon)
    - **Business Clearance**: Certificate for business operations (purple icon)
  - Hoverable card design with smooth animations
  - Visual feedback on selection
  - Responsive 2-column grid layout

#### Step 2: Document-Specific Form
Dynamic form fields that change based on selected document type:

**Barangay Clearance**:
- Purpose of Request (required, text input)

**Barangay Residency**:
- Years of Residency (required, number input)
- Date Started Living in Barangay (required, date picker)
- Purpose of Request (required, text input)

**Barangay Indigency**:
- Monthly Income (required, decimal number)
- Number of Household Members (required, integer)
- Purpose of Request (required, dropdown selector):
  - Medical Assistance
  - Educational Assistance
  - Financial Assistance
  - Burial Assistance
  - Others (shows additional text field when selected)
- Supporting Documents (optional, multi-file upload - PDF, JPG, PNG)
- Images (optional, multi-file upload - JPG, PNG)

**Business Clearance**:
- Business Name (required, text input)
- Business Logo (optional, image file - JPG, PNG, WebP)
- Business Description (optional, textarea)
- Business Full Address (required, text input)
- Contact Number (required, tel input)
- TIN - Tax Identification Number (optional, text)
- Business Started (required, date picker)
- Purpose of Request (required, text input)

**Form Features**:
- Back button to return to document type selection
- Real-time form validation
- Required field indicators
- Form reset on back navigation
- Conditional field visibility (e.g., "Others reason" for indigency)
- File upload validation (5MB limit per file)

#### Step 3: Confirmation Screen
After successful submission, displays:
- Success message with checkmark icon
- **Reference Number**: Auto-generated format (REQ-YYYYMMDD-XXXX)
- **Date Requested**: Formatted submission timestamp
- **Confirmation Message**: "Your '[document type]' is pending to review by our officials, We will send you an email for updates."
- Close button that reloads the page to show new request in list
- Email notification promise

#### Request Management Features
- **Search & Filter**:
  - Search by request number or document type
  - Real-time filtering as user types
  - Clear search button
  
- **Request History Table**:
  - Columns: Request #, Document Type (badge), Submitted Date, Status (color-coded), Actions
  - Status badges:
    - Yellow: Pending
    - Blue: Approved/Processing
    - Green: Ready to Receive/Received
    - Red: Rejected
  - View button for detailed information
  - Pagination (10 requests per page)

- **Request Details Modal** (Resident View):
  - Document Type and Status
  - Submission Date
  - Purpose of Request
  - All document-specific information
  - Formatted display of stored data

#### Admin Request Details Modal - Enhanced
Comprehensive view showing:

**Applicant Information Section**:
- Full Name (from residents table)
- Age
- Date of Birth (formatted)
- Gender
- Civil Status
- House Number/Street/Purok
- Contact Number
- Email Address
- Valid ID (clickable link to view uploaded image)

**Request Details Section**:
- Request Number (unique identifier)
- Request Type (with badge)
- Date Requested (formatted timestamp)
- Purpose of Request

**Document Information Section**:
- All document-specific fields stored in JSON
- Formatted display for all document types
- Dynamic field names based on request type

**Status Management Section**:
- Current status display
- Status dropdown selector with options:
  - Pending
  - Approved
  - Processing
  - Ready to Receive
  - Received
  - Rejected
- Date Received display (when applicable)
- Update Status button

#### Backend API Implementation
**POST `/api/requests/create.php`**:
- Authentication verification (residents only)
- Request type validation
- Document-specific field validation
- File upload handling:
  - MIME type verification
  - Size validation (5MB max per file)
  - Secure file naming with uniqid() and timestamp
  - Storage in `/uploads/requests/` directory
- Unique request number generation
- Document data stored as JSON for flexibility
- JSON response with success/error status
- Returns: request_number and created_at timestamp

#### Database Schema Updates
- Updated `requests` table:
  - Added `document_data` JSON column for flexible document-specific data storage
  - Updated `request_number` to VARCHAR(20) for new format
  - Maintains user_id for data isolation
  - Created_at and updated_at timestamps

**Sample document_data JSON**:
```json
{
  "years_residency": "5",
  "date_started_living": "2021-06-02",
  "purpose": "Employment requirement",
  "monthly_income": "25000",
  "household_members": "4"
}
```

#### File Handling
- **Upload Directory**: `/uploads/requests/`
- **File Type Support**:
  - Documents: PDF, JPG, PNG
  - Images: JPG, PNG, WebP
- **File Size Limit**: 5MB per file
- **Naming Convention**: `{type}_{uniqid}_{timestamp}.{ext}`
- **Security**:
  - MIME type verification
  - Size validation before storage
  - Unique filenames to prevent conflicts
  - Separate directory for request uploads

#### Frontend Technology
- **Modal Framework**: Bootstrap 5
- **Animations**: CSS transitions and keyframes
- **Form Handling**: Fetch API for async submission
- **File Upload**: FormData API for multipart/form-data
- **State Management**: JavaScript variables tracking current step
- **Validation**: Client-side and server-side validation

#### Design & UX
- **Responsive Layout**: Mobile-first design
- **Animations**: 
  - Fade-in for step transitions
  - Slide-in for form sections
  - Smooth hover effects on cards
- **Color Scheme**:
  - Green (#4CAF50): Barangay Clearance
  - Blue (#2196F3): Barangay Residency
  - Orange (#FF9800): Barangay Indigency
  - Purple (#9C27B0): Business Clearance
- **Typography**: Arial font throughout for consistency
- **Icons**: Font Awesome 6.4.0 for visual indicators
- **Bootstrap 5**: Grid system and components for responsiveness

#### Security Features
- Session authentication check
- User role verification (residents only)
- Server-side validation of all inputs
- Prepared SQL statements (SQL injection prevention)
- File type and size validation
- HTML sanitization with htmlspecialchars()
- User data isolation (can only access own requests)
- MIME type verification for uploads

#### Files Created/Modified
- **NEW**: `api/requests/create.php` - Request creation API endpoint
- **NEW**: `uploads/requests/` - Directory for request uploads
- **UPDATED**: `public/public-pages/requests.php` - Complete rewrite with 3-step modal
- **UPDATED**: `assets/js/public/requests.js` - Modal logic and form handling
- **UPDATED**: `assets/css/public/requests.css` - Enhanced modal and form styling
- **UPDATED**: `admin/main-pages/requests.php` - Enhanced details modal with applicant info
- **UPDATED**: `sql/safebrgy_schema.sql` - Added document_data column

#### User Workflows

**Resident Request Workflow**:
1. Navigate to "My Requests" page
2. Click "Request Now" button
3. Select desired document type
4. Fill in document-specific form fields
5. Submit the form
6. Receive confirmation with reference number
7. View request in history table
8. Receive email updates on request status

**Admin Request Review Workflow**:
1. Navigate to "Requests" page in admin panel
2. Search or filter requests
3. Click "View" button on target request
4. Review applicant information and request details
5. Update request status using dropdown
6. Click "Update Status" button
7. Status change is saved and timestamped

#### Testing Checklist
- ✓ Create Barangay Clearance request
- ✓ Create Barangay Residency request with date picker
- ✓ Create Barangay Indigency request with "Others" option
- ✓ Upload files for Indigency request
- ✓ Create Business Clearance with logo upload
- ✓ Verify request appears in resident list
- ✓ Search requests by number
- ✓ View request details as resident
- ✓ View request in admin with full applicant info
- ✓ Update request status as admin
- ✓ Test mobile responsiveness
- ✓ Verify file upload security

#### Performance Considerations
- Optimized queries with prepared statements
- JSON storage for flexible, scalable document data
- Pagination for large request lists (10 per page)
- Lazy-loading of document details via modal
- Efficient file upload handling with progress feedback
- CSS animations using GPU acceleration (transform/opacity)

#### Future Enhancements
- Real-time email notifications on status updates
- Document preview gallery in admin modal
- Bulk request operations (status update, export)
- Export requests to PDF/Excel reports
- Request templates for common purposes
- Advanced filtering (date range, document type, status)
- Document recommendation engine
- WebSocket integration for live status updates
- Mobile app API integration
- SMS notifications for request updates

---

## What's New in V1.9

### Resident Report Page - Complete Implementation
- **Status Tracker Section**:
  - Four responsive status tracker cards displaying report counts by status
  - Real-time statistics from database:
    - **Pending**: Yellow/warning badge for pending reports
    - **Ongoing**: Teal/info badge for reports under investigation
    - **Resolved**: Green/success badge for completed reports
    - **Dismissed**: Red/danger badge for dismissed reports
  - Large, readable metrics display with Font Awesome icons
  - Hover effects and smooth transitions for better interactivity
  - Responsive grid layout (4 columns on desktop, 2 on tablet, 1 on mobile)

- **Search & Filter Functionality**:
  - Search bar for finding reports by case number or title
  - Real-time search filtering as user types
  - Status dropdown filter (All Status, Pending, Ongoing, Resolved, Dismissed)
  - Combined search + filter functionality
  - Clean, modern search box with search icon
  - Integrated filter controls in single section

- **Create Report Modal**:
  - Accessible via "Create New Report" button in page header
  - Form fields:
    - **Report Type Picker**: Select from Incident, Lost Property, or Blotter
    - **Title**: Required input field for report title
    - **Description**: Required textarea for detailed description
    - **Location**: Optional field for report location
    - **Picture Upload Area**: Marked as "Recommended" with visual badge
  - Advanced file upload features:
    - Drag-and-drop functionality with visual feedback
    - Click to browse file explorer
    - Image preview with remove button capability
    - Supported formats: PNG, JPG, GIF, WebP
    - File size validation (5MB maximum)
    - Real-time preview display after selection
  - Submit button with icon for clear action intent
  - Cancel button to close without submitting

- **Reports Table**:
  - Comprehensive table displaying all resident reports
  - Columns:
    - **Case No.**: Auto-generated format (CASE-YYYYMMDD-####)
    - **Report Type**: Color-coded badge (Incident, Lost Property, Blotter)
    - **Title**: Report title text
    - **Date**: Formatted submission date (Mon DD, YYYY)
    - **Status**: Color-coded status badge matching tracker cards
    - **Actions**: View button to open report details
  - Table hover effects for better UX
  - Empty state message with icon when no reports exist
  - Responsive table with horizontal scroll on mobile devices

- **View Report Modal**:
  - Opens when clicking the "View" button on any report
  - Displays complete report information:
    - Case number
    - Report type with badge
    - Full title and description
    - Location (if provided)
    - Status with color-coded badge
    - Date submitted (formatted)
    - Uploaded images/attachments (if any)
  - Large preview images with proper aspect ratio
  - Professional modal styling with consistent design
  - Close button to dismiss modal

- **Database Integration**:
  - Fetches reports from `reports` table filtered by user_id
  - Calculates status statistics via grouped COUNT queries
  - Auto-generates unique case numbers using date + random identifier
  - Stores attachments as JSON array
  - Optimized queries using prepared statements
  - User data isolation (residents can only view their own reports)

- **File Upload Processing**:
  - Backend validation for file type and size
  - Creates `uploads/reports/` directory automatically if needed
  - Stores files with unique filename (uniqid + timestamp + extension)
  - Generates JSON array of attachment paths
  - Returns success/error responses to frontend

- **Design & Styling**:
  - Consistent with resident dashboard design language
  - Bootstrap 5 framework for responsive layout
  - Color-coded status indicators matching dashboard theme
  - Blue gradient buttons (#007bff to #0056b3) with hover effects
  - Professional card-based design with shadows
  - Smooth animations and transitions throughout
  - Clean typography and proper spacing
  - Font Awesome icons for visual indicators
  - Fully responsive (desktop 1400px, tablet 576-768px, mobile <576px)

- **Security Features**:
  - Session authentication check for resident access only
  - User role verification
  - File type validation (image files only)
  - File size validation (5MB maximum)
  - MIME type verification via mime_content_type()
  - User data isolation (cannot view other users' reports)
  - SQL injection prevention with prepared statements
  - Input sanitization with htmlspecialchars()

- **Frontend Enhancements**:
  - JavaScript event listeners for all interactive elements
  - Drag-and-drop file upload with visual feedback
  - Real-time search and filter functionality
  - Form submission via Fetch API (AJAX)
  - Dynamic modal content population
  - File preview generation with FileReader API
  - Error handling and user alerts
  - Bootstrap 5 modal framework for reliable dialogs

- **API Endpoints Created**:
  - **`api/reports/create.php`**: Handles report creation and file upload
    - Validates input parameters
    - Processes file uploads with comprehensive checks
    - Generates unique case numbers
    - Returns JSON response with success/error status
  - **`api/reports/get.php`**: Retrieves individual report details
    - Validates user ownership of report
    - Parses attachment JSON
    - Returns complete report data as JSON

- **Files Created/Modified**:
  - `public/public-pages/reports.php`: Complete rewrite with database queries, status tracking, and modal dialogs
  - `assets/js/public/reports.js`: Comprehensive JavaScript for search, filter, file upload, and AJAX functionality
  - `assets/css/public/reports.css`: Complete styling with responsive design, animations, and theme consistency
  - `api/reports/create.php`: New API endpoint for report creation with file handling
  - `api/reports/get.php`: New API endpoint for retrieving report details

---

# SafeBrgy - Official V1.8

## What's New in V1.8

### Resident Announcement Page - Complete Implementation
- **Search Functionality**:
  - Search announcements by title or keyword content
  - Real-time filtering through form submission
  - Search term persistence in the input field for user convenience
  - Highlights relevant results based on search query

- **Sort & Filter Options**:
  - Sort by **Newest** (default - displays pinned announcements first, then by publication date)
  - Sort by **Oldest** (chronological order for historical reference)
  - Reset button to clear search and return to default view
  - Persistent sort state in dropdown for better UX

- **Announcement Preview Cards**:
  - Display active announcements only (status = 'active' and archived = 0)
  - Card layout with title, priority badge, publication date, and excerpt
  - Priority badges for urgent (red), important (orange), normal announcements
  - 150-character excerpt showing announcement preview
  - Mark as Noted button on each card for quick acknowledgment
  - Smooth hover animations with shadow effects
  - Responsive 2-column grid layout (1 column on mobile)

- **Full Details Modal**:
  - Click "Read More" to open detailed modal view
  - Modal displays:
    - Complete announcement title and publication date with timestamp
    - Full announcement body content with preserved line breaks
    - Priority level indicator
    - Author information (optional)
    - Attachment download link if available
    - Close and Mark as Noted buttons
  - Professional modal styling consistent with admin pages
  - Bootstrap 5 modal framework for reliable functionality

- **Mark as Noted Feature**:
  - Available from both card and modal views
  - Button with checkmark icon provides visual feedback
  - On click: Shows "Noted" with checkmark icon
  - Sends acknowledgment to backend API
  - Auto-resets after 2 seconds for next action
  - Provides user confirmation without page reload
  - Optional: Can be extended to store user read history

- **Database Integration**:
  - Fetches announcements from `announcements` table
  - Filters only active, non-archived announcements (status = 'active' AND archived = 0)
  - Joins with users table to display author information
  - Supports search across title and body content using LIKE queries
  - Respects priority field for badge styling
  - Handles attachments stored as JSON with file information
  - Optimized queries for performance with prepared statements

- **Design & Styling**:
  - Consistent with admin announcement page layout and design
  - Bootstrap 5 grid system for responsive layout
  - Card-based design with professional shadows and hover effects
  - Color-coded priority badges (danger for urgent, warning for important)
  - Search/filter card with clean form layout
  - Professional typography and spacing
  - Font Awesome icons for visual indicators (calendar, check, etc.)
  - Fully responsive design (mobile, tablet, desktop)
  - Empty state message with icon when no announcements found

- **Frontend Enhancements**:
  - JavaScript event listeners for Mark as Noted buttons
  - Fetch API for async backend communication
  - Dynamic button state changes with visual feedback
  - Modal animations and transitions
  - Form submission handling with GET parameters
  - Graceful error handling with user alerts

- **Backend API**:
  - New endpoint: `api/announcement-noted.php`
  - Receives POST requests with announcement_id parameter
  - Validates user authentication and input
  - Logs announcement acknowledgments (foundation for future read tracking)
  - Returns JSON response with success status

- **Files Created/Modified**:
  - `public/public-pages/announcement.php`: Complete rewrite with DB queries, search, filter, sort, and modals
  - `assets/js/public/announcement.js`: Enhanced with Mark as Noted functionality and form handling
  - `assets/css/public/announcement.css`: Comprehensive styling for cards, modals, badges, and animations
  - `api/announcement-noted.php`: New API endpoint for marking announcements as noted

---

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

- **V2.1** (Latest - June 3, 2026): Comprehensive Admin Dashboard Redesign with executive overview, demographics, and activity tracking.
  - New Features: 
    - Statistics Summary Cards (6 key metrics: Total Residents, Registered Voters, Senior Citizens, Persons with Disabilities, Pending Requests, Pending Cases)
    - Residents Statistics (Gender distribution and age group breakdown with progress bars)
    - Recent Activities Timeline (Admin logs and resident requests combined in chronological order)
    - Notifications Summary (User verification, pending requests by type, upcoming events, new reports)
    - Calendar Widget (Scheduled events and announcements with date, time, priority, and status)
  - Design: Color-coded cards, responsive Bootstrap 5 grid, responsive animations, mobile-friendly
  - Files Modified: `admin/main-pages/dashboard.php`, `assets/css/admin/dashboard.css`
  - Database Integration: Live queries for all statistics and aggregations

- **V2.0** (June 2, 2026): Complete resident request system with 3-step modal workflow for submitting barangay documents. Enhanced admin interface with comprehensive applicant information and request management.
  - New Features: 3-step request modal, dynamic forms by document type, file uploads, request tracking, enhanced admin details modal
  - Files Added: `api/requests/create.php`, `uploads/requests/` directory
  - Files Updated: `public/public-pages/requests.php`, `assets/js/public/requests.js`, `assets/css/public/requests.css`, `admin/main-pages/requests.php`, `sql/safebrgy_schema.sql`

- **V1.9**: Resident Report Page with complete implementation including status tracking, search/filter, create report modal with file uploads, and view report details.

- **V1.8**: Resident Announcement Page with search, sort, filter, announcement cards, full details modal, and mark as noted feature.

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

*SafeBrgy V2.0 - Empowering communities through technology.*