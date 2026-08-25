# Admin Requests Page - Update Documentation

## Overview
The Admin Requests page has been completely redesigned to match the Admin Announcements page layout and functionality. The page now includes advanced search, filtering, statistics cards, and a comprehensive modal for viewing and updating request statuses.

## Changes Made

### 1. Database Schema Updates

#### New Columns Added to `requests` table:
- **request_number** (VARCHAR(10)): Unique 4-digit identifier for each request (format: R-0001, R-0002, etc.)
- **date_received** (DATETIME): Timestamp for when the document/service was received by the resident

#### Updated Status Enum:
New status values added:
- `Pending` (default)
- `Approved`
- `Processing`
- `Ready to Receive`
- `Received` (new)
- `Rejected`

**Migration File**: `/sql/migrations/001_update_requests_table.sql`

To apply migrations to existing database:
```sql
-- Run the migration file
source /path/to/sql/migrations/001_update_requests_table.sql;
```

### 2. PHP Backend Changes (`admin/main-pages/requests.php`)

#### New Features:
- **Search Functionality**: Search requests by:
  - Request number (R-XXXX)
  - Resident name (first/last name)
  - Email address
  - Username

- **Filtering**: Filter requests by status:
  - All Status
  - Pending
  - Approved
  - Processing
  - Ready to Receive
  - Received
  - Rejected

- **Sorting**: Sort requests by:
  - Newest first (default)
  - Oldest first

- **Statistics Cards**: Display counts for:
  - Total Requests
  - Pending Requests
  - Processing Requests
  - Received Requests

#### AJAX Functionality:
- `update_status` action: Updates request status and automatically sets `date_received` when status is set to "Received"

### 3. Frontend Changes

#### HTML Structure (`admin/main-pages/requests.php`):
- **Page Header**: Descriptive title and subtitle
- **Statistics Cards**: Display key metrics at the top
- **Search & Filter Panel**: Form for searching and filtering requests
- **Requests Table**: Updated columns:
  - Request # (unique identifier)
  - Resident Name (with email in small text)
  - Request Type (badge)
  - Submitted (date only)
  - Status (color-coded badge)
  - Date Received (automatically set when received)
  - Actions (View button)

- **Modal for Each Request**: 
  - Displays resident information
  - Shows request details (type, purpose, location, submitted date)
  - Status update dropdown with all available statuses
  - Update Status button to save changes
  - Shows date received if request has been received

#### Table Columns Structure:
```
Request #    | Resident Name | Request Type | Submitted | Status | Date Received | Actions
R-0001       | John Doe      | Clearance    | Jun 1, 26 | Pending| N/A           | View
```

### 4. CSS Styling (`assets/css/admin/requests.css`)

Completely redesigned to match announcement page styling:
- **Statistics Cards**: 
  - White background with left border accent
  - Hover effect with elevation and transform
  - Large value display with descriptive label
  
- **Search/Filter Panel**: 
  - Card-based layout with form controls
  - Grouped input fields with responsive grid

- **Table**: 
  - Bootstrap table with hover effects
  - Dark header with white text
  - Responsive design for mobile devices

- **Modal**: 
  - Clean header with background
  - Well-organized information sections
  - Status selector dropdown

- **Badges**: 
  - Color-coded status indicators:
    - Pending: Warning (yellow)
    - Approved: Info (blue)
    - Processing: Primary (blue)
    - Ready to Receive: Success (green)
    - Received: Success (green)
    - Rejected: Danger (red)

### 5. JavaScript Functionality (`assets/js/admin/requests.js`)

#### Features:
- **Status Update Handler**:
  - Captures status selection from dropdown
  - Sends AJAX request to update status
  - Reloads page on success
  - Shows error message on failure
  - Validates that a status is selected before updating

- **Modal Interaction**:
  - Each request has its own modal with ID: `viewRequestModal{requestId}`
  - Status dropdown updates on click
  - Update button is disabled until a status is selected

## Usage Instructions

### For Admin Users:

1. **Viewing Requests**:
   - Navigate to **Requests** in the sidebar
   - See statistics at the top of the page

2. **Searching for Requests**:
   - Enter search term in the search box (name, email, or request number)
   - Click "Search" button
   - Click "Reset" to clear filters

3. **Filtering Requests**:
   - Select status from the "Filter by Status" dropdown
   - Choose "Newest" or "Oldest" from the sort dropdown
   - Results update automatically

4. **Viewing Request Details**:
   - Click the "View" button on any request row
   - Modal opens showing:
     - Resident information
     - Request details
     - Current status

5. **Updating Request Status**:
   - In the modal, select a new status from the dropdown
   - Click "Update Status" button
   - Status automatically saves and page refreshes
   - If status changed to "Received", the system automatically records the date

## Technical Details

### Request Number Generation:
Request numbers are generated in the format `R-XXXX` where XXXX is a 4-digit padded ID.

Example generation SQL:
```sql
UPDATE requests SET request_number = CONCAT('R-', LPAD(id, 4, '0')) WHERE request_number IS NULL;
```

### Status Workflow:
```
Pending → Approved → Processing → Ready to Receive → Received
  ↓                    ↓
  └──────→ Rejected
```

### Automatic Date Recording:
When a request status is changed to "Received", the `date_received` field is automatically set to the current date and time.

## File Structure

```
admin/main-pages/
  requests.php                    (Main page)

assets/css/admin/
  requests.css                    (Styling)

assets/js/admin/
  requests.js                     (JavaScript functionality)

sql/
  safebrgy_schema.sql             (Updated schema)
  migrations/
    001_update_requests_table.sql (Migration for existing DBs)
```

## Dependencies

- Bootstrap 5.3.0 (for layout and modals)
- Font Awesome 6.4.0 (for icons)
- Shared styles from `/assets/css/shared/`
- PHP 7.4+ with PDO

## Future Enhancements

Potential improvements for future versions:
1. Bulk status updates
2. Email notifications to residents when status changes
3. PDF export of request details
4. Request tracking timeline/history
5. Attachment viewing in modal
6. Print request details
7. Scheduled status changes
8. Request assignment to specific admins
