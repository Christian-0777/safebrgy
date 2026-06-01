// Handle View Request Modal
document.addEventListener('DOMContentLoaded', () => {
  const viewModal = new bootstrap.Modal(document.getElementById('viewRequestModal'));
  let currentRequestId = null;

  document.querySelectorAll('.view-request-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const requestId = btn.dataset.id;
      const requestNumber = btn.dataset.requestNumber;
      const residentName = btn.dataset.name;
      const requestType = btn.dataset.type;
      const purpose = btn.dataset.purpose;
      const location = btn.dataset.location;
      const status = btn.dataset.status;
      const created = btn.dataset.created;
      const phone = btn.dataset.phone;
      const email = btn.dataset.email;

      currentRequestId = requestId;

      // Populate modal
      document.getElementById('modalRequestNumber').textContent = '#' + requestNumber;
      document.getElementById('modalResidentName').textContent = residentName;
      document.getElementById('modalRequestType').textContent = requestType;
      document.getElementById('modalPurpose').textContent = purpose;
      document.getElementById('modalLocation').textContent = location;
      document.getElementById('modalCurrentStatus').innerHTML = `<span class="badge badge-status badge-status-${status.toLowerCase().replace(/ /g, '-')}">${status}</span>`;
      document.getElementById('modalCreatedDate').textContent = created;
      document.getElementById('modalPhoneNumber').textContent = phone;
      document.getElementById('modalEmail').textContent = email;
      document.getElementById('statusSelect').value = status;

      viewModal.show();
    });
  });

  // Handle Update Status
  document.getElementById('updateStatusBtn').addEventListener('click', async () => {
    if (!currentRequestId) return;

    const newStatus = document.getElementById('statusSelect').value;
    const btn = document.getElementById('updateStatusBtn');
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

    try {
      const response = await fetch('requests.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=update_status&request_id=${currentRequestId}&status=${encodeURIComponent(newStatus)}`
      });

      const result = await response.json();

      if (result.success) {
        // Update status badge in modal
        document.getElementById('modalCurrentStatus').innerHTML = `<span class="badge badge-status badge-status-${newStatus.toLowerCase().replace(/ /g, '-')}">${newStatus}</span>`;
        
        // Reload table to show updated status
        setTimeout(() => {
          location.reload();
        }, 500);

        btn.innerHTML = '<i class="fas fa-check"></i> Updated!';
        setTimeout(() => {
          viewModal.hide();
        }, 1000);
      } else {
        alert(result.message || 'Failed to update status');
        btn.disabled = false;
        btn.innerHTML = originalText;
      }
    } catch (error) {
      console.error('Error:', error);
      alert('An error occurred while updating the status');
      btn.disabled = false;
      btn.innerHTML = originalText;
    }
  });

  // Reset button state when modal is hidden
  document.getElementById('viewRequestModal').addEventListener('hidden.bs.modal', () => {
    const btn = document.getElementById('updateStatusBtn');
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-save"></i> Update Status';
    currentRequestId = null;
  });
});

