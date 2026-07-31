// Admin Reports Page JavaScript
document.addEventListener('DOMContentLoaded', () => {
  const viewButtons = document.querySelectorAll('.view-btn');
  const reportDetailsModal = document.getElementById('reportDetailsModal');
  const modalBody = document.getElementById('modalBody');
  const applyStatusBtn = document.getElementById('applyStatusBtn');
  let currentReportId = null;

  // Load report details when View button is clicked
  viewButtons.forEach(btn => {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      const reportId = btn.dataset.id;
      currentReportId = reportId;

      try {
        const response = await fetch('reports.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `action=get_report&id=${reportId}`
        });

        const data = await response.json();
        if (data.success && data.report) {
          populateModal(data.report);
        } else {
          modalBody.innerHTML = '<div class="alert alert-danger">Failed to load report details.</div>';
        }
      } catch (error) {
        console.error('Error:', error);
        modalBody.innerHTML = '<div class="alert alert-danger">An error occurred while loading the report.</div>';
      }
    });
  });

  // Populate modal with report details
  function populateModal(report) {
    const reporterName = report.first_name && report.last_name 
      ? `${report.first_name} ${report.last_name}` 
      : (report.username || 'Anonymous');

    const attachmentsHtml = report.attachments 
      ? `<div class="mb-3"><strong>Attachments:</strong> <br>${report.attachments}</div>`
      : '';

    const statusBadgeClass = {
      'Pending': 'warning',
      'Ongoing': 'primary',
      'Resolved': 'success',
      'Dismissed': 'danger'
    }[report.status] || 'secondary';

    const html = `
      <div class="report-details">
        <div class="row mb-3">
          <div class="col-md-6">
            <strong>Case Number:</strong> <span class="badge bg-secondary">${escapeHtml(report.case_number || 'N/A')}</span>
          </div>
          <div class="col-md-6">
            <strong>Report Type:</strong> <span class="badge bg-info">${escapeHtml(report.report_type)}</span>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <strong>Resident ID:</strong> <p>${escapeHtml(report.resident_id || 'N/A')}</p>
          </div>
          <div class="col-md-6">
            <strong>Reporter:</strong> <p>${escapeHtml(reporterName)}</p>
          </div>
        </div>

        <div class="mb-3">
          <strong>Title:</strong> <p>${escapeHtml(report.title || 'Untitled')}</p>
        </div>

        <div class="mb-3">
          <strong>Description:</strong> 
          <p class="bg-light p-3 rounded">${escapeHtml(report.description || 'No description provided')}</p>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <strong>Location:</strong> <p>${escapeHtml(report.location || 'Not specified')}</p>
          </div>
          <div class="col-md-6">
            <strong>Date Filed:</strong> <p>${new Date(report.created_at).toLocaleString()}</p>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <strong>Reporter Email:</strong> <p>${escapeHtml(report.email || 'N/A')}</p>
          </div>
          <div class="col-md-6"></div>
        </div>

        ${attachmentsHtml}

        <hr>

        <div class="mb-3">
          <label class="form-label"><strong>Update Status:</strong></label>
          <select class="form-select" id="statusSelect">
            <option value="Pending" ${report.status === 'Pending' ? 'selected' : ''}>Pending</option>
            <option value="Ongoing" ${report.status === 'Ongoing' ? 'selected' : ''}>Ongoing</option>
            <option value="Resolved" ${report.status === 'Resolved' ? 'selected' : ''}>Resolved</option>
            <option value="Dismissed" ${report.status === 'Dismissed' ? 'selected' : ''}>Dismissed</option>
          </select>
          <small class="text-muted">Current Status: <span class="badge bg-${statusBadgeClass}">${escapeHtml(report.status)}</span></small>
        </div>
      </div>
    `;

    modalBody.innerHTML = html;
  }

  // Apply status update
  applyStatusBtn.addEventListener('click', async () => {
    if (window.showLoadingOverlay) {
      window.showLoadingOverlay();
    }

    const statusSelect = document.getElementById('statusSelect');
    const newStatus = statusSelect.value;

    try {
      const response = await fetch('reports.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update_status&id=${currentReportId}&status=${newStatus}`
      });

      const data = await response.json();
      if (window.hideLoadingOverlay) {
        window.hideLoadingOverlay();
      }
      if (data.success) {
        alert('Report status updated successfully!');
        location.reload();
      } else {
        alert('Failed to update report status.');
      }
    } catch (error) {
      if (window.hideLoadingOverlay) {
        window.hideLoadingOverlay();
      }
      console.error('Error:', error);
      alert('An error occurred while updating the status.');
    }
  });

  // Escape HTML to prevent XSS
  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  // Search functionality (client-side)
  // Search is handled via form submission in the PHP page for optimal filtering
});

// Handle logout button
document.addEventListener('DOMContentLoaded', () => {
  const logoutBtn = document.querySelector('.profile-dropdown .logout');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', (e) => {
      e.preventDefault();
      window.location.href = '../../admin/logout.php';
    });
  }
});
