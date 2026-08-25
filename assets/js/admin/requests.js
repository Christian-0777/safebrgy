// Requests page JavaScript
window.addEventListener('error', event => {
  console.error('[SafeBrgy] Browser error:', {
    message: event.message,
    source: event.filename,
    line: event.lineno,
    column: event.colno,
    error: event.error
  });
});

window.addEventListener('unhandledrejection', event => {
  console.error('[SafeBrgy] Unhandled promise rejection:', event.reason);
});

document.addEventListener('DOMContentLoaded', () => {
  const updateStatusButtons = document.querySelectorAll('.update-status-btn');
  const statusSelects = document.querySelectorAll('.status-select');
  const requestsEndpoint = window.location.href;
  const rejectRequestModalElement = document.getElementById('rejectRequestModal');
  const rejectRequestModal = rejectRequestModalElement ? new bootstrap.Modal(rejectRequestModalElement) : null;
  const rejectReasonSelect = document.getElementById('rejectReason');
  const otherReasonGroup = document.getElementById('otherReasonGroup');
  const otherReasonInput = document.getElementById('otherReason');

  console.info('[SafeBrgy] Requests page loaded', {
    endpoint: requestsEndpoint,
    updateButtons: updateStatusButtons.length,
    statusSelects: statusSelects.length
  });

  const updateRequestStatus = async (requestId, newStatus, rejectionReason = '', additionalDetails = '') => {
    if (window.showLoadingOverlay) {
      window.showLoadingOverlay();
    }

    try {
      const response = await fetch(requestsEndpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update_status&request_id=${requestId}&status=${encodeURIComponent(newStatus)}&rejection_reason=${encodeURIComponent(rejectionReason)}&additional_details=${encodeURIComponent(additionalDetails)}`
      });

      const responseText = await response.text();
      let data;

      try {
        data = JSON.parse(responseText);
      } catch (parseError) {
        console.error('[SafeBrgy] Server returned a non-JSON response:', {
          status: response.status,
          statusText: response.statusText,
          url: response.url,
          body: responseText,
          parseError
        });
        throw new Error(`Server returned ${response.status} ${response.statusText} instead of JSON`);
      }

      if (!response.ok) {
        console.error('[SafeBrgy] Request failed:', {
          status: response.status,
          statusText: response.statusText,
          url: response.url,
          response: data
        });
        throw new Error(`Request failed with ${response.status} ${response.statusText}`);
      }

      if (window.hideLoadingOverlay) {
        window.hideLoadingOverlay();
      }
      if (data.success) {
        alert(newStatus === 'Rejected' ? 'Request rejected and resident notified.' : 'Status updated successfully!');
        location.reload();
      } else {
        alert(data.message || 'Failed to update status.');
      }
    } catch (error) {
      if (window.hideLoadingOverlay) {
        window.hideLoadingOverlay();
      }
      console.error('[SafeBrgy] Error updating request status:', {
        requestId,
        newStatus,
        endpoint: requestsEndpoint,
        error
      });
      alert('An error occurred while updating the status.');
    }
  };

  // Update status button click handler
  updateStatusButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const requestId = btn.dataset.requestId;
      const modal = btn.closest('.modal');
      const statusSelect = modal.querySelector('.status-select');
      const newStatus = statusSelect.value;

      if (!newStatus) {
        alert('Please select a new status');
        return;
      }

      if (newStatus === 'Rejected') {
        document.getElementById('rejectRequestId').value = requestId;
        document.getElementById('rejectResident').textContent = modal.querySelector('.request-resident-name')?.textContent || 'Resident';
        document.getElementById('rejectDocumentType').textContent = modal.querySelector('.request-document-type')?.textContent || 'Request';
        document.getElementById('rejectReferenceNumber').textContent = modal.querySelector('.request-reference-number')?.textContent || 'N/A';
        rejectReasonSelect.value = '';
        otherReasonInput.value = '';
        document.getElementById('additionalDetails').value = '';
        otherReasonGroup.hidden = true;
        rejectRequestModal.show();
        return;
      }

      updateRequestStatus(requestId, newStatus);
    });
  });

  rejectReasonSelect?.addEventListener('change', () => {
    otherReasonGroup.hidden = rejectReasonSelect.value !== 'Other';
    if (rejectReasonSelect.value !== 'Other') {
      otherReasonInput.value = '';
    }
  });

  document.getElementById('confirmRejectBtn')?.addEventListener('click', () => {
    const selectedReason = rejectReasonSelect.value;
    const otherReason = otherReasonInput.value.trim();
    const reason = selectedReason === 'Other' ? otherReason : selectedReason;

    if (!reason) {
      alert(selectedReason === 'Other' ? 'Please enter the other reason for rejection.' : 'Please select a reason for rejection.');
      return;
    }

    const requestId = document.getElementById('rejectRequestId').value;
    const additionalDetails = document.getElementById('additionalDetails').value.trim();
    rejectRequestModal.hide();
    updateRequestStatus(requestId, 'Rejected', reason, additionalDetails);
  });

  // Validate status selection
  statusSelects.forEach(select => {
    const updateStatusControls = () => {
      const modal = select.closest('.modal');
      const updateBtn = modal.querySelector('.update-status-btn');
      updateBtn.disabled = !select.value;
    };

    select.addEventListener('change', updateStatusControls);
    updateStatusControls();
  });
});
