// Requests page JavaScript
document.addEventListener('DOMContentLoaded', () => {
  const updateStatusButtons = document.querySelectorAll('.update-status-btn');
  const statusSelects = document.querySelectorAll('.status-select');
  const requestsEndpoint = new URL('/safebrgy/admin/requests', window.location.origin).href;

  // Update status button click handler
  updateStatusButtons.forEach(btn => {
    btn.addEventListener('click', async () => {
      if (window.showLoadingOverlay) {
        window.showLoadingOverlay();
      }

      const requestId = btn.dataset.requestId;
      const modal = btn.closest('.modal');
      const statusSelect = modal.querySelector('.status-select');
      const newStatus = statusSelect.value;

      if (!newStatus) {
        alert('Please select a new status');
        return;
      }

      try {
        const response = await fetch(requestsEndpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `action=update_status&request_id=${requestId}&status=${encodeURIComponent(newStatus)}`
        });

        const data = await response.json();
        if (window.hideLoadingOverlay) {
          window.hideLoadingOverlay();
        }
        if (data.success) {
          alert('Status updated successfully!');
          location.reload();
        } else {
          alert('Failed to update status.');
        }
      } catch (error) {
        if (window.hideLoadingOverlay) {
          window.hideLoadingOverlay();
        }
        console.error('Error:', error);
        alert('An error occurred while updating the status.');
      }
    });
  });

  // Validate status selection
  statusSelects.forEach(select => {
    select.addEventListener('change', () => {
      // Enable/disable the update button based on selection
      const modal = select.closest('.modal');
      const updateBtn = modal.querySelector('.update-status-btn');
      updateBtn.disabled = !select.value;
    });
  });
});
