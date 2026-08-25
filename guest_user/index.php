<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeBrgy - Guest Portal</title>
    <link rel="icon" type="image/png" href="/safebrgy/assets/img/seal.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/safebrgy/assets/css/shared/colors.css">
    <link rel="stylesheet" href="/safebrgy/assets/css/shared/layout.css">
    <link rel="stylesheet" href="/safebrgy/assets/css/shared/shared-header.css">
    <link rel="stylesheet" href="/safebrgy/assets/css/shared/shared_sidebar.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="header-left">
            <button type="button" class="sidebar-toggle" id="guestSidebarToggle" aria-label="Open navigation" aria-expanded="false" aria-controls="guestSidebar"><i class="bi bi-list"></i></button>
            <a href="/safebrgy/guest/" class="header-logo">
                <img src="/safebrgy/assets/img/seal.png" alt="SafeBrgy Logo" class="logo-image">
                <span>SafeBrgy</span>
            </a>
        </div>
        <div class="header-right">
            <a href="/safebrgy/login" class="btn btn-primary btn-sm guest-login-link"><i class="bi bi-box-arrow-in-right me-1"></i> Login / Register</a>
        </div>
    </header>

    <aside class="sidebar" id="guestSidebar">
        <ul class="sidebar-menu">
            <li><a href="#announcements" data-page="announcements" class="active"><i class="bi bi-megaphone"></i> <span class="menu-label">Announcements</span></a></li>
            <li><a href="#reports" data-page="reports"><i class="bi bi-search"></i> <span class="menu-label">Reports</span></a></li>
            <li><a href="/safebrgy/guest/requests"><i class="bi bi-file-earmark-text"></i> <span class="menu-label">Request Documents</span></a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="/safebrgy/login"><i class="bi bi-box-arrow-in-right"></i> <span class="menu-label">Login / Register</span></a>
        </div>
    </aside>
    <div class="guest-sidebar-backdrop" id="guestSidebarBackdrop"></div>

    <!-- Main Content -->
    <main class="main-content guest-main-content">
        <div class="container-fluid guest-page-shell">
        <!-- Announcements Page -->
        <section id="announcements" class="page-content active py-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-10 col-xl-8">
                        <div class="page-header d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                            <h1 class="h3 fw-bold text-primary mb-0">Announcements</h1>
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">Latest Updates</span>
                        </div>
                        
                        <div id="announcementsContainer" class="row g-4">
                            <!-- Announcements will be loaded here via JS -->
                            <div class="col-12 text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading announcements...</span>
                                </div>
                                <p class="mt-2 text-muted">Loading announcements...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Reports Page -->
        <section id="reports" class="page-content py-5 d-none">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-10 col-xl-9">
                        <div class="page-header d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                            <h1 class="h3 fw-bold text-primary mb-0">Reports</h1>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2">Community Reports</span>
                        </div>

                        <!-- Tabs -->
                        <ul class="nav nav-tabs nav-tabs-custom mb-4" id="reportsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="feed-tab" data-bs-toggle="tab" data-bs-target="#feed" type="button" role="tab" aria-controls="feed" aria-selected="true">
                                    <i class="bi bi-rss me-1"></i> Lost Feed
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="submit-tab" data-bs-toggle="tab" data-bs-target="#submit" type="button" role="tab" aria-controls="submit" aria-selected="false">
                                    <i class="bi bi-plus-circle me-1"></i> Submit Report
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="search-tab" data-bs-toggle="tab" data-bs-target="#search" type="button" role="tab" aria-controls="search" aria-selected="false">
                                    <i class="bi bi-search me-1"></i> Search Report
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="reportsTabContent">
                            <!-- Reports Feed Tab -->
                            <div class="tab-pane fade show active" id="feed" role="tabpanel" aria-labelledby="feed-tab">
                                <div id="reportsFeedContainer">
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading reports...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Loading lost feed...</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Report Tab -->
                            <div class="tab-pane fade" id="submit" role="tabpanel" aria-labelledby="submit-tab">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-white border-0 pb-0">
                                        <h5 class="fw-bold text-primary mb-0">Submit a New Report</h5>
                                        <p class="text-muted small mb-0">Fill in the details below to submit a report. Fields marked with <span class="text-danger">*</span> are required.</p>
                                    </div>
                                    <div class="card-body">
                                        <form id="submitReportForm" enctype="multipart/form-data" novalidate>
                                            <div class="row g-3">
                                                <!-- Report Type -->
                                                <div class="col-12 col-md-6">
                                                    <label for="reportType" class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="reportType" name="report_type" required>
                                                        <option value="" disabled selected>Select report type</option>
                                                        <option value="Incident">Incident</option>
                                                        <option value="Lost Property">Lost Property</option>
                                                        <option value="Blotter">Blotter</option>
                                                    </select>
                                                    <div class="invalid-feedback">Please select a report type</div>
                                                </div>

                                                <!-- Title -->
                                                <div class="col-12">
                                                    <label for="reportTitle" class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="reportTitle" name="title" placeholder="Brief title of the report" required maxlength="255">
                                                    <div class="invalid-feedback">Title is required</div>
                                                </div>

                                                <!-- Description -->
                                                <div class="col-12">
                                                    <label for="reportDescription" class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" id="reportDescription" name="description" rows="4" placeholder="Describe the incident, lost property, or blotter in detail" required></textarea>
                                                    <div class="invalid-feedback">Description is required</div>
                                                </div>

                                                <!-- Location -->
                                                <div class="col-12">
                                                    <label for="reportLocation" class="form-label fw-semibold">Location <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="reportLocation" name="location" placeholder="Where did this happen? (e.g., Purok 3, Barangay Hall vicinity)" required maxlength="255">
                                                    <div class="invalid-feedback">Location is required</div>
                                                </div>

                                                <!-- Pictures Upload -->
                                                <div class="col-12">
                                                    <label for="reportPictures" class="form-label fw-semibold">Pictures (Optional)</label>
                                                    <input type="file" class="form-control" id="reportPictures" name="pictures[]" accept="image/*" multiple>
                                                    <div class="form-text">Maximum 5 files, 5MB each. Supported formats: JPG, PNG, GIF, WebP</div>
                                                    <div id="picturePreview" class="row g-2 mt-2"></div>
                                                </div>

                                                <!-- Contact Method -->
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold">How to reach you for updates <span class="text-danger">*</span></label>
                                                    <div class="btn-group w-100" role="group" aria-label="Contact method">
                                                        <input type="radio" class="btn-check" name="contact_method" id="contactEmail" value="email" autocomplete="off" required>
                                                        <label class="btn btn-outline-primary" for="contactEmail">
                                                            <i class="bi bi-envelope me-1"></i> Email
                                                        </label>
                                                        <input type="radio" class="btn-check" name="contact_method" id="contactMobile" value="mobile" autocomplete="off" required>
                                                        <label class="btn btn-outline-primary" for="contactMobile">
                                                            <i class="bi bi-phone me-1"></i> Mobile Number
                                                        </label>
                                                    </div>
                                                    <div class="invalid-feedback d-block">Please select a contact method</div>
                                                </div>

                                                <!-- Email Field (shown when email selected) -->
                                                <div class="col-12" id="emailFieldContainer" style="display: none;">
                                                    <label for="contactEmailInput" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                                    <input type="email" class="form-control" id="contactEmailInput" name="contact_email" placeholder="your@email.com" autocomplete="email">
                                                    <div class="invalid-feedback">Valid email is required</div>
                                                </div>

                                                <!-- Mobile Field (shown when mobile selected) -->
                                                <div class="col-12" id="mobileFieldContainer" style="display: none;">
                                                    <label for="contactMobileInput" class="form-label fw-semibold">Mobile Number <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">+63</span>
                                                        <input type="tel" class="form-control" id="contactMobileInput" name="contact_mobile" placeholder="9XXXXXXXXX" pattern="[0-9]{10}" maxlength="10" autocomplete="tel">
                                                    </div>
                                                    <div class="form-text">Enter 10-digit number without +63 (e.g., 9171234567)</div>
                                                    <div class="invalid-feedback">Valid Philippine mobile number required (10 digits)</div>
                                                </div>

                                                <!-- User AKA -->
                                                <div class="col-12">
                                                    <label for="guestAka" class="form-label fw-semibold">Your Name / Alias <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="guestAka" name="guest_aka" placeholder="How should we address you?" required maxlength="150">
                                                    <div class="invalid-feedback">Your name/alias is required</div>
                                                </div>

                                                <!-- Submit Button -->
                                                <div class="col-12">
                                                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold" id="submitBtn">
                                                        <i class="bi bi-send me-2"></i> Submit Report
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Search Report Tab -->
                            <div class="tab-pane fade" id="search" role="tabpanel" aria-labelledby="search-tab">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-white border-0 pb-0">
                                        <h5 class="fw-bold text-primary mb-0">Search Your Report</h5>
                                        <p class="text-muted small mb-0">Enter your case number to view report details and status.</p>
                                    </div>
                                    <div class="card-body">
                                        <form id="searchReportForm" class="mb-4">
                                            <div class="input-group">
                                                <span class="input-group-text bg-primary text-white fw-bold">CASE-</span>
                                                <input type="text" class="form-control form-control-lg" id="searchCaseNumber" name="case_number" placeholder="YYYYMMDD-XXXX or CASE-YYYYMMDD-XXXX" pattern="(CASE-)?[0-9]{8}-[0-9]{4}" required autocomplete="off">
                                                <button type="submit" class="btn btn-primary px-4 fw-semibold">
                                                    <i class="bi bi-search me-1"></i> Search
                                                </button>
                                            </div>
                                            <div class="form-text">Format: CASE-YYYYMMDD-XXXX (e.g., CASE-20260822-0472)</div>
                                        </form>
                                        
                                        <div id="searchResultContainer" class="d-none">
                                            <!-- Search results will appear here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-auto">
        <div class="container text-center text-muted small">
            <p class="mb-0">&copy; 2026 SafeBrgy. All rights reserved.</p>
        </div>
    </footer>

    <!-- Announcement Modal (Login/Register Prompt) -->
    <div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold" id="announcementModalLabel">
                        <i class="bi bi-megaphone me-2"></i> Announcements
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="bi bi-info-circle-fill text-primary fs-1 mb-3"></i>
                    <h5 class="fw-bold mb-3">For more announcements</h5>
                    <p class="text-muted mb-4">Create or log in to your account to access all announcements and stay updated with the latest barangay news.</p>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary px-4 fw-semibold" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#loginRegisterModal">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Login / Register
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Login/Register Modal -->
    <div class="modal fade" id="loginRegisterModal" tabindex="-1" aria-labelledby="loginRegisterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold" id="loginRegisterModalLabel">
                        <i class="bi bi-person-circle me-2"></i> Login / Register
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="text-center mb-4">
                        <p class="text-muted">Access your account to view all announcements, submit reports, and track your cases.</p>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="/safebrgy/login" class="btn btn-primary btn-lg fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Login
                        </a>
                        <a href="/safebrgy/register" class="btn btn-outline-primary btn-lg fw-semibold">
                            <i class="bi bi-person-plus me-2"></i> Register
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Submission Success Modal -->
    <div class="modal fade" id="submitSuccessModal" tabindex="-1" aria-labelledby="submitSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white border-0">
                    <h5 class="modal-title fw-bold" id="submitSuccessModalLabel">
                        <i class="bi bi-check-circle-fill me-2"></i> Report Submitted
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="bi bi-check-circle-fill text-success fs-1 mb-3"></i>
                    <h5 class="fw-bold mb-3" id="successGreeting">Hello!</h5>
                    <p class="text-muted mb-2" id="successSubmissionMessage">Your report has been submitted and is pending review.</p>
                    <div class="alert alert-info mb-3">
                        <strong>Case Number:</strong> 
                        <code id="successCaseNumber" class="fs-6 text-primary" style="cursor: pointer; user-select: all;"></code>
                        <button type="button" class="btn btn-sm btn-outline-primary ms-2" id="copyCaseNumber" title="Copy to clipboard">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                    <p class="text-muted small mb-0" id="successNotificationMessage"></p>
                    <p class="text-muted small mb-0 mt-2">Save your case number for future reference and tracking.</p>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-primary px-4 fw-semibold" data-bs-dismiss="modal">Done</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Search Result Modal -->
    <div class="modal fade" id="searchResultModal" tabindex="-1" aria-labelledby="searchResultModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold" id="searchResultModalLabel">
                        <i class="bi bi-file-text me-2"></i> Report Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="searchResultModalBody">
                    <!-- Search result details will be loaded here -->
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>