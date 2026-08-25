<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeBrgy - Request Documents</title>
    <link rel="icon" type="image/png" href="/safebrgy/assets/img/seal.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
            <li><a href="/safebrgy/guest/"><i class="bi bi-megaphone"></i> <span class="menu-label">Announcements</span></a></li>
            <li><a href="/safebrgy/guest/#reports"><i class="bi bi-search"></i> <span class="menu-label">Lost Feed</span></a></li>
            <li><a href="/safebrgy/guest/requests" class="active"><i class="bi bi-file-earmark-text"></i> <span class="menu-label">Request Documents</span></a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="/safebrgy/login"><i class="bi bi-box-arrow-in-right"></i> <span class="menu-label">Login / Register</span></a>
        </div>
    </aside>
    <div class="guest-sidebar-backdrop" id="guestSidebarBackdrop"></div>

    <main class="main-content guest-main-content">
        <div class="container-fluid guest-page-shell">
            <div class="page-header">
                <div>
                    <h1>Request Documents</h1>
                    <p>Barangay document services for registered residents.</p>
                </div>
            </div>
            <section class="guest-request-empty page-card">
                <i class="bi bi-file-earmark-lock2 guest-request-icon" aria-hidden="true"></i>
                <h2>Guest requests are unavailable</h2>
                <p>Log in or register to request barangay documents.</p>
            </section>
        </div>
    </main>

    <div class="modal fade" id="guestRequestModal" tabindex="-1" aria-labelledby="guestRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="guestRequestModalLabel"><i class="bi bi-info-circle me-2"></i>Document Request</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    The Document Request For Guest User is Not Available. However, you can request by loging in or registering your account
                </div>
                <div class="modal-footer">
                    <a href="/safebrgy/login" class="btn btn-primary">Login/Register</a>
                    <a href="/safebrgy/guest/" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>document.addEventListener('DOMContentLoaded', function () { new bootstrap.Modal(document.getElementById('guestRequestModal')).show(); });</script>
</body>
</html>
