/**
 * SafeBrgy Guest Portal - Main JavaScript
 * Handles all frontend interactions
 */

// ===== Configuration =====
const API_BASE = 'api';
let announcementModalShown = false;
const reportDetails = new Map();

// ===== Utility Functions =====
const $ = (selector) => document.querySelector(selector);
const $$ = (selector) => document.querySelectorAll(selector);

function showToast(message, type = 'info') {
    // Create toast container if not exists
    let toastContainer = $('#toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-info';
    const iconClass = type === 'success' ? 'bi-check-circle-fill' : type === 'error' ? 'bi-x-circle-fill' : 'bi-info-circle-fill';
    
    const toastHtml = `
        <div id="${toastId}" class="toast ${bgClass} text-white" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header ${bgClass} text-white border-0">
                <i class="bi ${iconClass} me-2"></i>
                <strong class="me-auto">SafeBrgy</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">${message}</div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = $(`#${toastId}`);
    const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
    toast.show();
    
    toastElement.addEventListener('hidden.bs.toast', () => toastElement.remove());
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getPriorityBadgeClass(priority) {
    switch (priority) {
        case 'urgent': return 'badge-priority-urgent';
        case 'important': return 'badge-priority-important';
        default: return 'badge-priority-normal';
    }
}

function getPriorityLabel(priority) {
    switch (priority) {
        case 'urgent': return 'Urgent';
        case 'important': return 'Important';
        default: return 'Normal';
    }
}

function getStatusBadgeClass(status) {
    switch (status) {
        case 'Pending': return 'badge-status-pending';
        case 'Ongoing': return 'badge-status-ongoing';
        case 'Resolved': return 'badge-status-resolved';
        case 'Dismissed': return 'badge-status-dismissed';
        default: return 'bg-secondary';
    }
}

function getStatusLabel(status) {
    return status;
}

// ===== Page Navigation =====
function initNavigation() {
    $$('[data-page]').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const page = link.dataset.page;
            showPage(page);
            
            // Update active nav link
            $$('[data-page]').forEach(l => l.classList.remove('active'));
            link.classList.add('active');

            if (page === 'reports') {
                const feedTab = $('#feed-tab');
                if (feedTab && window.bootstrap) {
                    bootstrap.Tab.getOrCreateInstance(feedTab).show();
                }
            }
        });
    });
}

function initGuestSidebar() {
    const toggle = $('#guestSidebarToggle');
    const sidebar = $('#guestSidebar');
    const backdrop = $('#guestSidebarBackdrop');
    if (!toggle || !sidebar) return;

    const closeSidebar = () => {
        sidebar.classList.remove('guest-sidebar-open');
        backdrop?.classList.remove('guest-sidebar-backdrop-open');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('guest-sidebar-active');
    };

    toggle.addEventListener('click', () => {
        const isOpen = sidebar.classList.toggle('guest-sidebar-open');
        backdrop?.classList.toggle('guest-sidebar-backdrop-open', isOpen);
        toggle.setAttribute('aria-expanded', String(isOpen));
        document.body.classList.toggle('guest-sidebar-active', isOpen);
    });

    sidebar.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', closeSidebar);
    });
    backdrop?.addEventListener('click', closeSidebar);
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') closeSidebar();
    });
}

function showPage(pageId) {
    $$('.page-content').forEach(page => {
        page.classList.remove('active');
        page.classList.add('d-none');
    });
    
    const targetPage = $(`#${pageId}`);
    if (targetPage) {
        targetPage.classList.remove('d-none');
        // Force reflow for animation
        targetPage.offsetHeight;
        targetPage.classList.add('active');
    }
    
    // Load page-specific data
    if (pageId === 'announcements' && !announcementModalShown) {
        loadAnnouncements();
        // Show modal after a short delay
        setTimeout(() => {
            showAnnouncementModal();
        }, 500);
    } else if (pageId === 'reports') {
        loadReportsFeed();
    }
}

