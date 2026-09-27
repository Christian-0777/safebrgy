// Admin Reports Page JavaScript
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text == null ? '' : String(text);
  return div.innerHTML;
}

function reportRecordFromDetails(report) {
  const activeStatuses = ['Pending', 'In Progress'];
  const expiry = report.expires_at ? new Date(report.expires_at.replace(' ', 'T') + 'Z') : null;
  const status = expiry && expiry <= new Date() && activeStatuses.includes(report.status) ? 'Expired' : report.status;
  const reporter = report.first_name && report.last_name
    ? `${report.first_name} ${report.last_name}`
    : (report.reporter_name || report.username || 'Anonymous');

  return {
    caseNumber: report.case_number || 'N/A',
    title: report.title || 'Untitled',
    date: report.created_at ? new Date(report.created_at.replace(' ', 'T')).toLocaleDateString('en-US') : '',
    type: report.report_type === 'Incident' ? 'Incident Report' : (report.report_type || ''),
    tags: report.tags || '',
    status: status || 'Unknown',
    reporter,
    email: report.email || report.guest_email || ''
  };
}

function reportAttachmentUrl(attachment) {
  const path = String(attachment || '').replace(/^\/+/, '');
  if (/^https?:\/\//i.test(path)) return path;
  if (path.startsWith('uploads/guest_reports/')) {
    return `${window.location.origin}/safebrgy/guest_module/${encodeURIComponent(path.split('/').pop())}`;
  }
  return `${window.location.origin}/safebrgy/${path.split('/').map(encodeURIComponent).join('/')}`;
}

function loadReportImageData(url) {
  return new Promise(resolve => {
    const image = new Image();
    image.onload = () => {
      const canvas = document.createElement('canvas');
      canvas.width = image.naturalWidth;
      canvas.height = image.naturalHeight;
      const context = canvas.getContext('2d');
      if (!context) return resolve(null);
      context.drawImage(image, 0, 0);
      resolve({ data: canvas.toDataURL('image/png'), width: image.naturalWidth, height: image.naturalHeight });
    };
    image.onerror = () => resolve(null);
    image.src = url;
  });
}

async function createIndividualReportPdf(report) {
  if (!window.jspdf?.jsPDF) throw new Error('PDF export is unavailable.');
  const record = reportRecordFromDetails(report);
  const pdf = new window.jspdf.jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
  const margin = 16;
  const pageWidth = pdf.internal.pageSize.getWidth();
  const pageHeight = pdf.internal.pageSize.getHeight();
  const contentWidth = pageWidth - margin * 2;
  let y = 18;

  pdf.setFont('helvetica', 'bold');
  pdf.setFontSize(17);
  pdf.text('REPORT DETAILS', margin, y);
  y += 12;

  const addField = (label, value) => {
    pdf.setFontSize(10);
    pdf.setFont('helvetica', 'bold');
    const lines = pdf.splitTextToSize(String(value || 'N/A'), contentWidth - 35);
    const fieldHeight = Math.max(6, lines.length * 4.5 + 1);
    if (y + fieldHeight > pageHeight - margin) {
      pdf.addPage();
      y = margin;
    }
    pdf.text(`${label}:`, margin, y);
    pdf.setFont('helvetica', 'normal');
    pdf.text(lines, margin + 35, y);
    y += fieldHeight;
  };

  const dateFiled = report.created_at ? new Date(report.created_at.replace(' ', 'T') + 'Z').toLocaleString() : 'N/A';
  const expiration = report.expires_at ? new Date(report.expires_at.replace(' ', 'T') + 'Z').toLocaleString() : 'Not available';
  const expirationDate = report.expires_at ? new Date(report.expires_at.replace(' ', 'T') + 'Z') : null;
  const expired = expirationDate && expirationDate <= new Date() && ['Pending', 'In Progress'].includes(report.status);

  addField('Case Number', record.caseNumber);
  addField('Report Type', record.type);
  addField('Resident ID', report.resident_id || (report.source === 'guest' ? 'Guest' : 'N/A'));
  addField('Reporter', record.reporter);
  addField('Reporter Email', record.email || 'N/A');
  addField('Title', record.title);
  addField('Tags', record.tags || 'No tags');
  addField('Description', report.description || 'No description provided');
  addField('Location', report.location || 'Not specified');
  addField('Date Filed', dateFiled);
  addField('Expiration', `${expiration}${expired ? ' (Expired)' : ''}`);
  addField('Status', record.status);

  const attachments = Array.isArray(report.attachments) ? report.attachments : [];
  if (attachments.length) {
    pdf.setFont('helvetica', 'bold');
    pdf.setFontSize(11);
    pdf.text('Pictures', margin, y);
    y += 6;
    for (const attachment of attachments) {
      const image = await loadReportImageData(reportAttachmentUrl(attachment));
      if (!image) continue;
      const scale = Math.min(120 / image.width, 100 / image.height, contentWidth / image.width);
      const width = image.width * scale;
      const height = image.height * scale;
      if (y + height > pageHeight - margin) {
        pdf.addPage();
        y = margin;
      }
      pdf.addImage(image.data, 'PNG', margin, y, width, height);
      y += height + 8;
    }
  }

  const caseNumber = String(record.caseNumber).replace(/[^a-z0-9_-]/gi, '-');
  pdf.save(`report-${caseNumber}.pdf`);
}

async function printIndividualReport(report) {
  const record = reportRecordFromDetails(report);
  const printWindow = window.open('', '_blank');
  if (!printWindow) throw new Error('Allow pop-ups to print this report.');
  const dateFiled = report.created_at ? new Date(report.created_at.replace(' ', 'T') + 'Z').toLocaleString() : 'N/A';
  const expirationDate = report.expires_at ? new Date(report.expires_at.replace(' ', 'T') + 'Z') : null;
  const expired = expirationDate && expirationDate <= new Date() && ['Pending', 'In Progress'].includes(report.status);
  const expiration = report.expires_at ? `${expirationDate.toLocaleString()}${expired ? ' (Expired)' : ''}` : 'Not available';
  const fields = [
    ['Case Number', record.caseNumber],
    ['Report Type', record.type],
    ['Resident ID', report.resident_id || (report.source === 'guest' ? 'Guest' : 'N/A')],
    ['Reporter', record.reporter],
    ['Reporter Email', record.email || 'N/A'],
    ['Title', record.title],
    ['Tags', record.tags || 'No tags'],
    ['Description', report.description || 'No description provided'],
    ['Location', report.location || 'Not specified'],
    ['Date Filed', dateFiled],
    ['Expiration', expiration],
    ['Status', record.status]
  ];
  const details = fields.map(([label, value]) => `<div class="field"><strong>${escapeHtml(label)}</strong><div>${escapeHtml(value)}</div></div>`).join('');
  const attachments = (Array.isArray(report.attachments) ? report.attachments : []).map(attachment => `<img src="${escapeHtml(reportAttachmentUrl(attachment))}" alt="Report attachment">`).join('');

  printWindow.document.write(`<!doctype html><html><head><title>Report ${escapeHtml(record.caseNumber)}</title><style>body{font:12px Arial,sans-serif;color:#111;padding:24px}h1{font-size:20px;margin:0 0 20px}.field{display:grid;grid-template-columns:140px 1fr;gap:12px;padding:8px 0;border-bottom:1px solid #ddd}.field div{white-space:pre-wrap;overflow-wrap:anywhere}.pictures{display:flex;flex-wrap:wrap;gap:12px;margin-top:18px}.pictures img{max-width:100%;max-height:240px;object-fit:contain}h2{font-size:14px}@page{margin:14mm}</style></head><body><h1>REPORT DETAILS</h1>${details}${attachments ? `<h2>Pictures</h2><div class="pictures">${attachments}</div>` : ''}</body></html>`);
  printWindow.document.close();
  const images = Array.from(printWindow.document.images);
  await Promise.all(images.map(image => image.complete ? Promise.resolve() : new Promise(resolve => {
    image.onload = resolve;
    image.onerror = resolve;
  })));
  printWindow.focus();
  printWindow.print();
}

function createReportRecordsPdf(records, context, filename = 'report-records.pdf') {
  if (!window.jspdf?.jsPDF) throw new Error('PDF export is unavailable.');
  const pdf = new window.jspdf.jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
  const margin = 12;
  const columnWidths = [34, 43, 26, 32, 38, 25, 37, 38];
  const headers = ['Case Number', 'Title', 'Date Filed', 'Type', 'Tags', 'Status', 'Reporter', 'Reporter Email'];
  let y = 15;
  const drawHeader = () => {
    pdf.setFontSize(16); pdf.text('REPORT RECORDS', margin, y); y += 8;
    pdf.setFontSize(9); pdf.text(`Date From: ${context.from}`, margin, y); pdf.text(`Date To: ${context.to}`, margin + 65, y); y += 5;
    pdf.text(`Declared by: ${context.admin}, ${context.declaredAt}`, margin, y); y += 8;
    let x = margin; pdf.setFont('helvetica', 'bold'); pdf.setFontSize(8);
    headers.forEach((header, index) => { pdf.text(pdf.splitTextToSize(header, columnWidths[index] - 2), x + 1, y); x += columnWidths[index]; });
    y += 4; pdf.line(margin, y, 285, y); y += 4; pdf.setFont('helvetica', 'normal'); pdf.setFontSize(8);
  };
  drawHeader();
  records.forEach(record => {
    const values = [record.caseNumber, record.title, record.date, record.type, record.tags, record.status, record.reporter, record.email];
    const lines = values.map((value, index) => pdf.splitTextToSize(String(value || ''), columnWidths[index] - 2));
    const rowHeight = Math.max(5, ...lines.map(cellLines => cellLines.length * 3.5)) + 3;
    if (y + rowHeight > 195) { pdf.addPage(); y = 15; drawHeader(); }
    let x = margin;
    lines.forEach((cellLines, index) => { pdf.text(cellLines, x + 1, y); x += columnWidths[index]; });
    y += rowHeight; pdf.line(margin, y, 285, y); y += 3;
  });
  pdf.save(filename);
}

function printReportRecordsWindow(records, context) {
  const printWindow = window.open('', '_blank');
  if (!printWindow) throw new Error('Allow pop-ups to print report records.');
  const rows = records.map(record => `<tr>${[record.caseNumber, record.title, record.date, record.type, record.tags, record.status, record.reporter, record.email].map(value => `<td>${escapeHtml(value)}</td>`).join('')}</tr>`).join('');
  printWindow.document.write(`<!doctype html><html><head><title>Report Records</title><style>body{font:11px Arial,sans-serif;color:#111;padding:24px}h1{font-size:20px}p{margin:6px 0}table{width:100%;border-collapse:collapse;margin-top:22px}th,td{border-bottom:1px solid #777;padding:6px;text-align:left;overflow-wrap:anywhere}th{border-top:1px solid #777}@page{size:landscape;margin:10mm}</style></head><body><h1>REPORT RECORDS</h1><p>Date From: ${escapeHtml(context.from)}</p><p>Date To: ${escapeHtml(context.to)}</p><p>Declared by: ${escapeHtml(context.admin)}, ${escapeHtml(context.declaredAt)}</p><table><thead><tr><th>Case Number</th><th>Title</th><th>Date Filed</th><th>Type</th><th>Tags</th><th>Status</th><th>Reporter</th><th>Reporter Email</th></tr></thead><tbody>${rows}</tbody></table></body></html>`);
  printWindow.document.close();
  printWindow.focus();
  printWindow.print();
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.report-status-icon[data-bs-toggle="tooltip"]').forEach(icon => {
    bootstrap.Tooltip.getOrCreateInstance(icon);
  });

  const reportsTable = document.getElementById('reportsTable');
  const reportDetailsModal = document.getElementById('reportDetailsModal');
  const modalBody = document.getElementById('modalBody');
  const applyStatusBtn = document.getElementById('applyStatusBtn');
  const exportReportDetailsButton = document.getElementById('exportReportDetailsPdf');
  const printReportDetailsButton = document.getElementById('printReportDetails');
  const reportsEndpoint = window.location.pathname;
  let currentReportId = null;
  let currentReportSource = 'resident';
  let currentReportDetails = null;

  exportReportDetailsButton?.addEventListener('click', async () => {
    if (!currentReportDetails) return;
    try {
      await createIndividualReportPdf(currentReportDetails);
    } catch (error) {
      alert(error.message);
    }
  });

  printReportDetailsButton?.addEventListener('click', async () => {
    if (!currentReportDetails) return;
    try {
      await printIndividualReport(currentReportDetails);
    } catch (error) {
      alert(error.message);
    }
  });

  // Load report details when View button is clicked
  reportsTable?.addEventListener('click', async e => {
      const btn = e.target.closest('.view-btn');
      if (!btn) return;
      e.preventDefault();
      const reportId = btn.dataset.id;
      currentReportId = reportId;
      currentReportSource = btn.dataset.source || 'resident';

      try {
        const response = await fetch(reportsEndpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ action: 'get_report', id: reportId, source: currentReportSource })
        });

        if (!response.ok) {
          throw new Error(`Request failed with status ${response.status}`);
        }

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

  // Populate modal with report details
  function populateModal(report) {
    currentReportDetails = report;
    const reporterName = report.first_name && report.last_name 
      ? `${report.first_name} ${report.last_name}` 
      : (report.reporter_name || report.username || 'Anonymous');

    const attachments = Array.isArray(report.attachments) ? report.attachments : [];
    const attachmentsHtml = attachments.length
      ? `<div class="mb-3"><strong>Attachments:</strong><div class="report-attachments">${attachments.map((attachment) => {
          const attachmentUrl = reportAttachmentUrl(attachment);
          return `<a href="${escapeHtml(attachmentUrl)}" target="_blank" rel="noopener"><img src="${escapeHtml(attachmentUrl)}" alt="Report attachment" class="report-attachment-image"></a>`;
        }).join('')}</div></div>`
      : '';

    const statusBadgeClass = {
      'Pending': 'warning',
      'In Progress': 'primary',
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
            <strong>Report Type:</strong> <span class="report-type-badge type-${escapeHtml(String(report.report_type || '').toLowerCase().replace(/\s+/g, '-'))}"><i class="fas ${({ Incident: 'fa-exclamation-triangle', 'Public Concerns': 'fa-bullhorn', Blotter: 'fa-gavel', 'Lost Property': 'fa-search' })[report.report_type] || 'fa-file-alt'}" aria-hidden="true"></i>${escapeHtml(report.report_type === 'Incident' ? 'Incident Report' : report.report_type)}</span>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <strong>Resident ID:</strong> <p>${escapeHtml(report.resident_id || (report.source === 'guest' ? 'Guest' : 'N/A'))}</p>
          </div>
          <div class="col-md-6">
            <strong>Reporter:</strong> <p>${escapeHtml(reporterName)}</p>
          </div>
        </div>

        <div class="mb-3">
          <strong>Title:</strong> <p>${escapeHtml(report.title || 'Untitled')}</p>
        </div>

        <div class="mb-3">
          <strong>Tags:</strong> <p>${escapeHtml(report.tags || 'No tags')}</p>
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

        <div class="mb-3">
          <strong>Expiration:</strong> <p>${report.expires_at ? `${new Date(report.expires_at.replace(' ', 'T') + 'Z').toLocaleString()}${new Date(report.expires_at.replace(' ', 'T') + 'Z') <= new Date() && ['Pending', 'In Progress'].includes(report.status) ? ' (Expired)' : ''}` : 'Not available'}</p>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <strong>Reporter Email:</strong> <p>${escapeHtml(report.email || report.guest_email || 'N/A')}</p>
          </div>
          <div class="col-md-6"></div>
        </div>

        ${attachmentsHtml}

        <hr>

        <div class="mb-3">
          <label class="form-label"><strong>Update Status:</strong></label>
          <select class="form-select" id="statusSelect">
            <option value="Pending" ${report.status === 'Pending' ? 'selected' : ''}>Pending</option>
            <option value="In Progress" ${report.status === 'In Progress' ? 'selected' : ''}>In Progress</option>
            <option value="Resolved" ${report.status === 'Resolved' ? 'selected' : ''}>Resolved</option>
            <option value="Dismissed" ${report.status === 'Dismissed' ? 'selected' : ''}>Dismissed</option>
          </select>
          <small class="text-muted">Current Status: <span class="badge bg-${statusBadgeClass}">${escapeHtml(report.status)}</span></small>
        </div>
      </div>
    `;

    modalBody.innerHTML = html;
    applyStatusBtn.disabled = false;
    if (exportReportDetailsButton) exportReportDetailsButton.disabled = false;
    if (printReportDetailsButton) printReportDetailsButton.disabled = false;
  }

  // Apply status update
  applyStatusBtn.addEventListener('click', async () => {
    if (window.showLoadingOverlay) {
      window.showLoadingOverlay();
    }

    const statusSelect = document.getElementById('statusSelect');
    const newStatus = statusSelect.value;

    try {
      const response = await fetch(reportsEndpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'update_status',
          id: currentReportId,
          source: currentReportSource,
          status: newStatus
        })
      });

      if (!response.ok) {
        throw new Error(`Request failed with status ${response.status}`);
      }

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

  const caseSearchForm = document.getElementById('caseSearchForm');
  if (caseSearchForm) {
    caseSearchForm.addEventListener('submit', async (event) => {
      event.preventDefault();
      const input = document.getElementById('caseSearchInput');
      const alertBox = document.getElementById('caseSearchAlert');
      const searchBtn = document.getElementById('caseSearchBtn');
      const caseNumber = input.value.trim();
      alertBox.classList.add('d-none');
      if (!caseNumber) {
        alertBox.textContent = 'Enter a case number.';
        alertBox.classList.remove('d-none');
        return;
      }
      searchBtn.disabled = true;
      try {
        const response = await fetch(reportsEndpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ action: 'search_case', case_number: caseNumber })
        });
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message || 'Report not found.');
        currentReportId = data.report.id;
        currentReportSource = data.report.source || 'resident';
        populateModal(data.report);
        bootstrap.Modal.getOrCreateInstance(reportDetailsModal).show();
      } catch (error) {
        alertBox.textContent = error.message;
        alertBox.classList.remove('d-none');
      } finally {
        delete searchBtn.dataset.keepLoading;
        if (window.clearButtonLoading) {
          window.clearButtonLoading(searchBtn);
        } else {
          searchBtn.classList.remove('is-loading');
          searchBtn.removeAttribute('aria-busy');
          searchBtn.disabled = false;
        }
      }
    });
  }

  // Escape HTML to prevent XSS
  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  // Search functionality (client-side)
  // Search is handled via form submission in the PHP page for optimal filtering
});

document.addEventListener('DOMContentLoaded', () => {
  const reportEndpoint = window.location.pathname;
  const reportsTable = document.getElementById('reportsTable');
  const selectAllButton = document.getElementById('selectAllReports');
  const unselectAllButton = document.getElementById('unselectAllReports');
  const selectAllRows = document.getElementById('selectAllReportRows');
  const quickUpdateButton = document.getElementById('openQuickUpdate');
  const quickUpdateModal = document.getElementById('quickUpdateModal');
  const quickUpdateList = document.getElementById('quickUpdateReportList');
  const selectedCount = document.getElementById('selectedReportCount');

  function reportCheckboxes() {
    return Array.from(reportsTable?.querySelectorAll('.report-row-checkbox') ?? []);
  }

  function selectedReportBoxes() {
    return reportCheckboxes().filter(checkbox => checkbox.checked);
  }

  function updateSelectionControls() {
    const selected = selectedReportBoxes();
    selectedCount.textContent = String(selected.length);
    quickUpdateButton.disabled = selected.length === 0;
    const checkboxes = reportCheckboxes();
    selectAllRows.checked = checkboxes.length > 0 && selected.length === checkboxes.length;
    selectAllRows.indeterminate = selected.length > 0 && selected.length < checkboxes.length;
  }

  function setAllRows(checked) {
    reportCheckboxes().forEach(checkbox => { checkbox.checked = checked; });
    updateSelectionControls();
  }

  selectAllButton?.addEventListener('click', () => setAllRows(true));
  unselectAllButton?.addEventListener('click', () => setAllRows(false));
  selectAllRows?.addEventListener('change', () => setAllRows(selectAllRows.checked));
  reportsTable?.addEventListener('change', event => {
    if (event.target.closest('.report-row-checkbox')) updateSelectionControls();
  });
  document.addEventListener('safebrgy:live-refresh', updateSelectionControls);

  quickUpdateButton?.addEventListener('click', () => {
    const selected = selectedReportBoxes();
    quickUpdateList.innerHTML = selected.map(checkbox => {
      const cells = checkbox.closest('tr').querySelectorAll('td');
      return `<div class="selected-report-row"><strong>${escapeHtml(cells[1].textContent.trim())}</strong><span>${escapeHtml(cells[2].innerText.trim())}</span></div>`;
    }).join('');
    bootstrap.Modal.getOrCreateInstance(quickUpdateModal).show();
  });

  document.getElementById('applyBulkStatus')?.addEventListener('click', async event => {
    const button = event.currentTarget;
    const selected = selectedReportBoxes().map(checkbox => ({ id: checkbox.dataset.id, source: checkbox.dataset.source }));
    if (!selected.length) return;
    button.disabled = true;
    try {
      const response = await fetch(reportEndpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ action: 'quick_update', status: document.getElementById('bulkStatusSelect').value, reports: JSON.stringify(selected) })
      });
      const data = await response.json();
      if (!response.ok || !data.success) throw new Error(data.message || 'Unable to update reports.');
      location.reload();
    } catch (error) {
      alert(error.message);
    } finally {
      button.disabled = false;
    }
  });

  const recordsTable = document.getElementById('reportRecordsTable');
  const exportModal = document.getElementById('exportConfirmationModal');
  let exportRecords = [];

  function readRecordRow(row) {
    const cells = Array.from(row.cells).map(cell => cell.textContent.trim());
    return {
      caseNumber: cells[0],
      title: cells[1],
      date: cells[2],
      type: cells[3],
      tags: cells[4],
      status: row.dataset.statusName || cells[5],
      reporter: cells[6],
      email: cells[7]
    };
  }

  function openExportConfirmation(records) {
    exportRecords = records;
    document.getElementById('exportReportList').innerHTML = records.map(record => `<tr><td>${escapeHtml(record.caseNumber)}</td><td>${escapeHtml(record.title)}</td><td>${escapeHtml(record.date)}</td><td>${escapeHtml(record.type)}</td><td>${escapeHtml(record.tags)}</td><td>${escapeHtml(record.status)}</td><td>${escapeHtml(record.reporter)}</td><td>${escapeHtml(record.email)}</td></tr>`).join('');
    const from = formatExportDate(document.getElementById('recordDateFrom').value);
    const to = formatExportDate(document.getElementById('recordDateTo').value);
    document.getElementById('exportRangeSummary').textContent = `Date From: ${from} | Date To: ${to} | ${records.length} report(s)`;
    bootstrap.Modal.getOrCreateInstance(exportModal).show();
  }

  function currentRecords() {
    return Array.from(recordsTable?.querySelectorAll('tbody tr') || [])
      .filter(row => row.cells.length > 1)
      .map(readRecordRow);
  }

  document.getElementById('exportRecords')?.addEventListener('click', () => openExportConfirmation(currentRecords()));
  document.getElementById('printRecords')?.addEventListener('click', () => openExportConfirmation(currentRecords()));
  recordsTable?.querySelectorAll('.export-one-record').forEach(button => {
    button.addEventListener('click', () => openExportConfirmation([readRecordRow(button.closest('tr'))]));
  });

  function getExportContext() {
    return {
      from: formatExportDate(document.getElementById('recordDateFrom').value),
      to: formatExportDate(document.getElementById('recordDateTo').value),
      admin: document.querySelector('#report-records-pane')?.dataset.adminName || 'Admin',
      declaredAt: new Date().toLocaleString()
    };
  }

  function formatExportDate(value) {
    if (!value) return 'All dates';
    const date = new Date(`${value}T00:00:00`);
    return Number.isNaN(date.getTime()) ? value : date.toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' });
  }

  function printReportRecords() {
    try {
      printReportRecordsWindow(exportRecords, getExportContext());
    } catch (error) {
      alert(error.message);
    }
  }

  document.getElementById('confirmPrintRecords')?.addEventListener('click', printReportRecords);
  document.getElementById('confirmExportPdf')?.addEventListener('click', () => {
    try {
      createReportRecordsPdf(exportRecords, getExportContext(), `report-records-${new Date().toISOString().slice(0, 10)}.pdf`);
    } catch (error) {
      alert(error.message);
    }
  });

  const stats = document.getElementById('reportStatistics');
  document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
    tab.addEventListener('shown.bs.tab', event => {
      if (stats) stats.hidden = event.target.dataset.bsTarget !== '#manage-reports-pane';
    });
  });
  if (new URLSearchParams(window.location.search).get('view') === 'records') {
    const recordsTab = document.querySelector('[data-bs-target="#report-records-pane"]');
    if (recordsTab) bootstrap.Tab.getOrCreateInstance(recordsTab).show();
  }
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
