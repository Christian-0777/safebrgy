// SafeBrgy Resident Profile Page Scripts

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeProfilePage();
});

function initializeProfilePage() {
    // Initialize logout button
    const logoutButtons = document.querySelectorAll('.logout, a[href*="logout"]');
    logoutButtons.forEach(btn => {
        if (btn.classList.contains('logout')) {
            btn.addEventListener('click', handleLogout);
        }
    });
}

/**
 * Open image modal for ID preview
 */
function openImageModal(imageElement) {
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    const modalImage = document.getElementById('modalImage');
    modalImage.src = imageElement.src;
    modal.show();
}

/**
 * Handle logout
 */
function handleLogout(e) {
    e.preventDefault();
    
    // Show confirmation dialog
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = '../../public/logout.php';
    }
}

/**
 * Format age display (Years, Months, Days)
 */
function formatAge(years, months, days) {
    const parts = [];
    
    if (years > 0) {
        parts.push(years + (years === 1 ? ' year' : ' years'));
    }
    if (months > 0) {
        parts.push(months + (months === 1 ? ' month' : ' months'));
    }
    if (days > 0) {
        parts.push(days + (days === 1 ? ' day' : ' days'));
    }
    
    return parts.join(', ') || 'N/A';
}

/**
 * Download document (checks if document is ready for download)
 */
function downloadDocument(documentId) {
    const btn = event.target.closest('.btn');
    
    if (btn.disabled) {
        alert('This document is not yet available for download. Please wait until the status changes to "Ready to Receive".');
        return false;
    }
    
    // Allow the download to proceed
    return true;
}

/**
 * View more requests (if there are more than 10)
 */
function viewMoreRequests() {
    window.location.href = 'requests.php';
}

/**
 * Copy text to clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        // Show success message
        showNotification('Copied to clipboard!', 'success');
    }).catch(() => {
        showNotification('Failed to copy', 'error');
    });
}

/**
 * Show notification
 */
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show`;
    notification.role = 'alert';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Add to page
    document.querySelector('.main-content').prepend(notification);
    
    // Auto dismiss after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

/**
 * Print profile
 */
function printProfile() {
    window.print();
}

// Export functions for external use
window.openImageModal = openImageModal;
window.handleLogout = handleLogout;
window.downloadDocument = downloadDocument;
window.viewMoreRequests = viewMoreRequests;
window.copyToClipboard = copyToClipboard;
window.printProfile = printProfile;