// ===== Announcements =====
async function loadAnnouncements() {
    const container = $('#announcementsContainer');
    if (!container) return;
    
    try {
        const response = await fetch(`${API_BASE}/announcements.php`);
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            container.innerHTML = data.data.map(announcement => createAnnouncementCard(announcement)).join('');
        } else {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="bi bi-megaphone text-muted fs-1 mb-3"></i>
                    <h5 class="text-muted">No announcements at the moment</h5>
                    <p class="text-muted small">Check back later for updates.</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Failed to load announcements:', error);
        container.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="bi bi-exclamation-triangle text-warning fs-1 mb-3"></i>
                <h5 class="text-muted">Failed to load announcements</h5>
                <button class="btn btn-primary btn-sm mt-2" onclick="loadAnnouncements()">
                    <i class="bi bi-arrow-clockwise me-1"></i> Retry
                </button>
            </div>
        `;
    }
}

function createAnnouncementCard(announcement) {
    const priorityClass = getPriorityBadgeClass(announcement.priority);
    const priorityLabel = getPriorityLabel(announcement.priority);
    const publishedDate = formatDate(announcement.published_at);
    
    let attachmentsHtml = '';
    if (announcement.attachments && announcement.attachments.length > 0) {
        attachmentsHtml = `
            <div class="attachments-grid mt-3">
                ${announcement.attachments.map(att => `
                    <div class="attachment-item">
                        <img src="${att}" alt="Attachment" loading="lazy">
                    </div>
                `).join('')}
            </div>
        `;
    }
    
    return `
        <div class="col-12 col-md-6">
            <div class="announcement-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="badge ${priorityClass}">${priorityLabel}</span>
                    <small class="text-white-50">${publishedDate}</small>
                </div>
                <div class="card-body">
                    <h5 class="card-title">${escapeHtml(announcement.title)}</h5>
                    <p class="card-text">${escapeHtml(announcement.body)}</p>
                    ${attachmentsHtml}
                </div>
                <div class="card-footer text-muted small">
                    <i class="bi bi-calendar-event me-1"></i> Published on ${publishedDate}
                </div>
            </div>
        </div>
    `;
}

function showAnnouncementModal() {
    if (announcementModalShown) return;
    
    const modal = new bootstrap.Modal($('#announcementModal'));
    modal.show();
    announcementModalShown = true;
    
    // Store in sessionStorage so it doesn't show again in this session
    sessionStorage.setItem('announcementModalShown', 'true');
}

// Check if modal was already shown in this session
if (sessionStorage.getItem('announcementModalShown')) {
    announcementModalShown = true;
}

// ===== Reports Feed =====
async function loadReportsFeed() {
    const container = $('#reportsFeedContainer');
    if (!container) return;
    
    try {
        const response = await fetch(`${API_BASE}/reports_feed.php`);
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            container.innerHTML = `
                <div class="row g-3">
                    ${data.data.map(report => createReportCard(report)).join('')}
                </div>
            `;
        } else {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-file-text text-muted fs-1 mb-3"></i>
                    <h5 class="text-muted">No reports available</h5>
                    <p class="text-muted small">No lost property reports have been published yet.</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Failed to load reports feed:', error);
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-exclamation-triangle text-warning fs-1 mb-3"></i>
                <h5 class="text-muted">Failed to load reports</h5>
                <button class="btn btn-primary btn-sm mt-2" onclick="loadReportsFeed()">
                    <i class="bi bi-arrow-clockwise me-1"></i> Retry
                </button>
            </div>
        `;
    }
}

function createReportCard(report) {
    const statusClass = getStatusBadgeClass(report.status);
    const statusLabel = getStatusLabel(report.status);
    const createdDate = formatDate(report.created_at);
    const sourceLabel = report.source === 'guest' ? 'Guest' : 'Registered';
    const reportKey = `${report.source}-${report.id}`;
    reportDetails.set(reportKey, report);
    
    let attachmentsHtml = '';
    if (report.attachments && report.attachments.length > 0) {
        attachmentsHtml = `
            <div class="report-attachments mt-2" aria-label="Report pictures">
                ${report.attachments.slice(0, 3).map(att => `
                    <div class="attachment-item">
                        <img src="${att}" alt="Attachment" loading="lazy">
                    </div>
                `).join('')}
                ${report.attachments.length > 3 ? `
                    <div class="attachment-item d-flex align-items-center justify-content-center bg-light">
                        <span class="text-muted small">+${report.attachments.length - 3} more</span>
                    </div>
                ` : ''}
            </div>
        `;
    }
    
    return `
        <div class="col-12 col-md-6 col-lg-4">
            <div class="report-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="badge ${statusClass}">${statusLabel}</span>
                    <small class="text-muted">${sourceLabel}</small>
                </div>
                <div class="card-body d-flex flex-column">
                    <h6 class="card-title">${escapeHtml(report.title)}</h6>
                    <p class="card-text">${escapeHtml(report.description)}</p>
                    ${attachmentsHtml}
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-geo-alt me-1"></i> ${escapeHtml(report.location || 'Not specified')}
                    </small>
                    <small class="text-muted">
                        <i class="bi bi-clock me-1"></i> ${createdDate}
                    </small>
                </div>
                <div class="px-3 pb-3">
                    <button type="button" class="btn btn-outline-primary btn-sm w-100" onclick="showGuestReportDetails('${reportKey}')">
                        <i class="bi bi-eye me-1"></i> View Details
                    </button>
                </div>
            </div>
        </div>
    `;
}

