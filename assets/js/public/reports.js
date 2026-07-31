// Reports page functionality
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('searchReports');
  const filterStatus = document.getElementById('filterStatus');
  const reportsTable = document.getElementById('reportsTable');
  const reportRows = document.querySelectorAll('#reportsTable .report-row');
  const createReportForm = document.getElementById('createReportForm');
  const pictureUploadArea = document.getElementById('pictureUploadArea');
  const reportPicture = document.getElementById('reportPicture');
  const picturePreview = document.getElementById('picturePreview');
  const viewReportModal = document.getElementById('viewReportModal');
  const reportDetailsContent = document.getElementById('reportDetailsContent');
  const reportButtons = document.querySelectorAll('.btn-view-report');

  // Search functionality
  if (searchInput) {
    searchInput.addEventListener('input', filterReports);
  }

  // Filter functionality
  if (filterStatus) {
    filterStatus.addEventListener('change', filterReports);
  }

  function filterReports() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase() : '';
    const statusFilter = filterStatus ? filterStatus.value : '';

    reportRows.forEach(row => {
      let show = true;

      // Check search query
      if (searchQuery) {
        const caseNumber = row.querySelector('.case-number')?.textContent.toLowerCase() || '';
        const rowText = row.innerText.toLowerCase();
        show = caseNumber.includes(searchQuery) || rowText.includes(searchQuery);
      }

      // Check status filter
      if (show && statusFilter) {
        const status = row.getAttribute('data-status');
        show = status === statusFilter;
      }

      row.style.display = show ? '' : 'none';
    });
  }

  // Picture upload area click handler
  if (pictureUploadArea) {
    pictureUploadArea.addEventListener('click', () => {
      reportPicture.click();
    });

    // Drag and drop
    pictureUploadArea.addEventListener('dragover', (e) => {
      e.preventDefault();
      pictureUploadArea.style.borderColor = '#007bff';
      pictureUploadArea.style.backgroundColor = 'rgba(0, 123, 255, 0.05)';
    });

    pictureUploadArea.addEventListener('dragleave', () => {
      pictureUploadArea.style.borderColor = '#ddd';
      pictureUploadArea.style.backgroundColor = '#f8f9fa';
    });

    pictureUploadArea.addEventListener('drop', (e) => {
      e.preventDefault();
      pictureUploadArea.style.borderColor = '#ddd';
      pictureUploadArea.style.backgroundColor = '#f8f9fa';
      
      const files = e.dataTransfer.files;
      if (files.length > 0) {
        reportPicture.files = files;
        handleFileSelect(files[0]);
      }
    });
  }

  // Picture file input change handler
  if (reportPicture) {
    reportPicture.addEventListener('change', (e) => {
      if (e.target.files.length > 0) {
        handleFileSelect(e.target.files[0]);
      }
    });
  }

  function handleFileSelect(file) {
    // Validate file type
    if (!file.type.startsWith('image/')) {
      alert('Please select a valid image file');
      return;
    }

    // Validate file size (5MB)
    if (file.size > 5 * 1024 * 1024) {
      alert('File size must be less than 5MB');
      return;
    }

    // Show preview
    const reader = new FileReader();
    reader.onload = (e) => {
      picturePreview.innerHTML = `
        <div class="picture-preview-item">
          <img src="${e.target.result}" alt="Preview">
          <button type="button" class="picture-remove-btn" title="Remove">
            <i class="fas fa-times"></i>
          </button>
        </div>
      `;

      // Remove button handler
      const removeBtn = picturePreview.querySelector('.picture-remove-btn');
      removeBtn.addEventListener('click', () => {
        reportPicture.value = '';
        picturePreview.innerHTML = '';
      });
    };
    reader.readAsDataURL(file);
  }

  // Create report form submission
  if (createReportForm) {
    createReportForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      if (window.showLoadingOverlay) {
        window.showLoadingOverlay();
      }

      const formData = new FormData(createReportForm);

      try {
        const response = await fetch('../../api/reports/create.php', {
          method: 'POST',
          body: formData
        });

        const data = await response.json();

        if (data.success) {
          alert('Report created successfully!');
          createReportForm.reset();
          picturePreview.innerHTML = '';
          
          // Close modal
          const modal = bootstrap.Modal.getInstance(document.getElementById('createReportModal'));
          modal.hide();

          // Reload page
          setTimeout(() => {
            location.reload();
          }, 1000);
        } else {
          alert('Error: ' + (data.message || 'Failed to create report'));
        }
      } catch (error) {
        if (window.hideLoadingOverlay) {
          window.hideLoadingOverlay();
        }
        console.error('Error:', error);
        alert('An error occurred while creating the report');
      }
    });
  }

  // View report functionality
  reportButtons.forEach(btn => {
    btn.addEventListener('click', async function() {
      const reportId = this.getAttribute('data-report-id');
      
      try {
        const response = await fetch(`../../api/reports/get.php?id=${reportId}`);
        const data = await response.json();

        if (data.success) {
          const report = data.report;
          reportDetailsContent.innerHTML = `
            <div class="report-detail-section">
              <div class="detail-label">Case Number</div>
              <div class="detail-value">${report.case_number || 'N/A'}</div>
            </div>

            <div class="report-detail-section">
              <div class="detail-label">Report Type</div>
              <div class="detail-value">
                <span class="badge bg-info">${report.report_type}</span>
              </div>
            </div>

            <div class="report-detail-section">
              <div class="detail-label">Title</div>
              <div class="detail-value">${report.title}</div>
            </div>

            <div class="report-detail-section">
              <div class="detail-label">Description</div>
              <div class="detail-value">${report.description}</div>
            </div>

            ${report.location ? `
              <div class="report-detail-section">
                <div class="detail-label">Location</div>
                <div class="detail-value">${report.location}</div>
              </div>
            ` : ''}

            <div class="report-detail-section">
              <div class="detail-label">Status</div>
              <div class="detail-value">
                <span class="badge bg-${
                  report.status === 'Pending' ? 'warning' :
                  report.status === 'Ongoing' ? 'info' :
                  report.status === 'Resolved' ? 'success' :
                  report.status === 'Dismissed' ? 'danger' : 'secondary'
                }">
                  ${report.status}
                </span>
              </div>
            </div>

            <div class="report-detail-section">
              <div class="detail-label">Date Submitted</div>
              <div class="detail-value">${new Date(report.created_at).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
              })}</div>
            </div>

            ${report.attachments && report.attachments.length > 0 ? `
              <div class="report-detail-section">
                <div class="detail-label">Attachments</div>
                ${report.attachments.map(attachment => `
                  <img src="../../${attachment}" alt="Report image" class="report-detail-image">
                `).join('')}
              </div>
            ` : ''}
          `;
        } else {
          reportDetailsContent.innerHTML = '<p class="text-danger">Failed to load report details</p>';
        }
      } catch (error) {
        console.error('Error:', error);
        reportDetailsContent.innerHTML = '<p class="text-danger">An error occurred while loading report details</p>';
      }
    });
  });
});
