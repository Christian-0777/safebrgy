# Guest User Endpoint Implementation - Todo List

## Database
- [x] Analyze existing database schema (guest_reports table already exists with required fields)

## Backend (PHP)
- [x] Create database connection file (config/db.php)
- [x] Create API endpoints:
  - [x] GET /api/announcements - Get last 2 announcements
  - [x] GET /api/reports/feed - Get reports for feed tab (lost property reports)
  - [x] POST /api/reports/submit - Submit new guest report
  - [x] GET /api/reports/search - Search report by case number
- [x] Create helper functions for case number generation
- [x] Handle file uploads for pictures

## Frontend
- [x] Create main layout (index.php) with responsive Bootstrap 5
- [x] Create announcement page with modal popup
- [x] Create reports page with 3 tabs:
  - [x] Reports Feed Tab
  - [x] Submit Report Tab (with all form fields)
  - [x] Report Search Tab
- [x] Implement modal for submission confirmation with copy to clipboard
- [x] Implement modal for search results
- [x] Add responsive CSS for mobile and desktop
- [x] Add JavaScript for interactivity (tabs, modals, form validation, AJAX)

## Testing
- [x] Test all pages on mobile and desktop
- [x] Test form submission
- [x] Test search functionality
- [x] Test modal popups
- [x] Verify responsive design