function showGuestReportDetails(reportKeyOrReport) {
    const report = typeof reportKeyOrReport === 'string'
        ? reportDetails.get(reportKeyOrReport)
        : reportKeyOrReport;
    if (!report) return;

    const pictures = report.attachments && report.attachments.length > 0
        ? `<div class="attachments-grid">${report.attachments.map((attachment) => `
            <div class="attachment-item"><img src="${escapeHtml(attachment)}" alt="Report picture" loading="lazy"></div>
        `).join('')}</div>`
        : '<span class="text-muted">No pictures uploaded</span>';

    $('#searchResultModalBody').innerHTML = `
        <div class="search-result-detail">
            <div class="detail-row"><span class="detail-label">Case Number:</span><div class="detail-value">${escapeHtml(report.case_number)}</div></div>
            <div class="detail-row"><span class="detail-label">Title:</span><div class="detail-value">${escapeHtml(report.title)}</div></div>
            <div class="detail-row"><span class="detail-label">Description:</span><div class="detail-value">${escapeHtml(report.description)}</div></div>
            <div class="detail-row"><span class="detail-label">Location:</span><div class="detail-value">${escapeHtml(report.location || 'Not specified')}</div></div>
            <div class="detail-row"><span class="detail-label">Date Filed:</span><div class="detail-value">${formatDate(report.created_at)}</div></div>
            <div class="detail-row"><span class="detail-label">Pictures:</span><div class="detail-value">${pictures}</div></div>
        </div>
    `;

    new bootstrap.Modal($('#searchResultModal')).show();
}

