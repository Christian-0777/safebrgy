// Reports page functionality
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('searchReports');
  const filterStatus = document.getElementById('filterStatus');
  const reportsTable = document.getElementById('reportsTable');
  const reportRows = document.querySelectorAll('#reportsTable .report-row');
  const createReportForm = document.getElementById('createReportForm');
  const reportType = document.getElementById('reportType');
  const pictureUploadArea = document.getElementById('pictureUploadArea');
  const reportPicture = document.getElementById('reportPicture');
  const picturePreview = document.getElementById('picturePreview');
  const viewReportModal = document.getElementById('viewReportModal');
  const reportDetailsContent = document.getElementById('reportDetailsContent');
  const reportButtons = document.querySelectorAll('.btn-view-report');

  const requestedReportType = new URLSearchParams(window.location.search).get('report_type');
  if (requestedReportType === 'Incident' || requestedReportType === 'Lost Property') {
    if (reportType) {
      reportType.value = requestedReportType;
    }
    const createReportModal = document.getElementById('createReportModal');
    if (createReportModal && window.bootstrap) {
      bootstrap.Modal.getOrCreateInstance(createReportModal).show();
    }
  }

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
        setSelectedFiles(files);
      }
    });
  }

  // Picture file input change handler
  if (reportPicture) {
    reportPicture.addEventListener('change', (e) => {
      setSelectedFiles(e.target.files);
    });
  }

  function setSelectedFiles(fileList) {
    const files = Array.from(fileList);

    if (files.length > 10) {
      alert('You can upload up to 10 pictures.');
      return;
    }

    if (files.some(file => !file.type.startsWith('image/'))) {
      alert('Please select valid image files only.');
      return;
    }

    const dataTransfer = new DataTransfer();
    files.forEach(file => dataTransfer.items.add(file));
    reportPicture.files = dataTransfer.files;
    renderPicturePreviews(files);
  }

  function renderPicturePreviews(files) {
    picturePreview.innerHTML = '';

    files.forEach((file, index) => {
      const reader = new FileReader();
      reader.onload = (e) => {
        const previewItem = document.createElement('div');
        previewItem.className = 'picture-preview-item';
        previewItem.innerHTML = `
          <img src="${e.target.result}" alt="Preview ${index + 1}">
          <button type="button" class="picture-remove-btn" title="Remove picture" aria-label="Remove picture">
            <i class="fas fa-times"></i>
          </button>
        `;
        previewItem.querySelector('.picture-remove-btn').addEventListener('click', () => {
          const remainingFiles = Array.from(reportPicture.files).filter((_, fileIndex) => fileIndex !== index);
          setSelectedFiles(remainingFiles);
        });
        picturePreview.appendChild(previewItem);
      };
      reader.readAsDataURL(file);
    });
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

  function escapeHtml(value) {
    const element = document.createElement('div');
    element.textContent = value == null ? '' : String(value);
    return element.innerHTML;
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
              <div class="detail-value case-number-detail">
                <span id="reportCaseNumber">${escapeHtml(report.case_number || 'N/A')}</span>
                <button type="button" class="copy-case-number-btn" id="copyCaseNumberBtn" title="Copy case number" aria-label="Copy case number">
                  <i class="fas fa-copy"></i>
                </button>
              </div>
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

          const copyCaseNumberButton = document.getElementById('copyCaseNumberBtn');
          const caseNumberElement = document.getElementById('reportCaseNumber');
          if (copyCaseNumberButton && caseNumberElement && caseNumberElement.textContent !== 'N/A') {
            copyCaseNumberButton.addEventListener('click', async () => {
              const caseNumber = caseNumberElement.textContent.trim();
              let copied = false;

              if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(caseNumber);
                copied = true;
              } else {
                const temporaryInput = document.createElement('textarea');
                temporaryInput.value = caseNumber;
                temporaryInput.style.position = 'fixed';
                temporaryInput.style.opacity = '0';
                document.body.appendChild(temporaryInput);
                temporaryInput.select();
                copied = document.execCommand('copy');
                temporaryInput.remove();
              }

              if (copied) {
                copyCaseNumberButton.innerHTML = '<i class="fas fa-check"></i>';
                copyCaseNumberButton.title = 'Copied';
                window.setTimeout(() => {
                  copyCaseNumberButton.innerHTML = '<i class="fas fa-copy"></i>';
                  copyCaseNumberButton.title = 'Copy case number';
                }, 1500);
              }
            });
          }
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
