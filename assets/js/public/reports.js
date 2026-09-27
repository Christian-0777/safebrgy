// Reports page functionality
document.addEventListener('DOMContentLoaded', function() {
  if (window.bootstrap) {
    document.querySelectorAll('.report-status-icon[data-bs-toggle="tooltip"]').forEach(icon => {
      bootstrap.Tooltip.getOrCreateInstance(icon);
    });
  }

  const searchInput = document.getElementById('searchReports');
  const filterStatus = document.getElementById('filterStatus');
  const reportsTable = document.getElementById('reportsTable');
  const createReportForm = document.getElementById('createReportForm');
  const reportType = document.getElementById('reportType');
  const reportTagsSection = document.getElementById('reportTagsSection');
  const reportTagChoices = document.getElementById('reportTagChoices');
  const addReportTagButton = document.getElementById('addReportTagButton');
  const addReportTagModal = document.getElementById('addReportTagModal');
  const addReportTagForm = document.getElementById('addReportTagForm');
  const addReportTagError = document.getElementById('addReportTagError');
  const pictureUploadArea = document.getElementById('pictureUploadArea');
  const reportPicture = document.getElementById('reportPicture');
  const picturePreview = document.getElementById('picturePreview');
  const viewReportModal = document.getElementById('viewReportModal');
  const reportDetailsContent = document.getElementById('reportDetailsContent');
  let selectedTagIds = new Set();

  const requestedReportType = new URLSearchParams(window.location.search).get('report_type');
  if (['Incident', 'Lost Property', 'Public Concerns', 'Blotter'].includes(requestedReportType)) {
    if (reportType) {
      reportType.value = requestedReportType;
    }
    const createReportModal = document.getElementById('createReportModal');
    if (createReportModal && window.bootstrap) {
      bootstrap.Modal.getOrCreateInstance(createReportModal).show();
    }
  }

  async function loadReportTags(type, selectedTagId = null, preserveSelection = false) {
    if (!preserveSelection) selectedTagIds = new Set();
    if (selectedTagId && selectedTagIds.size < 5) selectedTagIds.add(String(selectedTagId));
    if (!type || !reportTagsSection || !reportTagChoices) {
      if (reportTagsSection) reportTagsSection.hidden = true;
      return;
    }
    reportTagsSection.hidden = false;
    reportTagChoices.innerHTML = '<span class="text-muted">Loading tags...</span>';
    try {
      const response = await fetch(`../../api/reports/tags.php?report_type=${encodeURIComponent(type)}`);
      const data = await response.json();
      if (!response.ok || !data.success) throw new Error(data.message || 'Could not load tags.');
      if (reportType.value !== type) return;
      renderReportTags(data.tags || []);
    } catch (error) {
      if (reportType.value !== type) return;
      reportTagChoices.innerHTML = `<span class="text-danger">${escapeHtml(error.message)}</span>`;
    }
  }

  function renderReportTags(tags) {
    reportTagChoices.innerHTML = tags.map(tag => `
      <label class="report-tag-option">
        <input type="checkbox" name="tag_ids[]" value="${escapeHtml(tag.id)}" ${selectedTagIds.has(String(tag.id)) ? 'checked' : ''}>
        <span>${escapeHtml(tag.tag_name)}</span>
      </label>
    `).join('');
    reportTagChoices.querySelectorAll('input[type="checkbox"]').forEach(input => {
      input.addEventListener('change', () => {
        if (input.checked && selectedTagIds.size >= 5) {
          input.checked = false;
          alert('You can choose up to five tags.');
          return;
        }
        if (input.checked) selectedTagIds.add(input.value);
        else selectedTagIds.delete(input.value);
        renderReportTags(tags);
      });
    });
  }

  if (reportType) {
    reportType.addEventListener('change', () => loadReportTags(reportType.value));
    if (reportType.value) loadReportTags(reportType.value);
  }

  if (addReportTagButton && addReportTagModal) {
    addReportTagButton.addEventListener('click', event => {
      event.preventDefault();
      const createModal = document.getElementById('createReportModal');
      createModal.addEventListener('hidden.bs.modal', () => bootstrap.Modal.getOrCreateInstance(addReportTagModal).show(), { once: true });
      bootstrap.Modal.getOrCreateInstance(createModal).hide();
    });
    addReportTagModal.addEventListener('hidden.bs.modal', () => {
      bootstrap.Modal.getOrCreateInstance(document.getElementById('createReportModal')).show();
    });
  }

  if (addReportTagForm) {
    addReportTagForm.addEventListener('submit', async event => {
      event.preventDefault();
      const tagName = document.getElementById('newReportTagName').value.trim();
      const submitButton = addReportTagForm.querySelector('button[type="submit"]');
      addReportTagError.textContent = '';
      if (tagName.split(/\s+/).filter(Boolean).length > 3) {
        addReportTagError.textContent = 'Tags can contain a maximum of three words.';
        return;
      }
      submitButton.disabled = true;
      try {
        const response = await fetch('../../api/reports/tags.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ report_type: reportType.value, tag_name: tagName })
        });
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message || 'Could not add tag.');
        await loadReportTags(reportType.value, data.tag.id, true);
        addReportTagForm.reset();
        bootstrap.Modal.getOrCreateInstance(addReportTagModal).hide();
      } catch (error) {
        addReportTagError.textContent = error.message;
      } finally {
        submitButton.disabled = false;
      }
    });
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

    reportsTable?.querySelectorAll('.report-row').forEach(row => {
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

  document.addEventListener('safebrgy:live-refresh', filterReports);

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
            <i class="fas fa-times" aria-hidden="true"></i>
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

      if (selectedTagIds.size < 1 || selectedTagIds.size > 5) {
        alert('Choose between one and five tags for this report.');
        return;
      }

      const submitButton = createReportForm.querySelector('button[type="submit"]');
      if (submitButton && window.setButtonLoading) {
        submitButton.dataset.keepLoading = 'true';
        window.setButtonLoading(submitButton);
      }

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
          if (window.hideLoadingOverlay) {
            window.hideLoadingOverlay();
          }
          if (submitButton && window.clearButtonLoading) {
            window.clearButtonLoading(submitButton);
          }
          alert('Error: ' + (data.message || 'Failed to create report'));
        }
      } catch (error) {
        if (window.hideLoadingOverlay) {
          window.hideLoadingOverlay();
        }
        if (submitButton && window.clearButtonLoading) {
          window.clearButtonLoading(submitButton);
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
  reportsTable?.addEventListener('click', async event => {
      const btn = event.target.closest('.btn-view-report');
      if (!btn) return;
      const reportId = btn.getAttribute('data-report-id');
      
      try {
        const response = await fetch(`../../api/reports/get.php?id=${reportId}`);
        const data = await response.json();

        if (data.success) {
          const report = data.report;
          const reportTypeIcons = { Incident: 'fa-exclamation-triangle', 'Public Concerns': 'fa-bullhorn', Blotter: 'fa-gavel', 'Lost Property': 'fa-search' };
          const reportTypeClass = String(report.report_type || '').toLowerCase().replace(/\s+/g, '-');
          reportDetailsContent.innerHTML = `
            <div class="report-detail-section">
              <div class="detail-label">Case Number</div>
              <div class="detail-value case-number-detail">
                <span id="reportCaseNumber">${escapeHtml(report.case_number || 'N/A')}</span>
                <button type="button" class="copy-case-number-btn" id="copyCaseNumberBtn" title="Copy case number" aria-label="Copy case number">
                  <i class="fas fa-copy" aria-hidden="true"></i>
                </button>
              </div>
            </div>

            <div class="report-detail-section">
              <div class="detail-label">Report Type</div>
              <div class="detail-value"><span class="report-type-badge type-${escapeHtml(reportTypeClass)}"><i class="fas ${reportTypeIcons[report.report_type] || 'fa-file-alt'}" aria-hidden="true"></i>${escapeHtml(report.report_type === 'Incident' ? 'Incident Report' : report.report_type)}</span></div>
            </div>

            <div class="report-detail-section">
              <div class="detail-label">Tags</div>
              <div class="detail-value">${escapeHtml(report.tags || 'No tags')}</div>
            </div>

            <div class="report-detail-section">
              <div class="detail-label">Title</div>
              <div class="detail-value">${escapeHtml(report.title)}</div>
            </div>

            <div class="report-detail-section">
              <div class="detail-label">Description</div>
              <div class="detail-value">${escapeHtml(report.description)}</div>
            </div>

            ${report.location ? `
              <div class="report-detail-section">
                <div class="detail-label">Location</div>
                <div class="detail-value">${escapeHtml(report.location)}</div>
              </div>
            ` : ''}

            <div class="report-detail-section">
              <div class="detail-label">Status</div>
              <div class="detail-value">
                <span class="badge bg-${
                  report.status === 'Pending' ? 'warning' :
                  report.status === 'In Progress' ? 'info' :
                  report.status === 'Resolved' ? 'success' :
                  report.status === 'Dismissed' ? 'danger' : 'secondary'
                }">
                  ${escapeHtml(report.status)}
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

            <div class="report-detail-section">
              <div class="detail-label">Expiration</div>
              <div class="detail-value">${report.expires_at ? `${new Date(report.expires_at.replace(' ', 'T') + 'Z').toLocaleString()}${new Date(report.expires_at.replace(' ', 'T') + 'Z') <= new Date() && ['Pending', 'In Progress'].includes(report.status) ? ' (Expired)' : ''}` : 'Not available'}</div>
            </div>

            ${report.attachments && report.attachments.length > 0 ? `
              <div class="report-detail-section">
                <div class="detail-label">Attachments</div>
                ${report.attachments.map(attachment => `
                  <img src="../../${escapeHtml(attachment)}" alt="Report image" class="report-detail-image">
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
                copyCaseNumberButton.innerHTML = '<i class="fas fa-check" aria-hidden="true"></i>';
                copyCaseNumberButton.title = 'Copied';
                window.setTimeout(() => {
                  copyCaseNumberButton.innerHTML = '<i class="fas fa-copy" aria-hidden="true"></i>';
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