// ===== Submit Report =====
function initSubmitReport() {
    const form = $('#submitReportForm');
    if (!form) return;
    
    // Contact method toggle
    const contactEmailRadio = $('#contactEmail');
    const contactMobileRadio = $('#contactMobile');
    const emailFieldContainer = $('#emailFieldContainer');
    const mobileFieldContainer = $('#mobileFieldContainer');
    const emailInput = $('#contactEmailInput');
    const mobileInput = $('#contactMobileInput');
    
    function toggleContactFields() {
        if (contactEmailRadio.checked) {
            emailFieldContainer.style.display = 'block';
            mobileFieldContainer.style.display = 'none';
            emailInput.required = true;
            mobileInput.required = false;
        } else {
            emailFieldContainer.style.display = 'none';
            mobileFieldContainer.style.display = 'block';
            emailInput.required = false;
            mobileInput.required = true;
        }
    }
    
    contactEmailRadio.addEventListener('change', toggleContactFields);
    contactMobileRadio.addEventListener('change', toggleContactFields);
    
    // Picture preview
    const pictureInput = $('#reportPictures');
    const picturePreview = $('#picturePreview');
    
    pictureInput.addEventListener('change', (e) => {
        picturePreview.innerHTML = '';
        const files = Array.from(e.target.files).slice(0, 5);
        
        files.forEach((file, index) => {
            if (!file.type.startsWith('image/')) return;
            
            const reader = new FileReader();
            reader.onload = (event) => {
                const previewItem = document.createElement('div');
                previewItem.className = 'col-auto preview-item';
                previewItem.innerHTML = `
                    <img src="${event.target.result}" alt="Preview ${index + 1}">
                    <button type="button" class="remove-btn" data-index="${index}" aria-label="Remove image">
                        <i class="bi bi-x"></i>
                    </button>
                `;
                picturePreview.appendChild(previewItem);
            };
            reader.readAsDataURL(file);
        });
        
        // Update file input to only keep valid files
        const dt = new DataTransfer();
        files.forEach(file => dt.items.add(file));
        pictureInput.files = dt.files;
    });
    
    // Remove preview item
    picturePreview.addEventListener('click', (e) => {
        const removeBtn = e.target.closest('.remove-btn');
        if (removeBtn) {
            const index = parseInt(removeBtn.dataset.index);
            const items = picturePreview.querySelectorAll('.preview-item');
            items[index].remove();
            
            // Re-index remaining items
            picturePreview.querySelectorAll('.preview-item').forEach((item, i) => {
                item.querySelector('.remove-btn').dataset.index = i;
            });
            
            // Update file input
            const dt = new DataTransfer();
            Array.from(pictureInput.files).forEach((file, i) => {
                if (i !== index) dt.items.add(file);
            });
            pictureInput.files = dt.files;
        }
    });
    
    // Form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }
        
        const submitBtn = $('#submitBtn');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Submitting...';
        
        const formData = new FormData(form);
        
        try {
            const response = await fetch(`${API_BASE}/reports_submit.php`, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showSubmitSuccessModal(
                    data.data.guest_aka,
                    data.data.case_number,
                    data.data.notification_channel,
                    data.data.notification_sent
                );
                form.reset();
                form.classList.remove('was-validated');
                picturePreview.innerHTML = '';
                toggleContactFields(); // Reset to default
            } else {
                showToast(data.message || 'Failed to submit report', 'error');
            }
        } catch (error) {
            console.error('Submit error:', error);
            showToast('An error occurred. Please try again.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    });
}

function showSubmitSuccessModal(guestAka, caseNumber, notificationChannel, notificationSent) {
    $('#successGreeting').textContent = `Hello, ${escapeHtml(guestAka)}!`;
    $('#successCaseNumber').textContent = caseNumber;
    $('#successSubmissionMessage').textContent = `Your report (${caseNumber}) has been submitted and is pending review.`;
    const notificationMessage = notificationSent
        ? `A confirmation was sent by ${notificationChannel === 'email' ? 'email' : 'SMS'}.`
        : 'Your report was saved, but the confirmation could not be sent.';
    $('#successNotificationMessage').textContent = notificationMessage;
    
    const modal = new bootstrap.Modal($('#submitSuccessModal'));
    modal.show();
    
    // Copy case number functionality
    const copyBtn = $('#copyCaseNumber');
    const caseNumberEl = $('#successCaseNumber');
    
    copyBtn.onclick = () => {
        navigator.clipboard.writeText(caseNumber).then(() => {
            copyBtn.classList.add('copied');
            copyBtn.innerHTML = '<i class="bi bi-check"></i>';
            showToast('Case number copied to clipboard!', 'success');
            
            setTimeout(() => {
                copyBtn.classList.remove('copied');
                copyBtn.innerHTML = '<i class="bi bi-clipboard"></i>';
            }, 2000);
        }).catch(() => {
            showToast('Failed to copy. Please copy manually.', 'error');
        });
    };
    
    // Also allow clicking on the case number to copy
    caseNumberEl.onclick = () => copyBtn.click();
}

// ===== Search Report =====
function initSearchReport() {
    const form = $('#searchReportForm');
    if (!form) return;
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const caseNumberInput = $('#searchCaseNumber');
        const caseNumber = caseNumberInput.value.trim().toUpperCase().replace(/^CASE-/, '');
        
        if (!caseNumber) {
            showToast('Please enter a case number', 'error');
            return;
        }
        
        // Validate format
        if (!/^\d{8}-\d{4}$/.test(caseNumber)) {
            showToast('Invalid format. Use YYYYMMDD-XXXX', 'error');
            return;
        }
        
        const fullCaseNumber = `CASE-${caseNumber}`;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Searching...';
        
        try {
            const response = await fetch(`${API_BASE}/reports_search.php?case_number=${encodeURIComponent(fullCaseNumber)}`);
            const data = await response.json();
            
            if (data.success) {
                showSearchResultModal(data.data);
            } else {
                showToast(data.message || 'Report not found', 'error');
            }
        } catch (error) {
            console.error('Search error:', error);
            showToast('An error occurred. Please try again.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    });
}

function showSearchResultModal(report) {
    showGuestReportDetails(report);
}

// Global function for copying case number from search result
window.copyToClipboard = function(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Case number copied to clipboard!', 'success');
    }).catch(() => {
        showToast('Failed to copy. Please copy manually.', 'error');
    });
};

// ===== Initialize =====
document.addEventListener('DOMContentLoaded', () => {
    initNavigation();
    initGuestSidebar();
    initSubmitReport();
    initSearchReport();
    
    // Load initial page (announcements)
    loadAnnouncements();
});

// ===== Helper Functions =====
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Make functions globally accessible for inline onclick handlers
window.loadAnnouncements = loadAnnouncements;
window.loadReportsFeed = loadReportsFeed;
window.showGuestReportDetails = showGuestReportDetails;