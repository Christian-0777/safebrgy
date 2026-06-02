# SafeBrgy - Official V1.9

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

- **V2.0** (Latest - June 2, 2026): Complete resident request system with 3-step modal workflow for submitting barangay documents. Enhanced admin interface with comprehensive applicant information and request management.
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